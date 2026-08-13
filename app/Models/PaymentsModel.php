<?php

namespace App\Models;

use App\Models\DocumentsModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Schema;

class PaymentsModel extends Model
{
    public const TYPES = ['cash', 'card', 'online', 'transfer', 'admin_entry'];
    public const STATUSES = ['pending', 'confirmed', 'partially_refunded', 'refunded', 'cancelled'];
    public const CONFIRMATION_STATUSES = ['pending', 'confirmed', 'rejected', 'cancelled'];

    protected $table = 'payments';
    use HasFactory;
    protected $fillable = [
        'document_id',
        'order_id',
        'filial_id',
        'amount',
        'payment_type',
        'status',
        'confirmation_status',
        'paid_by_admin_id',
        'confirmed_by_id',
        'confirmed_at',
        'cashier_id',
        'cash_session_id',
        'online_transaction_id',
        'payment_proof_path',
        'payment_proof_original_name',
        'payment_proof_mime',
        'payment_proof_size',
        'refund_amount',
        'refunded_at',
        'refunded_by_id',
        'cancelled_at',
        'cancelled_by_id',
        'cancellation_reason',
        'invoice_id',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'refunded_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'payment_proof_size' => 'integer',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::created(function (PaymentsModel $payment): void {
            if ($payment->receipt_number) {
                return;
            }

            $payment->loadMissing(['order', 'document']);
            $filialId = $payment->filial_id ?: $payment->order?->filial_id ?: $payment->document?->filial_id;
            $code = $filialId ? FilialModel::query()->whereKey($filialId)->value('code') : null;
            $code = strtoupper((string) preg_replace('/[^A-Za-z0-9]+/', '', (string) ($code ?: 'FIL')));

            $payment->forceFill([
                'filial_id' => $filialId,
                'receipt_number' => 'LEGACY-RCPT-' . ($code ?: 'FIL') . '-' . $payment->id,
                'status' => $payment->status ?: 'confirmed',
                'confirmation_status' => $payment->confirmation_status ?: 'confirmed',
                'confirmed_by_id' => $payment->confirmed_by_id ?: $payment->paid_by_admin_id,
                'confirmed_at' => $payment->confirmed_at ?: $payment->created_at,
                'cashier_id' => $payment->cashier_id ?: $payment->paid_by_admin_id,
            ])->saveQuietly();

            if (Schema::hasTable('payment_status_histories')) {
                PaymentStatusHistory::query()->firstOrCreate([
                    'payment_id' => $payment->id,
                    'from_status' => null,
                    'to_status' => $payment->status,
                    'changed_by_id' => $payment->paid_by_admin_id,
                    'reason' => 'To‘lov ledgerga kiritildi',
                ]);
            }
        });
    }

    public function scopeEffective(Builder $query): Builder
    {
        return $query
            ->whereIn('status', ['confirmed', 'partially_refunded'])
            ->whereRaw('(amount - COALESCE(refund_amount, 0)) > 0');
    }

    public function getEffectiveAmountAttribute(): float
    {
        return round(max((float) $this->amount - (float) $this->refund_amount, 0), 2);
    }

    public function paidByAdmin()
    {
        return $this->belongsTo(User::class, 'paid_by_admin_id')->withTrashed();
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id')->withTrashed();
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by_id')->withTrashed();
    }

    public function refundedBy()
    {
        return $this->belongsTo(User::class, 'refunded_by_id')->withTrashed();
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by_id')->withTrashed();
    }

    public function filial()
    {
        return $this->belongsTo(FilialModel::class, 'filial_id');
    }

    public function cashSession()
    {
        return $this->belongsTo(CashSession::class, 'cash_session_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function refunds()
    {
        return $this->hasMany(PaymentRefund::class, 'payment_id')->latest();
    }

    public function statusHistories()
    {
        return $this->hasMany(PaymentStatusHistory::class, 'payment_id')->latest();
    }

    public function document()
    {
        return $this->belongsTo(DocumentsModel::class, 'document_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}

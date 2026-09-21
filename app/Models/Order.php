<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public const STATUSES = [
        'received',
        'waiting_documents',
        'awaiting_payment',
        'partially_paid',
        'paid',
        'in_processing',
        'waiting_review',
        'ready_for_delivery',
        'courier_sent',
        'delivered',
        'completed',
        'cancelled',
    ];

    public const STATUS_LABELS = [
        'received' => 'Qabul qilindi',
        'waiting_documents' => 'Hujjatlar kutilmoqda',
        'awaiting_payment' => 'To‘lov kutilmoqda',
        'partially_paid' => 'Qisman to‘langan',
        'paid' => 'To‘liq to‘langan',
        'in_processing' => 'Jarayonda',
        'waiting_review' => 'Tekshiruv kutilmoqda',
        'ready_for_delivery' => 'Topshirishga tayyor',
        'courier_sent' => 'Kuryerga berildi',
        'delivered' => 'Yetkazildi',
        'completed' => 'Yakunlandi',
        'cancelled' => 'Bekor qilindi',
    ];

    protected $fillable = [
        'client_id',
        'partner_id',
        'filial_id',
        'created_by_id',
        'responsible_user_id',
        'repeat_of_order_id',
        'package_template_id',
        'package_variant',
        'package_price',
        'package_deadline_days',
        'package_margin_percent',
        'package_name_snapshot',
        'order_code',
        'tracking_token',
        'status',
        'priority',
        'source',
        'customer_source',
        'partner_reference',
        'partner_discount_percent',
        'partner_discount_amount',
        'billing_status',
        'partner_invoice_id',
        'partner_metadata',
        'delivery_type',
        'title',
        'description',
        'currency',
        'subtotal_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'cost_amount',
        'profit_amount',
        'promised_at',
        'completed_at',
        'cancelled_at',
        'delivered_at',
    ];

    protected $casts = [
        'subtotal_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'cost_amount' => 'decimal:2',
        'profit_amount' => 'decimal:2',
        'responsible_user_id' => 'integer',
        'package_template_id' => 'integer',
        'package_price' => 'decimal:2',
        'package_deadline_days' => 'integer',
        'package_margin_percent' => 'decimal:2',
        'partner_discount_percent' => 'decimal:2',
        'partner_discount_amount' => 'decimal:2',
        'partner_invoice_id' => 'integer',
        'partner_metadata' => 'array',
        'promised_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(ClientsModel::class, 'client_id');
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function partnerInvoice()
    {
        return $this->belongsTo(PartnerInvoice::class, 'partner_invoice_id');
    }

    public function partnerInvoiceLines()
    {
        return $this->hasMany(PartnerInvoiceLine::class);
    }

    public function filial()
    {
        return $this->belongsTo(FilialModel::class, 'filial_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id')->withTrashed();
    }

    public function responsibleUser()
    {
        return $this->belongsTo(User::class, 'responsible_user_id')->withTrashed();
    }

    public function packageTemplate()
    {
        return $this->belongsTo(PackageTemplate::class, 'package_template_id');
    }

    public function documents()
    {
        return $this->hasMany(DocumentsModel::class, 'order_id');
    }

    public function priceLines()
    {
        return $this->hasMany(OrderPriceLine::class);
    }

    public function payments()
    {
        return $this->hasMany(PaymentsModel::class, 'order_id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function checklists()
    {
        return $this->hasMany(OrderChecklist::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(OrderStatusHistory::class)->latest();
    }

    public function deliveries()
    {
        return $this->hasMany(OrderDelivery::class)->latest();
    }

    public function notifications()
    {
        return $this->hasMany(OrderNotification::class)->latest();
    }

    public function pricingApprovals()
    {
        return $this->hasMany(PricingApproval::class);
    }

    public function paymentLinks()
    {
        return $this->hasMany(OrderPaymentLink::class)->latest();
    }

    public function supportTickets()
    {
        return $this->hasMany(OrderSupportTicket::class)->latest();
    }

    public function repeatedFrom()
    {
        return $this->belongsTo(self::class, 'repeat_of_order_id');
    }

    public function repeatedOrders()
    {
        return $this->hasMany(self::class, 'repeat_of_order_id');
    }

    public function costs()
    {
        return $this->hasMany(OrderCost::class)->latest();
    }

    public function workItems()
    {
        return $this->hasMany(WorkItem::class);
    }

    public function approvals()
    {
        return $this->morphMany(BusinessApproval::class, 'subject');
    }

    public function feedback()
    {
        return $this->hasOne(CustomerFeedback::class);
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if (! $user) {
            return $query->whereKey(-1);
        }

        if ($user->hasAnyRole(['super_admin', 'admin_manager'])) {
            return $query;
        }

        if ($user->hasAnyRole(['partner_admin', 'partner_operator'])) {
            return $user->partner_id
                ? $query->where('partner_id', $user->partner_id)
                : $query->whereKey(-1);
        }

        if ($user->filial_id === null) {
            return $query->whereKey(-1);
        }

        $query->where('filial_id', $user->filial_id);

        if ($user->hasRole('courier')) {
            return $query->whereHas('deliveries', fn (Builder $deliveries) => $deliveries->where('courier_id', $user->id));
        }

        if ($user->hasRole('employee')) {
            $query->where(function (Builder $visible) use ($user): void {
                $visible->where('created_by_id', $user->id)
                    ->orWhere('responsible_user_id', $user->id)
                    ->orWhereHas('documents', fn (Builder $documents) => $documents->where('user_id', $user->id));
            });
        }

        return $query;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getBalanceAmountAttribute(): float
    {
        return round(max((float) $this->total_amount - (float) $this->paid_amount, 0), 2);
    }

    public function getProfitMarginAttribute(): float
    {
        if ((float) $this->total_amount <= 0) {
            return 0;
        }

        return round(((float) $this->profit_amount / (float) $this->total_amount) * 100, 2);
    }
}

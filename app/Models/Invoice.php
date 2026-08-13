<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    public const STATUSES = ['issued', 'partially_paid', 'paid', 'cancelled'];

    protected $fillable = [
        'order_id',
        'client_id',
        'filial_id',
        'issued_by_id',
        'invoice_number',
        'status',
        'currency',
        'subtotal_amount',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'paid_amount',
        'balance_amount',
        'issued_at',
        'due_at',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'subtotal_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'issued_at' => 'datetime',
        'due_at' => 'date',
        'metadata' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function client()
    {
        return $this->belongsTo(ClientsModel::class, 'client_id');
    }

    public function filial()
    {
        return $this->belongsTo(FilialModel::class, 'filial_id');
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by_id')->withTrashed();
    }

    public function lines()
    {
        return $this->hasMany(InvoiceLine::class)->orderBy('id');
    }

    public function payments()
    {
        return $this->hasMany(PaymentsModel::class, 'invoice_id');
    }

    public function getBalanceAmountAttribute(): float
    {
        return round(max((float) $this->total_amount - (float) $this->paid_amount, 0), 2);
    }
}

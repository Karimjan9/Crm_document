<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerInvoice extends Model
{
    use HasFactory;

    public const STATUSES = ['draft', 'issued', 'partially_paid', 'paid', 'cancelled'];

    protected $fillable = [
        'partner_id',
        'invoice_number',
        'period_start',
        'period_end',
        'issued_at',
        'due_at',
        'status',
        'currency',
        'subtotal_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'payment_terms_days',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'issued_at' => 'datetime',
        'due_at' => 'date',
        'subtotal_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'payment_terms_days' => 'integer',
        'metadata' => 'array',
    ];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function lines()
    {
        return $this->hasMany(PartnerInvoiceLine::class);
    }

    public function getBalanceAmountAttribute(): float
    {
        return round(max((float) $this->total_amount - (float) $this->paid_amount, 0), 2);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerInvoiceLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_invoice_id',
        'order_id',
        'order_code',
        'description',
        'subtotal_amount',
        'discount_amount',
        'total_amount',
        'metadata',
    ];

    protected $casts = [
        'subtotal_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function invoice()
    {
        return $this->belongsTo(PartnerInvoice::class, 'partner_invoice_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

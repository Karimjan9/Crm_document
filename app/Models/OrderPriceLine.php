<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPriceLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'document_id',
        'line_type',
        'pricing_line_type',
        'source_id',
        'price_tariff_id',
        'name',
        'quantity',
        'unit_price',
        'total_price',
        'cost_amount',
        'metadata',
        'pricing_context',
        'effective_from',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'cost_amount' => 'decimal:2',
        'metadata' => 'array',
        'pricing_context' => 'array',
        'effective_from' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function document()
    {
        return $this->belongsTo(DocumentsModel::class, 'document_id');
    }

    public function tariff()
    {
        return $this->belongsTo(PriceTariff::class, 'price_tariff_id');
    }
}

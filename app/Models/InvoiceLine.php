<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'order_price_line_id',
        'document_id',
        'line_type',
        'description',
        'quantity',
        'unit_price',
        'total_amount',
        'metadata',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function orderPriceLine()
    {
        return $this->belongsTo(OrderPriceLine::class, 'order_price_line_id');
    }

    public function document()
    {
        return $this->belongsTo(DocumentsModel::class, 'document_id');
    }
}

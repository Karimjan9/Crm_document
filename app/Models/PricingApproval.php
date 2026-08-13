<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingApproval extends Model
{
    use HasFactory;

    public const STATUSES = ['pending', 'approved', 'rejected'];

    protected $fillable = [
        'document_id',
        'order_id',
        'requested_by_id',
        'approved_by_id',
        'status',
        'discount_percent',
        'discount_amount',
        'reason',
        'approved_at',
        'rejected_at',
        'metadata',
    ];

    protected $casts = [
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function document()
    {
        return $this->belongsTo(DocumentsModel::class, 'document_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }
}

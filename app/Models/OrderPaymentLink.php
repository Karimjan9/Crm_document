<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPaymentLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'token', 'amount', 'currency', 'provider', 'status',
        'expires_at', 'paid_at', 'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function getUrlAttribute(): string
    {
        return route('orders.portal.payment', ['paymentToken' => $this->token]);
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'pending' && (! $this->expires_at || $this->expires_at->isFuture());
    }
}

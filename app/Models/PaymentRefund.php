<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentRefund extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'refunded_by_id',
        'confirmed_by_id',
        'refund_number',
        'amount',
        'status',
        'reason',
        'confirmed_at',
        'cancelled_at',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function payment()
    {
        return $this->belongsTo(PaymentsModel::class, 'payment_id');
    }

    public function refundedBy()
    {
        return $this->belongsTo(User::class, 'refunded_by_id')->withTrashed();
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by_id')->withTrashed();
    }
}

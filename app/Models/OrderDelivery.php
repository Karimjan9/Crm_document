<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'courier_id',
        'assigned_by_id',
        'status',
        'tracking_code',
        'delivery_otp_hash',
        'recipient_name',
        'recipient_phone',
        'received_by',
        'address',
        'delivered_latitude',
        'delivered_longitude',
        'fee',
        'notes',
        'proof_path',
        'proof_original_name',
        'scheduled_at',
        'accepted_at',
        'picked_up_at',
        'delivered_at',
        'received_at',
        'returned_at',
    ];

    protected $casts = [
        'fee' => 'decimal:2',
        'scheduled_at' => 'datetime',
        'accepted_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
        'returned_at' => 'datetime',
        'received_at' => 'datetime',
        'delivered_latitude' => 'decimal:7',
        'delivered_longitude' => 'decimal:7',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function courier()
    {
        return $this->belongsTo(User::class, 'courier_id')->withTrashed();
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by_id')->withTrashed();
    }
}

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
        'recipient_name',
        'recipient_phone',
        'address',
        'fee',
        'notes',
        'scheduled_at',
        'accepted_at',
        'picked_up_at',
        'delivered_at',
        'returned_at',
    ];

    protected $casts = [
        'fee' => 'decimal:2',
        'scheduled_at' => 'datetime',
        'accepted_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
        'returned_at' => 'datetime',
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

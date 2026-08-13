<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderSupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'subject',
        'message',
        'contact',
        'status',
        'assigned_to_id',
        'resolved_at',
        'metadata',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_id')->withTrashed();
    }
}

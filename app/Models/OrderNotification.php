<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'event',
        'channel',
        'recipient',
        'message',
        'status',
        'attempts',
        'sent_at',
        'last_attempt_at',
        'error_message',
        'provider_message_id',
        'metadata',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'last_attempt_at' => 'datetime',
        'attempts' => 'integer',
        'metadata' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

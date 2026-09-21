<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotDeliveryReport extends Model
{
    protected $fillable = ['event_id', 'telegram_chat_id', 'telegram_message_id', 'status', 'reported_at'];
    protected $casts = ['reported_at' => 'datetime'];
}

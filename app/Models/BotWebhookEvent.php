<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotWebhookEvent extends Model
{
    protected $fillable = ['event_id', 'type', 'telegram_chat_id', 'telegram_message_id', 'payload', 'status', 'attempts', 'dispatched_at', 'last_error'];
    protected $casts = ['payload' => 'array', 'dispatched_at' => 'datetime'];
}

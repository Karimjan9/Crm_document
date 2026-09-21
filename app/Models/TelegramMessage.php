<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramMessage extends Model
{
    protected $fillable = ['client_id', 'lead_id', 'order_id', 'telegram_chat_id', 'telegram_message_id', 'event_id', 'direction', 'type', 'body', 'attachment_path', 'attachment_meta', 'file_expires_at', 'sent_by_id', 'sent_at', 'delivery_status', 'delivery_error', 'delivered_at'];
    protected $casts = ['attachment_meta' => 'array', 'sent_at' => 'datetime', 'delivered_at' => 'datetime', 'file_expires_at' => 'datetime'];
    public function client() { return $this->belongsTo(ClientsModel::class, 'client_id'); }
    public function lead() { return $this->belongsTo(Lead::class); }
    public function order() { return $this->belongsTo(Order::class); }
    public function sentBy() { return $this->belongsTo(User::class, 'sent_by_id')->withTrashed(); }
}

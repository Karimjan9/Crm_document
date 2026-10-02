<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatorRequest extends Model
{
    public const STATUS_LABELS = [
        'new' => 'Yangi',
        'contacted' => 'Bog‘lanildi',
        'resolved' => 'Yakunlandi',
    ];

    protected $fillable = [
        'external_id', 'client_id', 'lead_id', 'work_item_id', 'telegram_chat_id',
        'telegram_user_id', 'telegram_username', 'telegram_message_id', 'name', 'phone',
        'phone_verified', 'message', 'reason', 'status', 'handled_by_id', 'handled_at',
    ];

    protected $casts = ['phone_verified' => 'boolean', 'handled_at' => 'datetime'];

    public function handledBy()
    {
        return $this->belongsTo(User::class, 'handled_by_id')->withTrashed();
    }

    public function workItem()
    {
        return $this->belongsTo(WorkItem::class);
    }
}

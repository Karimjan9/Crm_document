<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotMarketingConsent extends Model
{
    protected $fillable = ['client_id', 'telegram_chat_id', 'consent', 'consented_at', 'revoked_at'];
    protected $casts = ['consent' => 'boolean', 'consented_at' => 'datetime', 'revoked_at' => 'datetime'];
    public function client() { return $this->belongsTo(ClientsModel::class, 'client_id'); }
}

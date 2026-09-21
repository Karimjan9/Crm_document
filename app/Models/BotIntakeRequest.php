<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotIntakeRequest extends Model
{
    protected $fillable = ['external_id', 'client_id', 'lead_id', 'payload'];
    protected $casts = ['payload' => 'array'];
    public function lead() { return $this->belongsTo(Lead::class); }
}

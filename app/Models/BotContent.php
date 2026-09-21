<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotContent extends Model
{
    protected $fillable = ['key', 'text', 'metadata', 'updated_by_id'];
    protected $casts = ['metadata' => 'array'];
    public function updatedBy() { return $this->belongsTo(User::class, 'updated_by_id')->withTrashed(); }
}

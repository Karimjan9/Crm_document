<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityAlert extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'context' => 'array',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'notified_at' => 'datetime',
    ];
}

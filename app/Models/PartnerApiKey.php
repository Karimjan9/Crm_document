<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerApiKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'name',
        'token_prefix',
        'token_hash',
        'abilities',
        'last_used_at',
        'expires_at',
        'revoked_at',
    ];

    protected $hidden = ['token_hash'];

    protected $casts = [
        'abilities' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->whereNull('revoked_at')
            ->where(function (Builder $active) {
                $active->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }
}

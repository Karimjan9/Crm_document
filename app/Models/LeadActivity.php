<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadActivity extends Model
{
    protected $fillable = ['lead_id', 'user_id', 'type', 'body', 'happened_at'];

    protected $casts = ['happened_at' => 'datetime'];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}

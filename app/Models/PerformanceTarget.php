<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerformanceTarget extends Model
{
    protected $fillable = ['filial_id', 'user_id', 'period_start', 'period_end', 'revenue_target', 'order_target', 'bonus_rate', 'weights'];

    protected $casts = ['period_start' => 'date', 'period_end' => 'date', 'revenue_target' => 'decimal:2', 'order_target' => 'integer', 'bonus_rate' => 'decimal:2', 'weights' => 'array'];

    public function filial()
    {
        return $this->belongsTo(FilialModel::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}

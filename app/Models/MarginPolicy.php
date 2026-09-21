<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarginPolicy extends Model
{
    protected $fillable = ['filial_id', 'service_id', 'minimum_margin_percent', 'refund_auto_approve_limit', 'is_active'];

    protected $casts = ['minimum_margin_percent' => 'decimal:2', 'refund_auto_approve_limit' => 'decimal:2', 'is_active' => 'boolean'];

    public function filial()
    {
        return $this->belongsTo(FilialModel::class);
    }

    public function service()
    {
        return $this->belongsTo(ServicesModel::class, 'service_id');
    }
}

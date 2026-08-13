<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostCenter extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'filial_id', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function filial()
    {
        return $this->belongsTo(FilialModel::class, 'filial_id');
    }

    public function expenses()
    {
        return $this->hasMany(ExpenseAdminModel::class, 'cost_center_id');
    }
}

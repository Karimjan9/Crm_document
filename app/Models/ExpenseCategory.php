<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'expense_type', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function expenses()
    {
        return $this->hasMany(ExpenseAdminModel::class, 'category_id');
    }

    public function budgets()
    {
        return $this->hasMany(Budget::class, 'category_id');
    }
}

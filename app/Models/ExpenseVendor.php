<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseVendor extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'phone', 'email', 'tax_id', 'notes', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function expenses()
    {
        return $this->hasMany(ExpenseAdminModel::class, 'vendor_id');
    }
}

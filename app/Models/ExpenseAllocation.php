<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseAllocation extends Model
{
    use HasFactory;

    protected $fillable = ['expense_id', 'filial_id', 'amount', 'percentage', 'note'];

    protected $casts = [
        'amount' => 'decimal:2',
        'percentage' => 'decimal:3',
    ];

    public function expense()
    {
        return $this->belongsTo(ExpenseAdminModel::class, 'expense_id');
    }

    public function filial()
    {
        return $this->belongsTo(FilialModel::class, 'filial_id');
    }
}

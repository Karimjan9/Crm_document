<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseAdminModel extends Model
{
    use HasFactory;
    protected $table = 'expense_admin';

    protected $fillable = [
        'user_id',
        'amount',
        'filial_id',
        'description'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function filial()
    {
        return $this->belongsTo(FilialModel::class);
    }
    
}

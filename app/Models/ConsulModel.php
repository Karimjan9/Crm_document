<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsulModel extends Model
{
    use HasFactory;
    protected $table = 'consul';
    protected $fillable = [
        'name',
        'amount',
        'day'
    ];
    
}

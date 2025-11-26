<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilialModel extends Model
{
    use HasFactory;
    protected $table='filial';

    protected $fillable=[
        'name',
        'code',
        'description'
    ];
}

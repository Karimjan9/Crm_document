<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApostilStatikModel extends Model
{
    use HasFactory;
    protected $table = 'apostil_static';
    protected $fillable = [
        'name',
        'price',
        'group_id',
        'days'
    ];

}

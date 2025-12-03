<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DirectionTypeModel extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'direction_type';
    protected $fillable = [
        'name',
        'description',
    ];
}

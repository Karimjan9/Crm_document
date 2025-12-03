<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ConsulationTypeModel extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'consulates_type';

    protected $fillable = [
        'name',
        'description',
    ];
    
}

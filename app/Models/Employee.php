<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'position',
        'filial_id',
    ];

    // Filial bilan bog‘lanishi
    public function filial()
    {
        return $this->belongsTo(Filial::class);
    }
}

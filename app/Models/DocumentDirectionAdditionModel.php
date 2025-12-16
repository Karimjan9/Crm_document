<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentDirectionAdditionModel extends Model
{
    use HasFactory;
    protected $table = 'document_direction_addition';
    protected $fillable = [
        'document_direction_id',
        'name',
        'description',
        'amount',
    ];


    public function directionType()
    {
        return $this->belongsTo(DirectionTypeModel::class, 'document_direction_id');
    }


}

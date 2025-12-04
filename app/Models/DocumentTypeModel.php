<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DocumentTypeModel extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'document_type';
    protected $fillable = [
        'name',
        'description',
    ];
}

<?php

namespace App\Models;

use App\Models\DocumentTypeModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DocumentTypeAdditionModel extends Model
{
    use HasFactory; 
    protected $table = 'document_type_addition';
    protected $fillable = [
        'document_type_id',
        'name',
        'description',
        'amount',
    ];  
    public function documentType()
    {
        return $this->belongsTo(DocumentTypeModel::class, 'document_type_id');
    }
}

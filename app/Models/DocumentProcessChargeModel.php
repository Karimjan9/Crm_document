<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentProcessChargeModel extends Model
{
    use HasFactory;

    protected $table = 'document_process_charges';

    protected $fillable = [
        'document_id',
        'charge_type',
        'source_id',
        'price',
        'days',
        'name',
    ];

    public function document()
    {
        return $this->belongsTo(DocumentsModel::class, 'document_id');
    }
}

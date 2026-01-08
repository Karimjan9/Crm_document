<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentFileModel extends Model
{
    use HasFactory;

    protected $table='document_files';

    protected $fillable = [
        'document_id',
        'original_name',
        'file_path',
        'file_type',
        'file_size',
    ];

    public function document()
    {
        return $this->belongsTo(DocumentsModel::class, 'document_id');
    }

    // Получить полный URL файла
    public function getFileUrlAttribute()
    {
        return Storage::url($this->file_path);
    }
}

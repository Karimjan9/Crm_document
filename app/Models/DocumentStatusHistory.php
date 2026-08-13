<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'from_status',
        'to_status',
        'changed_by_id',
        'reason',
        'comment',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function document()
    {
        return $this->belongsTo(DocumentsModel::class, 'document_id');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by_id')->withTrashed();
    }
}

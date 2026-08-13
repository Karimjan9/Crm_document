<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentQaReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'reviewer_id',
        'result',
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

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id')->withTrashed();
    }
}

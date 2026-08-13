<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentCustodyEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id', 'from_user_id', 'to_user_id', 'signed_by_id', 'event_type', 'event_at',
        'signature', 'previous_signature', 'photo_path', 'notes', 'metadata',
    ];

    protected $casts = [
        'event_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function document()
    {
        return $this->belongsTo(DocumentsModel::class, 'document_id');
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id')->withTrashed();
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id')->withTrashed();
    }

    public function signedBy()
    {
        return $this->belongsTo(User::class, 'signed_by_id')->withTrashed();
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? route('document-custody.photo', $this) : null;
    }
}

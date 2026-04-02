<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentCourier extends Model
{
    use HasFactory;

    protected $table = 'document_couriers';

    protected $fillable = [
        'document_id',
        'courier_id',
        'sent_by_id',
        'status',
        'sent_comment',
        'courier_comment',
        'return_comment',
        'sent_at',
        'accepted_at',
        'rejected_at',
        'returned_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function document()
    {
        return $this->belongsTo(DocumentsModel::class, 'document_id');
    }

    public function courier()
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    public function sentBy()
    {
        return $this->belongsTo(User::class, 'sent_by_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentAssignmentHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id', 'assigned_to_id', 'qa_user_id', 'assigned_by_id',
        'queue', 'priority', 'estimated_workload_minutes', 'source', 'notes', 'metadata',
    ];

    protected $casts = [
        'estimated_workload_minutes' => 'integer',
        'metadata' => 'array',
    ];

    public function document()
    {
        return $this->belongsTo(DocumentsModel::class, 'document_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_id')->withTrashed();
    }

    public function qaUser()
    {
        return $this->belongsTo(User::class, 'qa_user_id')->withTrashed();
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by_id')->withTrashed();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentChecklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'service_id',
        'service_checklist_item_id',
        'code',
        'title',
        'description',
        'is_required',
        'requires_file',
        'is_completed',
        'completed_by_id',
        'completed_at',
        'notes',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'requires_file' => 'boolean',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function document()
    {
        return $this->belongsTo(DocumentsModel::class, 'document_id');
    }

    public function service()
    {
        return $this->belongsTo(ServicesModel::class, 'service_id');
    }

    public function serviceItem()
    {
        return $this->belongsTo(ServiceChecklistItem::class, 'service_checklist_item_id');
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by_id')->withTrashed();
    }
}

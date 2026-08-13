<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceChecklistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'code',
        'title',
        'description',
        'is_required',
        'requires_file',
        'is_active',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'requires_file' => 'boolean',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    public function service()
    {
        return $this->belongsTo(ServicesModel::class, 'service_id');
    }

    public function documentChecklists()
    {
        return $this->hasMany(DocumentChecklist::class, 'service_checklist_item_id');
    }
}

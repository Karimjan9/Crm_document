<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderChecklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'document_id',
        'title',
        'is_required',
        'is_completed',
        'sort_order',
        'completed_by_id',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function document()
    {
        return $this->belongsTo(DocumentsModel::class, 'document_id');
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by_id')->withTrashed();
    }
}

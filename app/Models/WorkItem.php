<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class WorkItem extends Model
{
    public const STATUSES = ['open', 'in_progress', 'blocked', 'done', 'cancelled'];

    public const PRIORITIES = ['low', 'normal', 'high', 'urgent'];

    protected $fillable = ['filial_id', 'order_id', 'document_id', 'lead_id', 'assigned_to_id', 'created_by_id', 'assigned_role', 'type', 'title', 'description', 'status', 'priority', 'due_at', 'escalated_at', 'completed_at', 'metadata'];

    protected $casts = ['due_at' => 'datetime', 'escalated_at' => 'datetime', 'completed_at' => 'datetime', 'metadata' => 'array'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function document()
    {
        return $this->belongsTo(DocumentsModel::class, 'document_id');
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_id')->withTrashed();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id')->withTrashed();
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', ['open', 'in_progress', 'blocked']);
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if (! $user) {
            return $query->whereKey(-1);
        }
        if ($user->hasAnyRole(['super_admin', 'admin_manager'])) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($user): void {
            $q->where('assigned_to_id', $user->id)
                ->orWhere(fn (Builder $role) => $role->whereNull('assigned_to_id')->where('assigned_role', $user->getRoleNames()->first()));
        })->when($user->filial_id, fn (Builder $q) => $q->where('filial_id', $user->filial_id));
    }
}

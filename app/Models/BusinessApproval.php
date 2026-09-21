<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessApproval extends Model
{
    public const STATUSES = ['pending', 'approved', 'rejected', 'cancelled'];

    protected $fillable = ['filial_id', 'subject_type', 'subject_id', 'requested_by_id', 'resolved_by_id', 'type', 'status', 'amount', 'projected_margin_percent', 'required_role', 'reason', 'resolution_note', 'payload', 'due_at', 'resolved_at'];

    protected $casts = ['amount' => 'decimal:2', 'projected_margin_percent' => 'decimal:2', 'payload' => 'array', 'due_at' => 'datetime', 'resolved_at' => 'datetime'];

    public function subject()
    {
        return $this->morphTo();
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by_id')->withTrashed();
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by_id')->withTrashed();
    }

    public function filial()
    {
        return $this->belongsTo(FilialModel::class);
    }
}

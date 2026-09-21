<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowResponsibilityRule extends Model
{
    protected $fillable = ['filial_id', 'trigger_status', 'responsible_role', 'action_type', 'title', 'due_hours', 'escalate_to_role', 'is_active'];

    protected $casts = ['due_hours' => 'integer', 'is_active' => 'boolean'];
}

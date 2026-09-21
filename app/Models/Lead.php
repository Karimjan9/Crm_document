<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    public const STATUSES = ['new', 'contacted', 'qualified', 'quoted', 'won', 'lost'];

    protected $fillable = [
        'filial_id', 'client_id', 'assigned_to_id', 'converted_order_id', 'name', 'phone', 'source',
        'campaign', 'interested_service', 'status', 'estimated_amount', 'quoted_amount', 'lost_reason',
        'first_responded_at', 'next_follow_up_at', 'quoted_at', 'bot_follow_up_sent_at', 'bot_follow_up_event_id', 'won_at', 'lost_at', 'notes',
    ];

    protected $casts = [
        'estimated_amount' => 'decimal:2', 'quoted_amount' => 'decimal:2',
        'first_responded_at' => 'datetime', 'next_follow_up_at' => 'datetime', 'quoted_at' => 'datetime', 'bot_follow_up_sent_at' => 'datetime',
        'won_at' => 'datetime', 'lost_at' => 'datetime',
    ];

    public function filial()
    {
        return $this->belongsTo(FilialModel::class, 'filial_id');
    }

    public function client()
    {
        return $this->belongsTo(ClientsModel::class, 'client_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_id')->withTrashed();
    }

    public function convertedOrder()
    {
        return $this->belongsTo(Order::class, 'converted_order_id');
    }

    public function activities()
    {
        return $this->hasMany(LeadActivity::class)->latest('happened_at');
    }

    public function workItems()
    {
        return $this->hasMany(WorkItem::class);
    }

    public function telegramMessages()
    {
        return $this->hasMany(TelegramMessage::class)->latest();
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if (! $user) {
            return $query->whereKey(-1);
        }
        if ($user->hasAnyRole(['super_admin', 'admin_manager'])) {
            return $query;
        }
        if ($user->filial_id === null) {
            return $query->whereKey(-1);
        }
        $query->where('filial_id', $user->filial_id);

        return $user->hasRole('employee') ? $query->where('assigned_to_id', $user->id) : $query;
    }
}

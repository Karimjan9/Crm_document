<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashSession extends Model
{
    use HasFactory;

    public const STATUSES = ['open', 'closed', 'reconciled'];

    protected $fillable = [
        'filial_id',
        'cashier_id',
        'opened_by_id',
        'closed_by_id',
        'session_date',
        'status',
        'opening_balance',
        'expected_cash',
        'actual_cash',
        'variance',
        'opened_at',
        'closed_at',
        'reconciled_at',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'session_date' => 'date',
        'opening_balance' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'actual_cash' => 'decimal:2',
        'variance' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'reconciled_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function filial()
    {
        return $this->belongsTo(FilialModel::class, 'filial_id');
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id')->withTrashed();
    }

    public function openedBy()
    {
        return $this->belongsTo(User::class, 'opened_by_id')->withTrashed();
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by_id')->withTrashed();
    }

    public function payments()
    {
        return $this->hasMany(PaymentsModel::class, 'cash_session_id');
    }

    public function reconciliation()
    {
        return $this->hasOne(CashReconciliation::class, 'cash_session_id');
    }
}

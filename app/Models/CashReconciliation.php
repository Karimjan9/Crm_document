<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashReconciliation extends Model
{
    use HasFactory;

    protected $fillable = [
        'filial_id', 'reconciliation_date', 'expected_amount', 'actual_amount',
        'difference', 'status', 'recorded_by_id', 'cash_session_id', 'notes',
    ];

    protected $casts = [
        'reconciliation_date' => 'date',
        'expected_amount' => 'decimal:2',
        'actual_amount' => 'decimal:2',
        'difference' => 'decimal:2',
    ];

    public function filial()
    {
        return $this->belongsTo(FilialModel::class, 'filial_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by_id')->withTrashed();
    }

    public function cashSession()
    {
        return $this->belongsTo(CashSession::class, 'cash_session_id');
    }
}

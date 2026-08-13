<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'filial_id', 'category_id', 'cost_center_id',
        'period_start', 'period_end', 'amount', 'status', 'notes',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'amount' => 'decimal:2',
    ];

    public function filial()
    {
        return $this->belongsTo(FilialModel::class, 'filial_id');
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id');
    }

    public function expenses()
    {
        return $this->hasMany(ExpenseAdminModel::class, 'budget_id');
    }

    public function getSpentAmountAttribute(): float
    {
        return (float) $this->expenses()
            ->where('approval_status', '<>', 'rejected')
            ->where(function ($query): void {
                $query->whereDate('expense_date', '>=', $this->period_start?->toDateString())
                    ->whereDate('expense_date', '<=', $this->period_end?->toDateString())
                    ->orWhere(function ($fallback): void {
                        $fallback->whereNull('expense_date')
                            ->whereDate('created_at', '>=', $this->period_start?->toDateString())
                            ->whereDate('created_at', '<=', $this->period_end?->toDateString());
                    });
            })
            ->sum('amount');
    }

    public function getRemainingAmountAttribute(): float
    {
        return round((float) $this->amount - $this->spent_amount, 2);
    }
}

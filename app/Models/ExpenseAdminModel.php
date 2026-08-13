<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseAdminModel extends Model
{
    use HasFactory;
    protected $table = 'expense_admin';

    protected $fillable = [
        'user_id',
        'amount',
        'filial_id',
        'description',
        'category_id',
        'vendor_id',
        'payment_method',
        'receipt_path',
        'receipt_original_name',
        'approver_id',
        'approved_at',
        'approval_status',
        'is_recurring',
        'recurrence_rule',
        'recurrence_start',
        'recurrence_end',
        'next_occurrence',
        'budget_id',
        'cost_center_id',
        'expense_type',
        'expense_date',
        'currency',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'is_recurring' => 'boolean',
        'recurrence_start' => 'date',
        'recurrence_end' => 'date',
        'next_occurrence' => 'date',
        'expense_date' => 'date',
        'metadata' => 'array',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function filial()
    {
        return $this->belongsTo(FilialModel::class);
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function vendor()
    {
        return $this->belongsTo(ExpenseVendor::class, 'vendor_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id')->withTrashed();
    }

    public function budget()
    {
        return $this->belongsTo(Budget::class, 'budget_id');
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id');
    }

    public function allocations()
    {
        return $this->hasMany(ExpenseAllocation::class, 'expense_id');
    }

    public function getEffectiveExpenseDateAttribute()
    {
        return $this->expense_date ?: $this->created_at?->toDateString();
    }

    public function getReceiptUrlAttribute(): ?string
    {
        return $this->receipt_path
            ? route('expenses.receipt', ['expense' => $this->getKey()])
            : null;
    }
    
}

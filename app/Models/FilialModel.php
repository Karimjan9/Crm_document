<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilialModel extends Model
{
    use HasFactory;

    protected $table = 'filial';

    protected $fillable = [
        'name',
        'code',
        'description',
        'phone',
        'address',
        'manager_id',
        'work_start_time',
        'work_end_time',
        'working_days',
        'holiday_dates',
        'monthly_expense',
        'target_amount',
        'commission_percent',
        'daily_capacity',
    ];

    protected $casts = [
        'manager_id' => 'integer',
        'working_days' => 'array',
        'holiday_dates' => 'array',
        'monthly_expense' => 'decimal:2',
        'target_amount' => 'decimal:2',
        'commission_percent' => 'decimal:2',
        'daily_capacity' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (FilialModel $filial): void {
            if ($filial->working_days === null) {
                $filial->working_days = [1, 2, 3, 4, 5, 6];
            }
        });
    }

    public function payments()
    {
        return $this->hasMany(PaymentsModel::class, 'filial_id');
    }

    public function cashSessions()
    {
        return $this->hasMany(CashSession::class, 'filial_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'filial_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id')->withTrashed();
    }

    public function users()
    {
        return $this->hasMany(User::class, 'filial_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'filial_id');
    }

    public function expenses()
    {
        return $this->hasMany(ExpenseAdminModel::class, 'filial_id');
    }

    public function priceTariffs()
    {
        return $this->hasMany(PriceTariff::class, 'filial_id');
    }
}

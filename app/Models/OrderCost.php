<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderCost extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'category',
        'amount',
        'description',
        'recorded_by_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by_id')->withTrashed();
    }
}

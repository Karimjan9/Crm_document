<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarginLeak extends Model
{
    use HasFactory;

    protected $fillable = [
        'fingerprint', 'leak_type', 'severity', 'order_id', 'filial_id',
        'amount', 'message', 'metadata', 'detected_at', 'resolved_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'detected_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function filial()
    {
        return $this->belongsTo(FilialModel::class, 'filial_id');
    }
}

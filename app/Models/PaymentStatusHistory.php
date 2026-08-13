<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'from_status',
        'to_status',
        'changed_by_id',
        'reason',
        'metadata',
    ];

    protected $casts = ['metadata' => 'array'];

    public function payment()
    {
        return $this->belongsTo(PaymentsModel::class, 'payment_id');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by_id')->withTrashed();
    }
}

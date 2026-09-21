<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerFeedback extends Model
{
    protected $fillable = ['order_id', 'client_id', 'rating', 'nps_score', 'comment', 'submitted_at'];

    protected $casts = ['submitted_at' => 'datetime'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function client()
    {
        return $this->belongsTo(ClientsModel::class, 'client_id');
    }
}

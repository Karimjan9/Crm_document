<?php

namespace App\Models;


use App\Models\ClientsModel;
use App\Models\PaymentsModel;
use App\Models\ServicesModel;
use App\Models\ServicesAddonsModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DocumentsModel extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'client_id','service_id','service_price','addons_total_price',
        'deadline_time','final_price','paid_amount','discount'
    ];

    public function client() {
        return $this->belongsTo(ClientsModel::class);
    }

    public function service() {
        return $this->belongsTo(ServicesModel::class);
    }

    public function addons() {
        return $this->belongsToMany(ServicesAddonsModel::class, 'document_addons')
                    ->withPivot('addon_price','addon_deadline');
    }

    public function payments() {
        return $this->hasMany(PaymentsModel::class);
    }
}

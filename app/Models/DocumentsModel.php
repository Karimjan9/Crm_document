<?php

namespace App\Models;


use Carbon\Carbon;
use App\Models\ClientsModel;
use App\Models\PaymentsModel;
use App\Models\ServicesModel;
use App\Models\ServiceAddonModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DocumentsModel extends Model
{
    protected $table='documents';
    use HasFactory;
    
    protected $fillable = [
        'client_id',
        'service_id',
        'service_price',
        'addons_total_price',
        'deadline_time',
        'final_price',
        'paid_amount',
        'discount',
        'user_id',
        'description',
        'filial_id',
        'document_code'
    ];

    public function client() {
        return $this->belongsTo(ClientsModel::class);
    }

    public function service() {
        return $this->belongsTo(ServicesModel::class);
    }

   public function addons()
{
    return $this->belongsToMany(
        ServiceAddonModel::class,
        'document_addons',
        'document_id',
        'addon_id'
    )->withPivot('addon_price', 'addon_deadline');
}

    public function payments() {
        return $this->hasMany(PaymentsModel::class,'document_id','id');
    }
    public function user() {
        return $this->belongsTo(User::class,'user_id','id');
    }



public function getDeadlineRemainingAttribute()
{
    // Deadline kunlarda berilgan + 2 soat qo‘shish
    $deadline = $this->created_at->copy()->addDays($this->deadline_time)->addHours(2);
    $now = Carbon::now();

    if ($now->greaterThanOrEqualTo($deadline)) {
        return '0 kun';
    }

    $diffInSeconds = $deadline->diffInSeconds($now);

    $days = floor($diffInSeconds / 86400);      // 1 kun = 86400 sekund
    $hours = floor($diffInSeconds / 3600);      // qolgan vaqt soatlarda

    if ($diffInSeconds >= 86400) {
        return $days . '-kun';
    }

    return $hours . ' soat';
}

}

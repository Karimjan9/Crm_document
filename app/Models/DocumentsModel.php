<?php

namespace App\Models;


use Carbon\Carbon;
use App\Models\ClientsModel;
use App\Models\PaymentsModel;
use App\Models\ServicesModel;
use App\Models\ServiceAddonModel;
use App\Models\DocumentTypeAdditionModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DocumentsModel extends Model
{
    protected $table='documents';
    use HasFactory;

    protected $fillable = [
        'client_id',
        'service_id',
        'document_type_id',
        'direction_type_id',
        'consulate_type_id',
        'service_price',
        'addons_total_price',
        'deadline_time',
        'final_price',
        'paid_amount',
        'discount',
        'user_id',
        'description',
        'filial_id',
        'document_code',
        'status_doc'
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

    public function document_type_addons()
    {
        return $this->belongsToMany(
            DocumentTypeAdditionModel::class,
            'document_type_addons',
            'document_id',
            'addon_id'
        )->withPivot('addon_price');
    }

    public function document_direction_addons()
    {
        return $this->belongsToMany(
            DocumentDirectionAdditionModel::class,
            'document_direction_addons',
            'document_id',
            'addon_id'
        )->withPivot('addon_price');
    }

    public function payments() {
        return $this->hasMany(PaymentsModel::class,'document_id','id');
    }
    public function user() {
        return $this->belongsTo(User::class,'user_id','id');
    }



public function getDeadlineRemainingAttribute()
{
    $deadline = $this->created_at
                    ->copy()
                    ->addDays($this->deadline_time)
                    ->addHours(2);

    $now = Carbon::now();

    // Muddat O'TGAN bo'lsa
    if ($now->greaterThan($deadline)) {
        $diffHours = floor($deadline->diffInSeconds($now) / 3600);

        // 24 soatdan kam bo'lsa -> soatlarda minus bilan
        if ($diffHours < 24) {
            return '-' . $diffHours . " soat o'tgan";
        }

        // 24 soatdan oshsa -> kunlarda (faqat floor)
        $days = floor($diffHours / 24);
        return '-' . $days . " kun o'tgan";
    }

    // Muddat hali kelmagan bo'lsa
    $diffHours = floor($deadline->diffInSeconds($now) / 3600);

    // 24 soatdan ko'p bo'lsa -> kunlarda
    if ($diffHours >= 24) {
        $days = floor($diffHours / 24);
        return $days . ' kun';
    }

    // 24 soatdan kam bo'lsa -> soatlarda
    return $diffHours . ' soat';
}

public function documentType()
{
    return $this->belongsTo(DocumentTypeModel::class, 'document_type_id');
}

public function directionType()
{
    return $this->belongsTo(DirectionTypeModel::class, 'direction_type_id');
}

public function consulateType()
{
    return $this->belongsTo(ConsulationTypeModel::class, 'consulate_type_id');
}

public function files()
{
    return $this->hasMany(DocumentFileModel::class, 'document_id');
}

}

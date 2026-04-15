<?php

namespace App\Models;


use Carbon\Carbon;
use App\Support\WorkdayCalendar;
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
        'status_doc',
        'process_mode',
        'apostil_group1_id',
        'apostil_group2_id',
        'consul_id',
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

    public function filial()
    {
        return $this->belongsTo(FilialModel::class, 'filial_id');
    }



public function getDeadlineRemainingAttribute()
{
    return WorkdayCalendar::formatRemaining($this->deadline_due_at);
}

public function getDeadlineDueAtAttribute(): Carbon
{
    return WorkdayCalendar::resolveDueAt($this->created_at, $this->deadline_time);
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

    public function processCharges()
    {
        return $this->hasMany(DocumentProcessChargeModel::class, 'document_id');
    }

    public function courierAssignment()
    {
        return $this->hasOne(DocumentCourier::class, 'document_id');
    }

}

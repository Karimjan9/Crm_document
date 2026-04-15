<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageTemplateItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_template_id',
        'document_type_id',
        'service_id',
        'process_mode',
        'selection_mode',
        'direction_type_id',
        'apostil_group1_id',
        'apostil_group2_id',
        'consul_id',
        'consulate_type_id',
        'selected_addons',
        'base_price',
        'sort_order',
    ];

    protected $casts = [
        'selected_addons' => 'array',
        'base_price' => 'float',
    ];

    public function packageTemplate()
    {
        return $this->belongsTo(PackageTemplate::class, 'package_template_id');
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentTypeModel::class, 'document_type_id');
    }

    public function service()
    {
        return $this->belongsTo(ServicesModel::class, 'service_id');
    }

    public function directionType()
    {
        return $this->belongsTo(DirectionTypeModel::class, 'direction_type_id');
    }

    public function apostilGroup1()
    {
        return $this->belongsTo(ApostilStatikModel::class, 'apostil_group1_id');
    }

    public function apostilGroup2()
    {
        return $this->belongsTo(ApostilStatikModel::class, 'apostil_group2_id');
    }

    public function consul()
    {
        return $this->belongsTo(ConsulModel::class, 'consul_id');
    }

    public function consulateType()
    {
        return $this->belongsTo(ConsulationTypeModel::class, 'consulate_type_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'highlight',
        'description',
        'process_mode',
        'selection_mode',
        'document_type_id',
        'service_id',
        'direction_type_id',
        'apostil_group1_id',
        'apostil_group2_id',
        'consul_id',
        'consulate_type_id',
        'selected_addons',
        'base_price',
        'promo_price',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'selected_addons' => 'array',
        'base_price' => 'float',
        'promo_price' => 'float',
        'is_active' => 'boolean',
    ];

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

    public function items()
    {
        return $this->hasMany(PackageTemplateItem::class, 'package_template_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}

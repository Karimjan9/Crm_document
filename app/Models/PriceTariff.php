<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceTariff extends Model
{
    use HasFactory;

    public const LINE_TYPES = [
        'base_service',
        'addon',
        'apostille',
        'consulate',
        'courier',
        'express_fee',
        'tax',
    ];

    public const VARIANTS = [
        'standard',
        'express',
        'rush',
        'corporate',
        'seasonal',
    ];

    protected $fillable = [
        'line_type',
        'service_id',
        'service_addon_id',
        'source_id',
        'price_key',
        'filial_id',
        'partner_id',
        'variant',
        'season_code',
        'name',
        'price',
        'cost_amount',
        'deadline',
        'currency',
        'effective_from',
        'effective_to',
        'priority',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_amount' => 'decimal:2',
        'deadline' => 'integer',
        'effective_from' => 'datetime',
        'effective_to' => 'datetime',
        'priority' => 'integer',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    public function service()
    {
        return $this->belongsTo(ServicesModel::class);
    }

    public function serviceAddon()
    {
        return $this->belongsTo(ServiceAddonModel::class, 'service_addon_id');
    }

    public function filial()
    {
        return $this->belongsTo(FilialModel::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function orderPriceLines()
    {
        return $this->hasMany(OrderPriceLine::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}

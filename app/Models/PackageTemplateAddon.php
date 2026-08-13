<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageTemplateAddon extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_template_id',
        'service_addon_id',
        'is_included',
        'quantity',
        'price_override',
        'deadline_days_override',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_included' => 'boolean',
        'quantity' => 'integer',
        'price_override' => 'float',
        'deadline_days_override' => 'integer',
        'is_active' => 'boolean',
    ];

    public function packageTemplate()
    {
        return $this->belongsTo(PackageTemplate::class);
    }

    public function serviceAddon()
    {
        return $this->belongsTo(ServiceAddonModel::class);
    }
}

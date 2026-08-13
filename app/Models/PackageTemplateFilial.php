<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageTemplateFilial extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_template_id',
        'filial_id',
        'is_available',
        'standard_price',
        'express_price',
        'standard_deadline_days',
        'express_deadline_days',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'standard_price' => 'float',
        'express_price' => 'float',
        'standard_deadline_days' => 'integer',
        'express_deadline_days' => 'integer',
    ];

    public function packageTemplate()
    {
        return $this->belongsTo(PackageTemplate::class);
    }

    public function filial()
    {
        return $this->belongsTo(FilialModel::class);
    }
}

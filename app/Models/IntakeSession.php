<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntakeSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'token', 'client_id', 'filial_id', 'current_step', 'answers',
        'recommended_service_id', 'estimated_price', 'estimated_deadline_days',
        'required_files', 'recommended_addons', 'status', 'completed_at',
    ];

    protected $casts = [
        'answers' => 'array',
        'estimated_price' => 'decimal:2',
        'required_files' => 'array',
        'recommended_addons' => 'array',
        'completed_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(ClientsModel::class, 'client_id');
    }

    public function filial()
    {
        return $this->belongsTo(FilialModel::class, 'filial_id');
    }

    public function recommendedService()
    {
        return $this->belongsTo(ServicesModel::class, 'recommended_service_id');
    }

    public function ocrDocuments()
    {
        return $this->hasMany(IntakeOcrDocument::class, 'intake_session_id')->latest();
    }
}

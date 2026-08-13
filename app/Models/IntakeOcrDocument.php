<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntakeOcrDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'intake_session_id', 'uploaded_by_id', 'file_path', 'original_name',
        'status', 'provider', 'confidence', 'extracted_data', 'approved_by_id',
        'approved_at', 'approval_note', 'error_message',
    ];

    protected $casts = [
        'confidence' => 'decimal:2',
        'extracted_data' => 'array',
        'approved_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(IntakeSession::class, 'intake_session_id');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by_id')->withTrashed();
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_id')->withTrashed();
    }
}

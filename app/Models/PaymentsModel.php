<?php

namespace App\Models;

use App\Models\DocumentsModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentsModel extends Model
{
    use HasFactory;
    protected $fillable = ['document_id','amount','payment_type','paid_by_admin_id'];

    public function document() {
        return $this->belongsTo(DocumentsModel::class);
    }
}

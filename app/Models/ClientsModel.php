<?php

namespace App\Models;

use App\Models\DocumentsModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClientsModel extends Model
{
    use HasFactory;
    protected $table='clients';
     protected $fillable = ['name','phone_number','description'];
    public function documents() {
        return $this->hasMany(DocumentsModel::class, 'client_id');
    }
    
}

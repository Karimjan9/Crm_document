<?php

namespace App\Models;

use App\Models\ServicesModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServicesAddonsModel extends Model
{
    use HasFactory;
    protected $table='service_addons';
      protected $fillable = ['service_id','name','description','price','deadline'];
    public function service() {
        return $this->belongsTo(ServicesModel::class);
    }
}

<?php

namespace App\Models;

use App\Models\ServiceAddonModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServicesModel extends Model
{
    use HasFactory;
    protected $table='services';

     protected $fillable = ['name','description','price','deadline'];
    public function addons() {
        return $this->hasMany(ServiceAddonModel::class, 'service_id');
    }
}

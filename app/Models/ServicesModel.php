<?php

namespace App\Models;

use App\Models\ServicesAddonsModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServicesModel extends Model
{
    use HasFactory;

     protected $fillable = ['name','description','price','deadline'];
    public function addons() {
        return $this->hasMany(ServicesAddonsModel::class);
    }
}

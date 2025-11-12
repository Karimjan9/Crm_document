<?php

namespace App\Models;


use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
/**
 * @method bool hasRole(string|array $roles)
 * @method bool hasAnyRole(array|string $roles)
 * @method bool hasAllRoles(array|string $roles)
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable,HasRoles;

    use SoftDeletes;
    protected $fillable = [
        'name',
        'filial_id',
        'login',
        'phone',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

   
   
     public function filial()
    {
        return $this->belongsTo(FilialModel::class);
    }

    // public function hasRole($role)
    // {
    //     return in_array($role, $this->roles->pluck('name')->toArray());
    // }
   

   


    
    
    
    

}

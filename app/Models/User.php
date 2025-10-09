<?php

namespace App\Models;


use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable,HasRoles;


    protected $fillable = [
        'name',
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

   
   
    // public function hasRole($role) {
    //     $counter=0;
    //     if (is_array($role)) {
    //        foreach ($role as $key => $rol) {
    //             if ($this->user_level->name == $rol) {
    //                 $counter=$counter+1;
                    
    //             }
    //        }
    //        if ($counter==1) {
    //             return true;
    //        }
    //        return false;
    //     }else{
    //         if ($this->user_level->name == $role) {

    //             return true;
    //         }
    
    //         return false;
    //     }
       
    // }


   

   


    
    
    
    

}

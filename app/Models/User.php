<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;


    protected $fillable = [
        'login',
        'full_name',
        'email',
        'level_id',
        'password',
        'departament_id',
        'career_id',
        'jshir',
        'surname',
        'other_name',
        'birth_date',
        'birth_place',
        'nation',
        'gender',
        'number_phone',
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

    public function user_level() 
    {
        return $this->hasOne(UserLevel::class, 'id', 'level_id');    
    }
    public function user_department() 
    {
        return $this->hasOne(DepartamentUpdatedModel::class, 'id', 'departament_id');    
    }
    public function hasRole($role) {
        $counter=0;
        if (is_array($role)) {
           foreach ($role as $key => $rol) {
                if ($this->user_level->name == $rol) {
                    $counter=$counter+1;
                    
                }
           }
           if ($counter==1) {
                return true;
           }
           return false;
        }else{
            if ($this->user_level->name == $role) {

                return true;
            }
    
            return false;
        }
       
    }


    public function user_job() 
    {
        return $this->belongsTo(UserJobsModel::class, 'id', 'user_id');    
    }

    public function user_second() 
    {
        return $this->belongsTo(SecondUsersModel::class, 'id', 'user_id');    
    }


    public function get_career() 
    {
        return $this->belongsTo(CareerUpdatedModel::class, 'career_id','id');    
    }
    public function user_get_job() 
    {
        return $this->belongsTo(GetJobsModel::class, 'id', 'user_id');    
    }
    
    public function user_get_all_job() 
    {
        return $this->hasMany(GetJobsModel::class, 'user_id', 'id');    
      
    }
public function user_get_counter() 
{
    return $this->belongsTo(UserJobsRateCount::class, 'id', 'user_id');    
}

}

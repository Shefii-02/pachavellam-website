<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;




class User extends Authenticatable
{
   use HasApiTokens, HasFactory, Notifiable, HasRoles;
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];


   public function isAdmin()
    {
        // Logic to check if the user is an admin
        // You need to replace this with your own logic based on your user model's structure
        return $this->type === 'Admin'; // Assuming you have a 'role' column in the users table
    }

    public function isStaff()
    {
        // Logic to check if the user is a staff member
        // You need to replace this with your own logic based on your user model's structure
        return $this->role === 'Admin Staff'; // Assuming you have a 'role' column in the users table
    }

    public function check_filledform($user_id = null){
        $form_filled = auth()->user()->filled_column;
        if($form_filled == 0){
             return ('web.stage1');
        }
        else if($form_filled == 1){
            return ('web.stage2');
        }
        else if($form_filled == 2){
     
            return ('dashboard');

            
        }
       
    }
      public function getProfilePhotoUrlAttribute()
    {
        return $this->image; 
    }

    public function role()
    {
        return $this->hasMany(RoleUser::class, 'user_id');
    }
   
}

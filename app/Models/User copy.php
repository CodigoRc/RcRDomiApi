<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'checked', 'isAdmin', 'image', 'lang', 'rcimg', 'client_id', 'status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Set the user's password.
     *
     * @param  string  $value
     * @return void
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * Get the roles associated with the user.
     */
    public function roles()
    {
        return $this->hasOne('App\Models\Roles', 'user_id');
    }

    /**
     * Get the user roles associated with the user.
     */
    public function user_roles()
    {
        return $this->hasMany('App\Models\UserRol', 'user_id');
    }

    /**
     * Get the client associated with the user.
     */
    public function client()
    {
        return $this->hasOne('App\Models\Client', 'id', 'client_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;

class Client extends Model
{
    
    protected $table = 'client';
    protected $fillable = array(
        'id',
        'user_id',
        'name',
        'join_date',
        'email',
        'personal_private',
        'personal_phone_1',
        'personal_phone_2',
        'personal_address',
        'company',
        'company_private',
        'company_phone_1',
        'company_phone_2',
        'company_address',
        'client_description',
        'status',
        'image',
        'rcimg',        
        'rcimgcopy',    
        'station_type_id'    
    );

  
    public function stations()
    {
        return $this->hasMany('App\Models\Station');
    }

    public function activities()
    {
        return $this->hasMany('App\Models\Activity');
    }

    public function tickets()
    {
        return $this->hasMany('App\Models\Ticket', 'client_id');
    }

    public function openTickets()
    {
        return $this->tickets()->where('status', '!=', 'closed');
    }
}

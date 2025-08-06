<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Station extends Model


{       

    protected $table = 'station';
    protected $fillable = array(
        'id',
        'dig',
        
        'name',
        'station_type_id',
        'is_link',
        'is_link_url',

        'client_id',
        'join_date',
        'slogan',
        'description',
        'url',

        'country_id',
        'city_id',
        'address',

        'status',
        'featured',

        'image',

        'health',

        'email',
        'email2',
        'order',

        
    );

  




    public function stationType()
    {
        return $this->hasOne('App\Models\StationType');
    }

    public function Streaming()
    {
        return $this->hasOne('App\Models\Streaming', 'station_id');
    }


    public function AllOrder()
    {
        return $this->hasOne('App\Models\Pais');
    }

    public function RadioOrder()
    {
        return $this->hasOne('App\Models\Pais');
    }

    public function TvOrder()
    {
        return $this->hasOne('App\Models\Pais');
    }


    public function client()
    {
        return $this->hasOne('App\Models\Client', 'id', 'client_id')->select(['id', 'image', 'name']);
    }
    public function city()
    {
        return $this->hasOne('App\Models\City', 'id', 'city_id');
    }
    public function country()
    {
        return $this->hasOne('App\Models\Country', 'id', 'country_id');
    }

    public function Mailoff()
    {
        return $this->hasMany('App\Models\MailOff')->orderBy('id', 'desc');
    }

    public function orden()
    {
        return $this->hasOne('App\Models\Orden');
    }
    public function ordenados()
    {
        return $this->hasOne('App\Models\Orden');
    }
    public function likes()
    {
        return $this->hasMany('App\Models\Likes');
    }
    public function tipo()
    {
        return $this->hasOne('App\Models\Tipo');
    }

    public function tags()
    {
        $this->belongsToMany('App\Models\Tag');
    }

    public function user_estaciones()
    {
        $this->belongsTo('App\Models\UserEstaciones');
    }

    public function stats()
    {
        return $this->hasOne('App\Models\ServiceStats', 'service_id', 'id')->where('type', 'view')->select(['count']);
    }

    //function Test_result
    // public function test_result()
    // {
    //     return $this->hasMany(TesterStats::class, 'station_id')->orderBy('created_at', 'desc');
    // }

    // public function TesterDiagnosticStatus()
    // {
    //     return $this->hasOne(TesterDiagnosticItem::class, 'station_id');
    // }



    
    public function activities()
    {
        return $this->hasMany('App\Models\Activity');
    }

    public function tickets()
    {
        return $this->hasMany('App\Models\Ticket', 'station_id');
    }

    public function openTickets()
    {
        return $this->tickets()->where('status', '!=', 'closed');
    }
}




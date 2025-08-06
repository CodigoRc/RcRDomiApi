<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    public $timestamps = false;
    protected $table = 'city';
    protected $fillable = array(
        'id',
        'country_id',
        'name',
        'lat',
        'long',      
    );


    public function country()
    {
        return $this->belongsTo('App\Models\Country');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    public $timestamps = false;
    protected $table = 'country';
    protected $fillable = array(
        'id',
        'name',
        'code',
        'lat',
        'long',      
    );


    public function cities()
    {
        return $this->hasMany('App\Models\City');
    }
}

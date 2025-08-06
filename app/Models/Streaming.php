<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Streaming extends Model
{
    protected $table = 'streaming';
    protected $fillable = array(
      'station_id',
      'server',
      'port',
      'portnossltv',
      'mount',   
      'server2',
      'custom_video',
      'tvfullurl',
      'tvfullandroid',
      'tvfullios',
    );
}

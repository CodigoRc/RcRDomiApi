<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceStats extends Model
{
    protected $table = 'service_stats';
    protected $fillable = array(
      'type',
      'service_id',
      'count'     
    );

}

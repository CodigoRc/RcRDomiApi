<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostingStation extends Model
{
    protected $table = 'station_web_admin';

    protected $fillable = [
        'station_id',
        'cpanel',
        'user_name',
        'pass',
        'ftp_user',
        'ftp_pass',
        'url',
    ];

    public $timestamps = true;

    public function station()
    {
        return $this->belongsTo(Station::class, 'station_id');
    }

    // Note: The server relationship is commented out as there's no 'server_id' in the table.
    // If you have a HostingServer model and a server_id column in another table or need to relate it differently, let me know.
    /*
    public function server()
    {
        return $this->belongsTo(HostingServer::class, 'server_id');
    }
    */
}
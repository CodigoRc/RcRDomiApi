<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RadioStreaming extends Model
{
    protected $table = 'radio_streaming';

    protected $fillable = [
        'station_id',
        'server_id',
        'ip',
        'host',
        'port',
        'username',
        'password',
        'stream_password',
        'stream_ssl_url',
        'autodj_enabled',
        'bitrate_limit',
        'listener_limit',
        'bandwidth_limit',
        'script_config',
    ];
}
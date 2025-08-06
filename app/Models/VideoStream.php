<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VideoStream extends Model
{
    use HasFactory;

    protected $table = 'video_streaming';

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
        'viewer_limit',
        'bandwidth_limit',
        'script_config',
        'stream_key',
        'stream_username',
    ];

    protected $casts = [
        'station_id' => 'integer',
        'server_id' => 'integer',
        'port' => 'integer',
        'autodj_enabled' => 'boolean',
        'bitrate_limit' => 'integer',
        'viewer_limit' => 'integer',
        'bandwidth_limit' => 'integer',
        'stream_key' => 'string',
        'stream_username' => 'string',
    ];

    public function station()
    {
        return $this->belongsTo(Station::class);
    }
} 
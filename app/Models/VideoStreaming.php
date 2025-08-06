<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VideoStreaming extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'video_streaming';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'station_id' => 'integer',
        'server_id' => 'integer',
        'port' => 'integer',
        'autodj_enabled' => 'boolean',
        'bitrate_limit' => 'integer',
        'viewer_limit' => 'integer',
        'bandwidth_limit' => 'integer',
    ];

    /**
     * Get the station that owns the video streaming.
     */
    public function station()
    {
        return $this->belongsTo(Station::class);
    }

    /**
     * Get the video server that owns the video streaming.
     */
    public function videoServer()
    {
        return $this->belongsTo(VideoServer::class, 'server_id');
    }
} 
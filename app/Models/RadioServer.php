<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RadioServer extends Model
{

    protected $table = 'radio_server';

    protected $fillable = [
        'name',
        'centova_url',
        'audio_stream_url',
        'dns',
        'extensions_url',
    ];
}
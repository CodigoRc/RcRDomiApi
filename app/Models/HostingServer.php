<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostingServer extends Model
{

    protected $table = 'hosting_server';

    protected $fillable = [
        'name',
        'cpanel_url',
        'admin_url',
    ];
}
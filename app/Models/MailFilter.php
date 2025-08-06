<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailFilter extends Model
{
    use HasFactory;

    protected $table = 'mail_filters';

    protected $fillable = [
        'id',
        'title',
        'slug',
        'icon',
    ];
}
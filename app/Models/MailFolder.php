<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailFolder extends Model
{
    use HasFactory;

    protected $table = 'mail_folders';

    protected $fillable = [
        'id',
        'title',
        'slug',
        'icon',
    ];
}
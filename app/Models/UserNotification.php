<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;

class UserNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'notification_id',
        'is_read',
        'is_deleted',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function notification()
    {
        return $this->belongsTo(DatabaseNotification::class, 'notification_id', 'id');
    }
}
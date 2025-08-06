<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mail extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'station_id', 'user_id', 'contact_method', 'type', 'from', 'to', 'cc', 'ccCount', 'bcc', 'bccCount', 'date', 'subject', 'content',  'in_progress', 'open', 'closed', 'unread', 'folder', 'labels', 'admin_user_id','created_by'
    ];

    protected $casts = [
        'from' => 'array',
        'cc' => 'array',
        'bcc' => 'array',
        'labels' => 'array',
        'in_progress' => 'boolean',
        'open' => 'boolean',
        'closed' => 'boolean',
        'unread' => 'boolean',
        'date' => 'datetime',
    ];

    public function station()
    {
        return $this->belongsTo(Station::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function messages()
    {
        return $this->hasMany(MailMessage::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'mail_id', 'sender_id', 'recipient_id', 'sender_type', 'message', 'created_at', 'updated_at'
    ];

    public function mail()
    {
        return $this->belongsTo(Mail::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id')->withDefault();
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{

    protected $table = 'activities';

    protected $fillable = [
        'user_id',
        'client_id',
        'station_id',
        'model_type',
        'model_id',
        'status',
        'action',
        'description',
        'important_change',
        'ticket_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    // Nombre de la tabla asociada con el modelo
    protected $table = 'tickets';

    // Definir los campos que se pueden asignar masivamente
    protected $fillable = [
        'user_id',
        'client_id',
        'station_id',
        'contact_method',
        'title',
        'priority',
        'notes',
        'status',
        'phone',
        'internal_use',
        'department',
        'email',
    ];
}
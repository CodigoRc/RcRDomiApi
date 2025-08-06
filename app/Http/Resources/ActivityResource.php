<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Station;
use App\Models\Client;

class ActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // Inicializar las variables
        $image = null;
        $name = null;
        $ticket = null;

        // Obtener la imagen y el nombre en función del tipo de modelo
        if ($this->model_type === 'Station') {
            $station = Station::find($this->model_id);
            if ($station) {
                $image = $station->image;
                $name = $station->name;
            }
        } elseif ($this->model_type === 'Client') {
            $client = Client::find($this->model_id);
            if ($client) {
                $image = $client->image;
                $name = $client->name;
            }
        }

        // Obtener el ticket asociado
        if ($this->ticket_id) {
            $ticket = $this->ticket;
        }

        return [
            'id' => (string) $this->id,
            'icon' => $this->icon ?? "mat_solid:access_time_filled",
            'image' => $image, // Usar la imagen obtenida
            'description' => $this->description ?? null,
            'date' => $this->created_at->toIso8601String(), 
            'extraContent' => $this->important_change ?? null,
            'linkedContent' => $name, // Usar el nombre obtenido
            'link' => $this->link ?? null,
            'useRouter' => $this->use_router ?? false,
            'model' => [
                'user_id' => $this->user_id,
                'user_name' => $this->user->name ?? null,
                'client_id' => $this->client_id,
                'station_id' => $this->station_id,
                'station_name' => $name, // Usar el nombre obtenido
                'model_type' => $this->model_type,
                'model_id' => $this->model_id,
                'ticket_id' => $this->ticket_id,
                'status' => $this->status,
                'action' => $this->action,
                'description' => $this->description,
                'important_change' => $this->important_change,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            'ticket' => $ticket ? [
                'id' => $ticket->id,
                'user_id' => $ticket->user_id,
                'client_id' => $ticket->client_id,
                'station_id' => $ticket->station_id,
                'contact_method' => $ticket->contact_method,
                'title' => $ticket->title,
                'priority' => $ticket->priority,
                'notes' => $ticket->notes,
                'status' => $ticket->status,
                'phone' => $ticket->phone,
                'internal_use' => $ticket->internal_use,
                'department' => $ticket->department,
                'email' => $ticket->email,
                'created_at' => $ticket->created_at->toIso8601String(),
                'updated_at' => $ticket->updated_at->toIso8601String(),
            ] : null,
        ];
    }
}
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RdomiStationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // Obtener el streaming correcto según el tipo de estación
        $streaming = null;
        if (in_array($this->station_type_id, [0, 3])) {
            $stream = \App\Models\RadioStreaming::where('station_id', $this->id)->first();
        } elseif (in_array($this->station_type_id, [1, 4])) {
            $stream = \App\Models\VideoStream::where('station_id', $this->id)->first();
        } else {
            $stream = null;
        }
        $streaming = $stream ? [
            'host' => $stream->host ?? null,
            'port' => $stream->port ?? null,
            'stream_ssl_url' => $stream->stream_ssl_url ?? null,
            'script_config' => $stream->script_config ?? null,
        ] : null;

        // Obtener la URL del hosting de la estación
        $hostingUrl = null;
        $hostingStation = \App\Models\HostingStation::where('station_id', $this->id)->first();
        if ($hostingStation && $hostingStation->url) {
            $hostingUrl = $hostingStation->url;
        }
        return [
            'id'                    => $this->id,
            'name'                  => $this->name,
            'station_type_id'       => $this->station_type_id,
            'is_link'               => $this->is_link,
            'is_link_url'           => $this->is_link_url,
            'client_id'             => $this->client_id,
            'join_date'             => $this->join_date,
            'slogan'                => $this->slogan,
            'description'           => $this->description,
            'url'                   => $hostingUrl ?? $this->url,
            'status'                => $this->status,
            'featured'              => $this->featured,
            'image'                 => $this->image,
            'health'                => $this->health,
            'order'                 => $this->order,
            'str'                   => $streaming,
            'counter'               => $this->stats['count'] ?? 0,
            'image_url'             => "https://rdomint.com/images/station/".$this->image,
            'open_tickets_count'    => method_exists($this, 'openTickets') ? $this->openTickets()->count() : 0,
            // Cliente
            'client_name'           => $this->client->name ?? null,
            'client_image'          => $this->client->image ?? null,
            'image_client'          => isset($this->client->image) ? "https://rdomint.com/images/client/".$this->client->image : null,
            // País y ciudad
            'country_name'          => $this->country->name ?? null,
            'city_name'             => $this->city->name ?? null,
        ];
    }
} 
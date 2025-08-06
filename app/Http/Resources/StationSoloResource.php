<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StationSoloResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                    => $this->id,
            'dig'                   => $this->dig,
            'name'                  => $this->name,
            'station_type_id'       => $this->station_type_id,
            'is_link'               => $this->is_link,
            'is_link_url'           => $this->is_link_url,
            'client_id'             => $this->client_id,
            'join_date'             => $this->join_date,
            'slogan'                => $this->slogan,
            'description'           => $this->description,
            'url'                   => $this->url,
            'country_id'            => $this->country_id,
            'city_id'               => $this->city_id,
            'address'               => $this->address,
            'status'                => $this->status,
            'featured'              => $this->featured,
            'image'                 => $this->image,
            'health'                => $this->health,
            'order'                 => $this->order,
            'city'                  => $this->city,
            'country'               => $this->country,
            'client'                => $this->client,
            'str'                   => $this->Streaming,
            'counter'               => $this->stats['count'] ?? 0, // Usar 0 como valor predeterminado
            'image_url'             => "https://rdomint.com/images/station/".$this->image,
        ];
    }
}
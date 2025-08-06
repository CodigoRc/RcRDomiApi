<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RcControlStationDefaultResource extends JsonResource
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
            'phone'                 => $this->phone,
            'email'                 => $this->email,
            'email2'                => $this->email2,
            'created_at'            => $this->created_at,
            'updated_at'            => $this->updated_at,
            'image_url'             => "https://domintapi.com/images/station/".$this->image,
            'open_tickets_count'    => $this->openTickets()->count(),
        ];
    }
}
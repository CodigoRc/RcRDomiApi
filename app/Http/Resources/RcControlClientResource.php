<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\RcControlStationDefaultResource;

class RcControlClientResource extends JsonResource
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
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'join_date' => $this->join_date,
            'email' => $this->email,
            'personal_private' => $this->personal_private,
            'personal_phone_1' => $this->personal_phone_1,
            'personal_phone_2' => $this->personal_phone_2,
            'personal_address' => $this->personal_address,
            'company' => $this->company,
            'company_private' => $this->company_private,
            'company_phone_1' => $this->company_phone_1,
            'company_phone_2' => $this->company_phone_2,
            'company_address' => $this->company_address,
            'client_description' => $this->client_description,
            'status' => $this->status,
            'image' => $this->image,
            'rcimg' => $this->rcimg,
            'rcimgcopy' => $this->rcimgcopy,
            'station_type_id' => $this->station_type_id,
            'stations' => RcControlStationDefaultResource::collection($this->stations),
            'open_tickets_count' => $this->openTickets()->count(),
        ];
    }
}
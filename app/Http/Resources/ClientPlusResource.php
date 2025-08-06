<?php

namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\StationResource;
use App\Http\Resources\StationSoloResource;

class ClientPlusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        

        return[
            'id'    => $this->id,
            'name'  => $this->name,
            'image' => "https://domintapi.com/images/client/".$this->image      
        ];
    }
}

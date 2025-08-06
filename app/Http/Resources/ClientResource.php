<?php

namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\StationResource;
use App\Http\Resources\StationSoloResource;

class ClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $parent = parent::toArray($request);
        $stations = $this->stations;
        if($stations){
            $stations = StationResource::collection($this->stations);
            $stations_solo = StationSoloResource::collection($this->stations);
        }else{
            $stations = [];
            $stations_solo = [];
        }

        return[
            'client' => $parent,
            'stations' =>  $stations,
            'stations_solo' => $stations_solo
        ];
    }
}

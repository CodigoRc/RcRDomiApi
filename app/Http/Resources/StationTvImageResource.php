<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StationTvImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return  [

            
            'id'                => $this->id,
           
            'image'             => $this->image,
            'backdrop_path'             => $this->image,
            'still_path'             => $this->image,
            'poster_path'             => $this->image,

          
          ];

        //   $antes = [ 'movie' => $miarray ];

        //   return $miarray;
    
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Support\Carbon;

use Illuminate\Http\Resources\Json\JsonResource;

class StationSoloResource2minp extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */

   // 'https://ss5.domint.net:3018/cvh_str/colorvisionhd/playlist.m3u8',

    public $htttps;
    public $m3u8;
    public $mp4;

    public $server;
    public $port;
    public $mount;

    public $fullvideo;
    public $fullaudio;

    public $fullvideourltv;


    public function toArray($request)
    {
        // return parent::toArray($request);

        $this->htttps = 'https://';
        $this->m3u8 = '/playlist.m3u8';
        $this->mp4 = '/;stream.mp4';





        if($this->Streaming !== null){

            if($this->Streaming->server != null){
                $this->server = $this->Streaming->server;
            }else{
                $this->server = null;
            }

            if($this->Streaming->port != null){
                $this->port = $this->Streaming->port;
            }else{
                $this->port = null;
            }

            if($this->Streaming->mount != null){
                $this->mount = $this->Streaming->mount;
            }else{
                $this->mount = null;
            }

            if($this->Streaming->tvfullurl != null){
                $this->fullvideourltv = $this->Streaming->tvfullurl;
            }else{
                $this->fullvideourltv = null;
            }


            if($this->station_type_id == 1 && $this->port != null  || $this->station_type_id == 4   ){
                $this->fullvideo = $this->htttps.$this->server.':'.$this->port.'/'.$this->mount.$this->m3u8;
            }else{
                $this->fullvideo = null;
            }

            // if($this->station_type_id == 1 && $this->port != null  || $this->station_type_id == 4 && $this->port != null   ){

            //     $this->fullvideo = $this->htttps.$this->server.':'.$this->port.'/'.$this->mount.$this->m3u8;

            // }
            
            // if(
            //     $this->station_type_id == 1 && $this->port === null  || $this->station_type_id == 4 && $this->port === null
            // ) {

            //     // $this->fullvideo = $this->tvfullurl;
            //     $this->fullvideo = $this->fullvideourltv;
            // }
                
            // }else{
            //     $this->fullvideo = null;

            // }





            if($this->station_type_id == 1 && $this->port != null  || $this->station_type_id == 4   ){
                $this->fullvideo = $this->htttps.$this->server.':'.$this->port.'/'.$this->mount.$this->m3u8;
            }else{
                $this->fullvideo = null;
            }

            if($this->station_type_id == 0 && $this->port == null || $this->station_type_id == 3 && $this->port == null   ){
                
                if($this->Streaming->server2){
                  $this->fullaudio =  $this->Streaming->server2.$this->mp4;

                }else{
                $this->fullaudio = null;

                }
            }else{
                $this->fullaudio = null;
            }

        }





        return [
            'id'                => $this->id,
            'station_type_id'   => $this->station_type_id,
            'dig'               => $this->dig,
            'name'              => $this->name,
            'slogan'            => $this->slogan,
            'is_link'           => $this->is_link,
            'is_link_url'       => $this->is_link_url,
            'client_id'         => $this->client_id,
            'join_date'         => $this->join_date,
            'description'       => $this->description,
            'url'               => $this->url,
            'country_id'        => $this->country_id,
            'country_name'      => $this->country['name'],
            'city_id'           => $this->city_id,
            'city_name'         => $this->city['name'],
            'status'            => $this->status,
            'featured'          => $this->featured,
            'image'             => $this->image,
            'order'             => $this->order,
            'client_name'       => $this->client['name'],
            'client_img'        => $this->client['image'],
            'str_server'        => $this->server,
            'str_port'          => $this->port,
            'str_mount'         => $this->mount,
            'full_video'        => $this->fullvideo,
            'full_audio'        => $this->fullaudio,
            'tvfullurl'         => $this->fullvideourltv,
            'counter'           => $this->stats['count'],
            'image_url'             => "https://rdomint.com/images/station/".$this->image,




          
          ];
    }
}


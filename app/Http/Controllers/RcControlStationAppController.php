<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Station;
use App\Http\Resources\StationAppResource;
use App\Http\Resources\StationTvResource;
use App\Http\Resources\StationTvImageResource;
use App\Http\Resources\StationSoloResource2minp;


class RcControlStationAppController extends Controller
{
    public function allx()
    {
      // $item = Station::all();   
      // $item = Station::orderBy('order', 'asc')->get();
      $item = Station::orderBy('order', 'asc')
        ->where('status', 1)
        ->get();
  
      //$item =  StationSoloResource::collection($item);
      return response()->json($item);
    }

    
    public function allApp()
    {
      // $item = Station::all();   
      // $item = Station::orderBy('order', 'asc')->get();


      $item = Station::orderBy('order', 'asc')
        ->where('status', 1)
        ->get();
  
      $item =  StationAppResource::collection($item);
      return response()->json($item);
    }

    public function allDomintControl()
    {
      // $item = Station::all();   
      // $item = Station::orderBy('order', 'asc')->get();


      $item = Station::orderBy('order', 'asc')
        ->get();
  
      $item =  StationAppResource::collection($item);
      return response()->json(["data" => $item, "code" => 200]);

    }

    
    public function oneDomintControl (Request $request)
    {
      $id = $request->input('id'); // client id

      $stations = Station::where('id', '=', $id)
      ->get();
    
      // $item =  StationAppResource::collection($stations);

      return response()->json(["data" => $stations, "code" => 200]);

  
    }



    

    
    public function stationPaginate()
    {
      // $item = Station::all();   
      $item = Station::orderBy('order', 'asc')->simplePaginate(15);
      $item =  StationAppResource::collection($item);
  
      return response()->json($item);
    }

    
    public function stationPaginateAll()
    {
      // $item = Station::all();   
      $item = Station::where('status', '=', 1)
                            ->orderBy('order', 'asc')
                            ->where('status', 1)
                            
      
                            ->simplePaginate(15);
      $item =  StationAppResource::collection($item);
  
      return response()->json($item);
    }

    public function stationAppAllRc ()
    {
      // $item = Station::all();   
      $item = Station::where('status', '=', 1)
                            ->orderBy('order', 'asc')
                            ->where('status', 1)
                            
                            ->simplePaginate(2000);
                            // ->limit(15);
      $item =  StationAppResource::collection($item);
  
      return response()->json(["result" => $item,  "code" => 200]);
      // return response()->json($item);
    }


    public function stationAppAllRadioRcSimpleDomin ()
    {
      // $item = Station::all();   
      $item = Station::where('status', '=', 1)
                            ->orderBy('order', 'asc')
                            ->where('status', 1)
                            ->where('station_type_id', 0)
                            
                            ->simplePaginate(1000);
                            // ->limit(15);
      $item =  StationSoloResource2minp::collection($item);
  
      return response()->json( $item);
      // return response()->json($item);
    }

    public function stationAppAllTvRcSimpleDomin ()
    {
      // $item = Station::all();   
      $item = Station::where('status', '=', 1)
                            ->orderBy('order', 'asc')
                            ->where('status', 1)
                            ->where('station_type_id', 1)
                            
                            ->simplePaginate(1000);
                            // ->limit(15);
      $item =  StationSoloResource2minp::collection($item);
  
      return response()->json( $item);
      // return response()->json($item);
    }

    public function stationAppAllRcSimpleDomin ()
    {
      // $item = Station::all();   
      $item = Station::where('status', '=', 1)
                            ->orderBy('order', 'asc')
                            ->where('status', 1)
                            
                            ->simplePaginate(10);
                            // ->limit(15);
      $item =  StationSoloResource2minp::collection($item);
  
      return response()->json( $item);
      // return response()->json($item);
    }

    public function AllStationsMixSimple ()
    {
      // $item = Station::all();   
      $item = Station::where('status', '=', 1)
                            ->orderBy('order', 'asc')
                            ->where('status', 1)
                            
                            ->simplePaginate(1000);
                            // ->limit(15);
      $item =  StationSoloResource2minp::collection($item);
  
      return response()->json( $item);
      // return response()->json($item);
    }

    public function stationAppAllRcSimpleDominPlus ()
    {
      // $item = Station::all();   
      $item = Station::where('status', '=', 1)
                            ->orderBy('order', 'asc')
                            ->where('status', 1)
                            
                            ->simplePaginate(10);
                            // ->limit(15);
      $item =  StationSoloResource2minp::collection($item);
  
      return response()->json( $item);
      // return response()->json($item);
    }


    
    
    public function stationAppAllRcSimple ()
    {
      // $item = Station::all();   
      $item = Station::where('status', '=', 1)
                            ->orderBy('order', 'asc')
                            ->where('status', 1)
                            
                            ->simplePaginate(2000);
                            // ->limit(15);
      $item =  StationAppResource::collection($item);
  
      return response()->json( $item);
      // return response()->json($item);
    }


    public function stationAppAllRcSimpleGet (Request $request)
    
    {
      // $item = Station::all();   
      $id = $request->input('id'); // client id

      $item = Station::where('status', '=', 1)
                            ->where('id', $id)
                            ->where('status', 1)
                            
                            ->first();
                            // ->limit(15);
      $item = new StationAppResource($item);
  
      return response()->json( $item);
      // return response()->json($item);
    }


    public function stationAppAllRcPlusGet (Request $request)
    
    {
      // $item = Station::all();   
      $id = $request->input('id'); // client id

      $item = Station::where('status', '=', 1)
                            ->where('id', $id)
                            ->where('status', 1)
                            
                            ->first();
                            // ->limit(15);
      $item = new StationSoloResource2minp($item);
  
      return response()->json( $item);
      // return response()->json($item);
    }



    public function stationAppAllRcLimited ()
    {
      // $item = Station::all();   
      $item = Station::where('status', '=', 1)
                            ->orderBy('order', 'asc')
                            ->where('status', 1)
                            
                            ->simplePaginate(20);
                            // ->limit(15);
      $item =  StationAppResource::collection($item);
  
      return response()->json(["result" => $item,  "code" => 200]);
      // return response()->json($item);
    }


    public function stationAppOtherStations (Request $request)
    {
      $id = $request->input('id'); // client id

      $stations = Station::where('client_id', '=', $id)
      ->orderBy('id', 'asc')
      ->where('status', 1)
      ->get();
    
      $item =  StationAppResource::collection($stations);


      return response()->json(["result" => $item,  "code" => 200]);
      // return response()->json($item);
    }



    
    public function stationAppOtherStationsSolo (Request $request)
    {
      $id = $request->input('id'); // client id

      $stations = Station::where('client_id', '=', $id)
      ->orderBy('id', 'asc')
      ->where('status', 1)
      ->get();
    
      $item =  StationAppResource::collection($stations);


      return response()->json($item);
      // return response()->json($item);
    }

    
    public function stationAppOtherStationsPlus (Request $request)
    {
      $id = $request->input('id'); // client id

      $stations = Station::where('client_id', '=', $id)
      ->orderBy('id', 'asc')
      ->where('status', 1)
      ->get();
    
      $item =  StationSoloResource2minp::collection($stations);


      return response()->json($item);
      // return response()->json($item);
    }

    

    public function stationPaginateRadio()
    {
      // $item = Station::all();   
      $item = Station::where('station_type_id', '=', 0)
                            ->orderBy('order', 'asc')
                            ->where('status', 1)
                            
      
                            ->simplePaginate(15);
      $item =  StationAppResource::collection($item);
  
      return response()->json($item);
    }

    public function stationPaginateRadioRc()
    {
      // $item = Station::all();   
      $item = Station::where('station_type_id', '=', 0)
                            ->orderBy('order', 'asc')
                            ->where('status', 1)
                            
      
                            ->simplePaginate(500);
      $item =  StationAppResource::collection($item);
  
      return response()->json(["result" => $item,  "code" => 200]);
      // return response()->json($item);
    }


    public function stationPaginateWebRc()
    {
      // $item = Station::all();   
      $item = Station::where('station_type_id', '=', 2)
                            ->orderBy('order', 'asc')
                            ->where('status', 1)
                            
      
                            ->simplePaginate(500);
      $item =  StationAppResource::collection($item);
  
      return response()->json(["result" => $item,  "code" => 200]);
      // return response()->json($item);
    }

    

    
    public function stationPaginateRadioP()
    {
      // $item = Station::all();   
      $item = Station::where('station_type_id', '=', 3)
                            ->orderBy('order', 'asc')
                            
      
                            ->simplePaginate(15);
      $item =  StationAppResource::collection($item);
  
      return response()->json($item);
    }


    
    public function stationPaginateTv()
    {
      // $item = Station::all();   
      $item = Station::where('station_type_id', '=', 1)
                            ->orderBy('order', 'asc')
                            ->where('status', 1)
      
                            ->simplePaginate(15);
      $item =  StationAppResource::collection($item);
  
      return response()->json($item);
    }

    public function stationPaginateTvRc()
    {
      // $item = Station::all();   
      $item = Station::where('station_type_id', '=', 1)
                            ->orderBy('order', 'asc')
                            ->where('status', 1)
      
                            ->simplePaginate(500);
      $item =  StationAppResource::collection($item);
  
      return response()->json(["result" => $item,  "code" => 200]);
      // return response()->json($item);
    }

    
    public function stationPaginateTvP()
    {
      // $item = Station::all();   
      $item = Station::where('station_type_id', '=', 4)
                            ->orderBy('order', 'asc')
                            ->where('status', 1)
                            
      
                            ->simplePaginate(15);
      $item =  StationAppResource::collection($item);
  
      return response()->json($item);
    }

    
    //tv app
    public function tvAppStationPaginateTv()
    {
      $item = Station::where('station_type_id', '=', 1)
                            ->orderBy('order', 'asc')
                            ->where('status', 1)
                            ->get();      
      $item =  StationTvResource::collection($item);  
      return response()->json( $item);
    }

    //tv app
    public function tvAppStationPaginateRadio()
    {
      $item = Station::where('station_type_id', '=', 0)
      ->where('status', 1)
                            ->orderBy('order', 'asc')
                            ->get();      
      $item =  StationTvResource::collection($item);  
      return response()->json( $item);
    }



    public function tvAppImageStationTv($id)
    {    
      $item = Station::find($id);
      $item2 = new StationTvImageResource($item);  
      return response()->json( $item2);
    }




    // [
    //   {
    //     'movie': {
    //       title: 'mio'
    //     }
    //   }
    // ]




}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Station;
use App\Http\Resources\StationResource;
use App\Http\Resources\StationSoloResource;
use App\Http\Resources\StationSoloResource2minp;
use App\Http\Resources\StationStrResource;

class StationController extends Controller
{

  public function search(Request $request)
  {
    $search = $request->input('name');
    //$search = "guar";
    $items = Station::search($search, null, true)
      //  ->where('tipo', 1)
      ->orderBy('id', 'asc')
      ->get();

    return response()->json(["data" => $items,   "code" => 200]);
  }




  public function all()
  {
    // $item = Station::all();   
    $item = Station::orderBy('order', 'asc')->get();

    $item =  StationSoloResource::collection($item);
    return response()->json(["data" => $item,  "code" => 200]);
  }

  
  public function allonline()
  {
    // $item = Station::all();   
    // $item = Station::orderBy('order', 'asc')->get();
    $item = Station::orderBy('order', 'asc')
    ->where('status', 1)
    ->get();
    $item =  StationSoloResource::collection($item);
    return response()->json(["data" => $item,  "code" => 200]);
  }











  public function allonlineclean()
  {
    // $item = Station::all();   
    // $item = Station::orderBy('order', 'asc')->get();
    $item = Station::orderBy('order', 'asc')
    ->where('status', 1)
    ->get();
    $item =  StationSoloResource::collection($item);
    return response()->json($item);
  }


  


  public function allonlinecleanTV10()
  {
    // $item = Station::all();   
    // $item = Station::orderBy('order', 'asc')->get();
    $item = Station::orderBy('order', 'asc')
    ->where('status', 1)
    ->where('station_type_id', 1)
    // ->paginate(10)
    ->limit(10)
    ->get();
    $item =  StationSoloResource::collection($item);
    return response()->json($item);
  }

  
  public function allonlinecleanRadio10()
  {
    // $item = Station::all();   
    // $item = Station::orderBy('order', 'asc')->get();

    // $count = stats->input('stats');
    $item = Station::orderBy('order', 'asc')
    ->where('status', 1)
    ->where('station_type_id', 0)
    ->limit(10)
    ->get();
    $item =  StationSoloResource::collection($item);
    return response()->json($item);
  }


  
  public function allonlinecleanRadio102minp()
  {
    // $item = Station::all();   
    // $item = Station::orderBy('order', 'asc')->get();

    // $count = stats->input('stats');
    $item = Station::orderBy('order', 'asc')
    ->where('status', 1)
    ->where('station_type_id', 0)
    ->limit(10)
    ->get();
    $item =  StationSoloResource2minp::collection($item);
    return response()->json($item);
  }










  
  public function featured()
  {
    // $item = Station::all();   
    $item = Station::orderBy('order', 'asc')->take(8)->get();

    $item =  StationSoloResource::collection($item);
    return response()->json(["data" => $item,  "code" => 200]);
  }

  public function getStr($id)
  {

    // $id = $request->input('id');



    // $item = Station::all();   
    $item = Station::find($id);

        $item =  new StationStrResource($item);
    return response()->json(["data" => $item,  "code" => 200]);
  }



  public function allRadio()
  {
    $item = Station::where('station_type_id', 0)->get();

    return response()->json(["data" => $item,  "code" => 200]);
  }

  public function allTv()
  {
    $item = Station::where('station_type_id', 1)->get();
    return response()->json(["data" => $item,  "code" => 200]);
  }


  public function allStr()
  {
    $item = Station::orderBy('order', 'asc')->get();
    $items =  StationResource::collection($item);
    $itemsolo =  StationSoloResource::collection($item);

    return response()->json(["data" => $items, 'itemsolo' => $itemsolo,  "code" => 200]);
  }

  
  public function allStrPublic()
  {
    $item = Station::orderBy('order', 'asc')
    ->where('status', 1)
    ->get();
    $items =  StationResource::collection($item);
    $itemsolo =  StationSoloResource::collection($item);

    return response()->json(["data" => $items, 'itemsolo' => $itemsolo,  "code" => 200]);
  }

  public function allStrTester()
  {
    $item = Station::orderBy('order', 'asc')
      //  ->where('station_type_id', 0)
      // ->where('station_type_id', 1)
      ->whereIn('station_type_id', [1,0])
      ->where('status', 1)
      ->get();
    $items =  StationResource::collection($item);
    $itemsolo =  StationSoloResource::collection($item);

    return response()->json(["data" => $itemsolo,  "code" => 200]);
  }

  public function buscar(Request $request)
  {
    $busqueda = $request->input('nombre');
    $items = Station::search($busqueda, null, true)
      //  ->where('tipo', 1)
      ->orderBy('id', 'asc')
      ->get();
    return response()->json(["data" => $items, "buscando" => $busqueda,  "code" => 200]);
  }

  public function get(Request $request)
  {
    $id = $request->input('id');

    $item = Station::find($id);
    $item2 = new StationResource($item);
    $item3 = new StationSoloResource($item);
    return response()->json(["data" => $item2, "itemsolo" => $item3,  "code" => 200]);
  }










  public function getsolo(Request $request)
  {
    $id = $request->input('id');

    $item = Station::find($id);
    $item2 = new StationResource($item);
    $item3 = new StationSoloResource($item);
    return response()->json( $item3);
  }












  public function add(Request $request)
  {
    $item = Station::create($request->all());
    return response()->json(["data" => $item,  "code" => 200]);
  }

  public function upd(Request $request)
  {
    $id = $request->input('id');
    $item = Station::find($id);
    $item->fill($request->all())->save();
    return response()->json(["data" => $item,  "code" => 200]);
  }

  public function del(Request $request)
  {
    $id = $request->input('id');
    $image = $request->input('image');
    $item = Station::find($id);
    $item->delete();
    if ($item) {
      $this->delImg($image);
    }
    return response()->json(["data" => $item, "code" => 200]);
  }





  public function sendOrder(Request $request)
  {
    // $ids = $request->input('id');
    $datas = $request->input('name');
    // $orders = $request->input('order');

    foreach ($datas as $item) {


      $id = $item['id'];
      $order = $item['order'];

      $values =  Station::where('id', $id)->update([
        'order' => $order
      ]);
    }


    $items = Station::all();


    return response()->json(["dataa" => $items, "code" => 200]);
  }


  
  public function addEmail(Request $request)
  {
    // $ids = $request->input('id');
    $datas = $request->input('name');
    // $orders = $request->input('order');



      $id = $request['id'];
      $email = $request->input('email');
      $email2 = $request->input('email2');

      Station::where('id', $id)->update([
        'email' => $email,
        'email2' => $email2,
      ]);


    $items = Station::all();


    return response()->json(["dataa" => $items, "code" => 200]);
  }

















  public function addImg(Request $request)
  {
    $id = $request->input('id');
    $image = $request->input('image');
    $rcimg = $request->input('rcimg');
    $copy = $request->input('rcimgcopy');
    $folder = 'station';
    $copyfolder = 'station-copy';
    $station = Station::find($id);

    if ($image) {
      $this->delImg($image);
    }

    $img_name = $this->decode($rcimg, $folder, $id);

    if ($copy) {
      $this->decode($copy, $copyfolder, $id);
    }

    $station->fill([
      'image' => $img_name
    ])->save();


    return response()->json(["data" => $station, "code" => 200]);
  }



  public function decode($data, $folder, $id)
  {
    if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
      $data = substr($data, strpos($data, ',') + 1);
      $type = strtolower($type[1]); // jpg, png, gif

      if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
        throw new \Exception('invalid image type');
      }

      $data = base64_decode($data);

      if ($data === false) {
        throw new \Exception('base64_decode failed');
      }
    } else {
      throw new \Exception('did not match data URI with image data');
    }

    $date = date("m-d-Y");
    $time = time();
    $datetime = $date . $time;
    $this->img_name = "domint{$id}img{$datetime}.{$type}";
    //$this->img_name = "img{$datetime}.{$type}";

    file_put_contents("images/{$folder}/{$this->img_name}", $data);

    return $this->img_name;
  }

  public function delImg($file)
  {
    $folder = 'station';
    $toDel = "images/{$folder}/{$file}";
    $delCopy = "images/{$folder}-copy/{$file}";
    if (file_exists($toDel)) {
      unlink($toDel);
    } else {
      return response()->json(["data" => 'error del img',  "code" => 200]);
    }

    if (file_exists($delCopy)) {
      unlink($delCopy);
    }
  }
}

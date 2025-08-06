<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Station;
use App\Models\Country;
use App\Http\Resources\ClientResource;
use App\Http\Resources\RcControlClientResource;
use App\Http\Resources\CountryResource;
use App\Http\Resources\ClientPlusResource;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Services\ActivityService; // Importar el servicio de actividad
use App\Helpers\ActivityHelper; // Importar el helper de actividad
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class RcControlClientController extends Controller
{
    protected $activityService;
    private $img_name;
    
    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    public function all()
    {
        // Recuperar los datos de la base de datos
        $items = Client::orderBy('id', 'asc')->get();
        $items = RcControlClientResource::collection($items);
        return response()->json(["data" => $items, "code" => 200]);
    }

    public function RcAllControl()
    {
        // Recuperar los datos de la base de datos
        $items = Client::orderBy('id', 'asc')->get();
        $items = RcControlClientResource::collection($items);
        return response()->json(["data" => $items, "code" => 200]);
    }

    public function setUser(Request $request)
    {
        $id = $request->input('id');
        $client_id = $request->input('client_id');
        $stations = User::where('id', $id)->update(['client_id' => $client_id]);
        return response()->json(["data" => $stations, "code" => 200]);
    }

    public function add(Request $request)
    {
        // Definir la clave JWT_SECRET directamente en código
        $Secret = 's3cR3tK3yF0rJWTt0k3nG3n3r4t10n';

        // Obtener el token desde el encabezado de autorización
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Token not provided'], 401);
        }

        try {
            // Decodificar el token para obtener el ID del usuario
            $decoded = JWT::decode($token, new Key($Secret, 'HS256'));
            $userId = $decoded->sub; // Asumiendo que el ID del usuario está en el campo 'sub'

            // Crear el nuevo cliente
            $item = Client::create($request->all());

            // Registrar la actividad de creación
            $this->activityService->logCreateActivity($item, $userId, $request);

            return response()->json(["data" => $item, "code" => 200]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid token: ' . $e->getMessage()], 401);
        }
    }

    public function upd(Request $request)
    {
        // Definir la clave JWT_SECRET directamente en código
        $Secret = 's3cR3tK3yF0rJWTt0k3nG3n3r4t10n';

        // Obtener el token desde el encabezado de autorización
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Token not provided'], 401);
        }

        try {
            // Decodificar el token para obtener el ID del usuario
            $decoded = JWT::decode($token, new Key($Secret, 'HS256'));
            $userId = $decoded->sub; // Asumiendo que el ID del usuario está en el campo 'sub'

            $id = $request->input('id');
            $item = Client::find($id);

            if (!$item) {
                return response()->json(['message' => 'Client not found'], 404);
            }

            // Obtener los datos originales antes de la actualización
            $originalData = $item->getOriginal();

            // Actualizar el modelo
            $item->fill($request->all())->save();

            // Obtener los cambios después de la actualización
            $changes = $item->getChanges();

            // Verificar si hay cambios
            if (!empty($changes)) {
                // Registrar la actividad
                $this->activityService->logActivity($item, $userId, null, $request);
            }

            return response()->json(["data" => $item, "code" => 200]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid token: ' . $e->getMessage()], 401);
        }
    }

    public function del(Request $request)
    {
        $id = $request->input('id');
        $item = Client::find($id);
        $item->delete();
        return response()->json(["data" => $item, "code" => 200]);
    }


    public function addClientReport(Request $request)
    {
        // Definir la clave JWT_SECRET directamente en código
        $Secret = 's3cR3tK3yF0rJWTt0k3nG3n3r4t10n';

        // Obtener el token desde el encabezado de autorización
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Token not provided'], 401);
        }

        try {
            // Decodificar el token para obtener el ID del usuario
            $decoded = JWT::decode($token, new Key($Secret, 'HS256'));
            $userId = $decoded->sub; // Asumiendo que el ID del usuario está en el campo 'sub'

            $id = $request->input('id');
            $item = Client::find($id);
            // $image = https://domintapi.com/client $item->image;

            if (!$item) {
                return response()->json(['message' => 'Client not found'], 404);
            }

            $description = $request->input('description');
            $importantChange = $request->input('important_change');
            $status = $request->input('status');

            // Registrar el reporte
            $this->activityService->logClientReport($item, $userId, $description, $importantChange, $status,$id, $request);

            return response()->json(['message' => 'Report logged successfully', 'code' => 200]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid token: ' . $e->getMessage()], 401);
        }
    }

    public function getXuser(Request $request)
    {
        $id = $request->input('id');
        $client = Client::where('id', $id)->first();
        $res = User::where('client_id', $id)->first();
        return response()->json(["data" => $client, "client_id" => $id, "code" => 200]);
    }

    public function getXuserPa(Request $request)
    {
        $id = $request->input('id');
        $res = User::where('client_id', $id)->first();
        return response()->json(["data" => $res, "code" => 200]);
    }

    public function getUserXClient(Request $request)
    {
        $id = $request->input('id');
        $res = User::where('client_id', $id)->first();
        return response()->json(["data" => $res, "code" => 200]);
    }

    public function suspendAll(Request $request)
    {
        $id = $request->input('id');
        $stations = Station::where('client_id', $id)->update(['status' => false]);
        return response()->json(["data" => $stations, "code" => 200]);
    }

    public function activateAll(Request $request)
    {
        $id = $request->input('id');
        $stations = Station::where('client_id', $id)->update(['status' => true]);
        return response()->json(["data" => $stations, "code" => 200]);
    }

    public function activateOne(Request $request)
    {
        $id = $request->input('id');
        $stations = Station::where('id', $id)->update(['status' => true]);
        return response()->json(["data" => $stations, "code" => 200]);
    }

    public function suspendOne(Request $request)
    {
        $id = $request->input('id');
        $stations = Station::where('id', $id)->update(['status' => false]);
        return response()->json(["data" => $stations, "code" => 200]);
    }

    public function allwStations()
    {
        $item = Client::all();
        $item = ClientResource::collection($item);
        return response()->json(["data" => $item, "code" => 200]);
    }

    public function ClientAllPlus()
    {
        $item = Client::all()->take(10);
        $item = ClientPlusResource::collection($item);
        return response()->json($item);
    }

    public function citiesbycountry(Request $request)
    {
        $id = $request->input('id');
        $item = Client::where('country_id', $id)->get();
        return response()->json(["data" => $item, "code" => 200]);
    }

    public function allcountries()
    {
        $item = Country::all();
        return response()->json(["data" => $item, "code" => 200]);
    }

    public function allcountriesfull()
    {
        $item = Country::all();
        $item = CountryResource::collection($item);
        return response()->json(["data" => $item, "code" => 200]);
    }

    public function buscar(Request $request)
    {
        $busqueda = $request->input('nombre');
        $items = Client::search($busqueda, null, true)
            ->orderBy('id', 'asc')
            ->get();
        return response()->json(["data" => $items, "buscando" => $busqueda, "code" => 200]);
    }

    public function rcControlGet(Request $request)
    {
        $id = $request->input('id');
        $item = Client::find($id);
        $item = new RcControlClientResource($item);
        return response()->json(["data" => $item, "code" => 200]);
    }

    public function getPlus(Request $request)
    {
        $id = $request->input('id');
        $item = Client::find($id);
        $item = new ClientPlusResource($item);
        return response()->json($item);
    }

    public function getXstation(Request $request)
    {
        $id = $request->input('id');
        $station = Station::find($id);
        $client_id = $station['client_id'];
        $item = Client::find($client_id);
        $item = new ClientResource($item);
        return response()->json(["data" => $item, "code" => 200]);
    }


 

    public function addImg(Request $request)
    {
        $id = $request->input('id');
        $image = $request->input('image');
        $rcimg = $request->input('rcimg');
        $copy = $request->input('rcimgcopy');
        $folder = 'client';
        $copyfolder = 'client-copy';
        $client = Client::find($id);

        if ($image) {
            $this->delImg($image);
        }

        $img_name = $this->decode($rcimg, $folder, $id);

        if ($copy) {
            $this->decode($copy, $copyfolder, $id);
        }

        $client->fill([
            'image' => $img_name
        ])->save();

        return response()->json(["data" => $client, "code" => 200]);
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

        file_put_contents("images/{$folder}/{$this->img_name}", $data);

        return $this->img_name;
    }

    public function delImg($file)
    {
        $folder = 'client';
        $toDel = "images/{$folder}/{$file}";
        $delCopy = "images/{$folder}-copy/{$file}";
        if (file_exists($toDel)) {
            unlink($toDel);
        } else {
            return response()->json(["data" => 'error del img', "code" => 200]);
        }

        if (file_exists($delCopy)) {
            unlink($delCopy);
        }
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Station;
use App\Http\Resources\RcControlStationSoloResource;
use App\Http\Resources\StationSoloResource;
use App\Http\Resources\StationSoloResource2minp;
use App\Http\Resources\StationStrResource;
use App\Services\ActivityService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Database\QueryException;

class RcControlStationController extends Controller
{
    private $img_name;
    protected $activityService;

    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    public function search(Request $request)
    {
        $search = $request->input('name');
        $items = Station::search($search, null, true)
            ->orderBy('id', 'asc')
            ->get();

        return response()->json(["data" => $items, "code" => 200]);
    }

    public function all()
    {
        $item = Station::orderBy('order', 'asc')->get();
        $item = RcControlStationSoloResource::collection($item);
        return response()->json(["data" => $item, "code" => 200]);
    }

    public function allPublic()
    {
        $limit = request()->query('limit');

        $query = Station::where('status', 1)
                        ->orderBy('order', 'asc');

        if ($limit) {
            $query->limit((int) $limit);
        }

        $item = $query->get();
        $item = RcControlStationSoloResource::collection($item);

        return response()->json(["data" => $item, "code" => 200]);
    }

    public function allRadio()
    {
        $limit = request()->query('limit');

        $query = Station::where('station_type_id', 0)
                        ->where('status', 1)
                        ->orderBy('order', 'asc');

        if ($limit) {
            $query->limit((int)$limit);
        }

        $item = $query->get();
        $item = RcControlStationSoloResource::collection($item);

        return response()->json(["data" => $item, "code" => 200]);
    }

    public function allTv()
    {
        $limit = request()->query('limit');

        $query = Station::where('station_type_id', 1)
                        ->where('status', 1)
                        ->orderBy('order', 'asc');

        if ($limit) {
            $query->limit((int)$limit);
        }

        $item = $query->get();
        $item = RcControlStationSoloResource::collection($item);

        return response()->json(["data" => $item, "code" => 200]);
    }

    public function add(Request $request)
    {
        $jwtSecret = 's3cR3tK3yF0rJWTt0k3nG3n3r4t10n';
        $token = $request->bearerToken();

        if (!$token) {
            \Log::error('Add station: Token not provided');
            return response()->json(['message' => 'Token not provided'], 401);
        }

        try {
            $decoded = JWT::decode($token, new Key($jwtSecret, 'HS256'));
            $userId = $decoded->sub;

            // Minimal validation for required fields
            $validated = $request->validate([
                'client_id' => 'required|integer',
                'name' => 'required|string|max:255',
                'station_type_id' => 'required|integer|in:0,1'
            ]);

            // Create station with validated data
            $item = Station::create($request->all());

            return response()->json(["data" => $item, "code" => 200]);
        } catch (\Firebase\JWT\ExpiredException $e) {
            \Log::error('Add station: JWT Expired: ' . $e->getMessage());
            return response()->json(['message' => 'Token expired'], 401);
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            \Log::error('Add station: JWT Invalid Signature: ' . $e->getMessage());
            return response()->json(['message' => 'Invalid token signature'], 401);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Add station: Validation failed: ' . json_encode($e->errors()));
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (QueryException $e) {
            \Log::error('Add station: Database error: ' . $e->getMessage());
            return response()->json(['message' => 'Database error: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            \Log::error('Add station: General error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to create station: ' . $e->getMessage()], 500);
        }
    }

    public function upd(Request $request)
    {
        $Secret = 's3cR3tK3yF0rJWTt0k3nG3n3r4t10n';
    
        $token = $request->bearerToken();
    
        if (!$token) {
            return response()->json(['message' => 'Token not provided'], 401);
        }
    
        try {
            $decoded = JWT::decode($token, new Key($Secret, 'HS256'));
            $userId = $decoded->sub;
    
            $id = $request->input('id');
            $item = Station::find($id);
    
            if (!$item) {
                return response()->json(['message' => 'Station not found'], 404);
            }
    
            $originalData = $item->getOriginal();
    
            $item->fill($request->all())->save();
    
            $changes = $item->getChanges();
    
            if (!empty($changes)) {
                $this->activityService->logActivity($item, $userId, null, $request);
            }
    
            return response()->json(["data" => $item, "code" => 200]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid token: ' . $e->getMessage()], 401);
        }
    }

    public function addStationReport(Request $request)
    {
        $Secret = 's3cR3tK3yF0rJWTt0k3nG3n3r4t10n';

        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Token not provided'], 401);
        }

        try {
            $decoded = JWT::decode($token, new Key($Secret, 'HS256'));
            $userId = $decoded->sub;

            $id = $request->input('id');
            $item = Station::find($id);

            if (!$item) {
                return response()->json(['message' => 'Station not found'], 404);
            }

            $description = $request->input('description');
            $importantChange = $request->input('important_change');
            $status = $request->input('status');

            $this->activityService->logStationReport($item, $userId, $description, $importantChange, $status , $id, $request);

            return response()->json(['message' => 'Report logged successfully', 'code' => 200]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid token: ' . $e->getMessage()], 401);
        }
    }

    public function decode($data, $folder, $id)
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
            $data = substr($data, strpos($data, ',') + 1);
            $type = strtolower($type[1]);

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
        $folder = 'station';
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
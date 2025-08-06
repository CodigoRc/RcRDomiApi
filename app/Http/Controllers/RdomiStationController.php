<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Station;
use App\Http\Resources\RdomiStationResource;

class RdomiStationController extends Controller
{
    public function RdomiAll()
    {
        $limit = request()->query('limit');
        $featured = request()->query('featured');

        $query = Station::where('status', 1)
                        ->orderBy('order', 'asc');

        if (!is_null($featured)) {
            $query->where('featured', 1);
        }

        if ($limit) {
            $query->limit((int) $limit);
        }

        $item = $query->get();
        $item = RdomiStationResource::collection($item);

        return response()->json(["data" => $item, "code" => 200]);
    }

    public function RdomiRadio()
    {
        $limit = request()->query('limit');
        $featured = request()->query('featured');

        $query = Station::where('station_type_id', 0)
                        ->where('status', 1)
                        ->orderBy('order', 'asc');

        if (!is_null($featured)) {
            $query->where('featured', 1);
        }

        if ($limit) {
            $query->limit((int)$limit);
        }

        $item = $query->get();
        $item = RdomiStationResource::collection($item);

        return response()->json(["data" => $item, "code" => 200]);
    }

    public function RdomiTv()
    {
        $limit = request()->query('limit');
        $featured = request()->query('featured');

        $query = Station::where('station_type_id', 1)
                        ->where('status', 1)
                        ->orderBy('order', 'asc');

        if (!is_null($featured)) {
            $query->where('featured', 1);
        }

        if ($limit) {
            $query->limit((int)$limit);
        }

        $item = $query->get();
        $item = RdomiStationResource::collection($item);

        return response()->json(["data" => $item, "code" => 200]);
    }

    public function RdomiById($id)
    {
        $station = Station::where('status', 1)->find($id);
        if (!$station) {
            return response()->json(['message' => 'Station not found'], 404);
        }
        return response()->json(["data" => new RdomiStationResource($station), "code" => 200]);
    }

    public function RdomiByClient($client_id)
    {
        $stations = Station::where('status', 1)->where('client_id', $client_id)->orderBy('order', 'asc')->get();
        $data = RdomiStationResource::collection($stations);
        return response()->json(["data" => $data, "code" => 200]);
    }
} 
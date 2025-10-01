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

        $query = Station::leftJoin('service_stats', function($join) {
                        $join->on('station.id', '=', 'service_stats.service_id')
                             ->where('service_stats.type', '=', 'view');
                    })
                    ->where('station.status', 1)
                    ->orderBy('service_stats.count', 'desc')
                    ->orderBy('station.order', 'asc')
                    ->select('station.*');

        if (!is_null($featured)) {
            $query->where('station.featured', 1);
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

        $query = Station::leftJoin('service_stats', function($join) {
                        $join->on('station.id', '=', 'service_stats.service_id')
                             ->where('service_stats.type', '=', 'view');
                    })
                    ->where('station.station_type_id', 0)
                    ->where('station.status', 1)
                    ->orderBy('service_stats.count', 'desc')
                    ->orderBy('station.order', 'asc')
                    ->select('station.*');

        if (!is_null($featured)) {
            $query->where('station.featured', 1);
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

        $query = Station::leftJoin('service_stats', function($join) {
                        $join->on('station.id', '=', 'service_stats.service_id')
                             ->where('service_stats.type', '=', 'view');
                    })
                    ->where('station.station_type_id', 1)
                    ->where('station.status', 1)
                    ->orderBy('service_stats.count', 'desc')
                    ->orderBy('station.order', 'asc')
                    ->select('station.*');

        if (!is_null($featured)) {
            $query->where('station.featured', 1);
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
        $stations = Station::leftJoin('service_stats', function($join) {
                        $join->on('station.id', '=', 'service_stats.service_id')
                             ->where('service_stats.type', '=', 'view');
                    })
                    ->where('station.status', 1)
                    ->where('station.client_id', $client_id)
                    ->orderBy('service_stats.count', 'desc')
                    ->orderBy('station.order', 'asc')
                    ->select('station.*')
                    ->get();
        $data = RdomiStationResource::collection($stations);
        return response()->json(["data" => $data, "code" => 200]);
    }
} 
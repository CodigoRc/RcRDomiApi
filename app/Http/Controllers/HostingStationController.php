<?php

namespace App\Http\Controllers;

use App\Models\HostingStation;
use Illuminate\Http\Request;

class HostingStationController extends Controller
{
    public function index(Request $request)
    {
        $query = HostingStation::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('station_id', 'like', "%{$search}%")
                  ->orWhere('cpanel', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%")
                  ->orWhere('pass', 'like', "%{$search}%")
                  ->orWhere('ftp_user', 'like', "%{$search}%")
                  ->orWhere('ftp_pass', 'like', "%{$search}%")
                  ->orWhere('url', 'like', "%{$search}%");
            });
        }

        $sortColumn = $request->input('sort', 'id');
        $sortDirection = $request->input('direction', 'asc');
        $query->orderBy($sortColumn, $sortDirection);

        $perPage = 25;
        $stationWebAdmins = $query->paginate($perPage);

        return response()->json($stationWebAdmins);
    }

    public function show(Request $request)
    {
        $station_id = $request->query('station_id');
        $request->validate([
            'station_id' => 'required|integer|exists:station_web_admin,station_id',
        ], ['station_id.required' => 'The station_id parameter is required.']);

        $station = HostingStation::where('station_id', $station_id)->firstOrFail();
        return response()->json($station);
    }

    public function store(Request $request)
    {
        $request->validate([
            'station_id' => 'required|integer', // Removed exists:stations,id
            'cpanel' => 'nullable|string|max:255',
            'user_name' => 'nullable|string|max:255',
            'pass' => 'nullable|string|max:255',
            'ftp_user' => 'nullable|string|max:255',
            'ftp_pass' => 'nullable|string|max:255',
            'url' => 'nullable|url|max:255',
        ]);

        $station = HostingStation::create($request->only([
            'station_id',
            'cpanel',
            'user_name',
            'pass',
            'ftp_user',
            'ftp_pass',
            'url',
        ]));

        return response()->json($station, 201);
    }

    public function update(Request $request)
    {
        $request->validate([
            'station_id' => 'required|integer|exists:station_web_admin,station_id',
            'cpanel' => 'nullable|string|max:255',
            'user_name' => 'nullable|string|max:255',
            'pass' => 'nullable|string|max:255',
            'ftp_user' => 'nullable|string|max:255',
            'ftp_pass' => 'nullable|string|max:255',
            'url' => 'nullable|url|max:255',
        ]);

        $station = HostingStation::where('station_id', $request->station_id)->firstOrFail();
        $station->update($request->only([
            'station_id',
            'cpanel',
            'user_name',
            'pass',
            'ftp_user',
            'ftp_pass',
            'url',
        ]));

        return response()->json($station);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'station_id' => 'required|integer|exists:station_web_admin,station_id',
        ]);

        $station = HostingStation::where('station_id', $request->station_id)->firstOrFail();
        $station->delete();

        return response()->json(null, 204);
    }
}
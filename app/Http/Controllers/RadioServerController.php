<?php

namespace App\Http\Controllers;

use App\Models\RadioServer;
use Illuminate\Http\Request;

class RadioServerController extends Controller
{
    public function index(Request $request)
    {
        $servers = RadioServer::all();
        return response()->json(["data" => $servers, "code" => 200]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'centova_url' => 'nullable|string|max:255',
            'audio_stream_url' => 'nullable|string|max:255',
            'dns' => 'nullable|string|max:255',
            'extensions_url' => 'nullable|string|max:255',
        ]);

        $server = RadioServer::create($request->all());
        return response()->json(["data" => $server, "code" => 201]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:radio_servers,id',
            'name' => 'required|string|max:255',
            'centova_url' => 'nullable|string|max:255',
            'audio_stream_url' => 'nullable|string|max:255',
            'dns' => 'nullable|string|max:255',
            'extensions_url' => 'nullable|string|max:255',
        ]);

        $radioServer = RadioServer::findOrFail($request->id);
        $radioServer->update($request->all());
        return response()->json(["data" => $radioServer, "code" => 200]);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:radio_servers,id',
        ]);

        $radioServer = RadioServer::findOrFail($request->id);
        $radioServer->delete();
        return response()->json(["data" => null, "code" => 204]);
    }
}
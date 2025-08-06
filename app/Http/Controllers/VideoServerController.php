<?php
namespace App\Http\Controllers;

use App\Models\VideoServer;
use Illuminate\Http\Request;

class VideoServerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $servers = VideoServer::all();
        return response()->json($servers);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'fms_url' => 'required|string|max:255',
            'wowza_url' => 'required|string|max:255',
        ]);

        $server = VideoServer::create($request->all());
        return response()->json($server, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:video_server,id',
            'name' => 'required|string|max:255',
            'fms_url' => 'required|string|max:255',
            'wowza_url' => 'required|string|max:255',
        ]);

        $videoServer = VideoServer::findOrFail($request->id);
        $videoServer->update($request->all());
        return response()->json($videoServer);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:video_server,id',
        ]);

        $videoServer = VideoServer::findOrFail($request->id);
        $videoServer->delete();
        return response()->json(null, 204);
    }
}
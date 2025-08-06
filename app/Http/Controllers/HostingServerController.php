<?php
namespace App\Http\Controllers;

use App\Models\HostingServer;
use Illuminate\Http\Request;

class HostingServerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $servers = HostingServer::all();
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
            'cpanel_url' => 'required|string|max:255',
            'admin_url' => 'required|string|max:255',
        ]);

        $server = HostingServer::create($request->all());
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
            'id' => 'required|integer|exists:hosting_server,id',
            'name' => 'required|string|max:255',
            'cpanel_url' => 'required|string|max:255',
            'admin_url' => 'required|string|max:255',
        ]);

        $hostingServer = HostingServer::findOrFail($request->id);
        $hostingServer->update($request->all());
        return response()->json($hostingServer);
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
            'id' => 'required|integer|exists:hosting_server,id',
        ]);

        $hostingServer = HostingServer::findOrFail($request->id);
        $hostingServer->delete();
        return response()->json(null, 204);
    }
}
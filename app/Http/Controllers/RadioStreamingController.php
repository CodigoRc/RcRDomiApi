<?php
namespace App\Http\Controllers;

use App\Models\RadioStreaming;
use Illuminate\Http\Request;
use App\Helpers\ActivityHelper;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RadioStreamingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $streams = RadioStreaming::all();
        if ($streams->isEmpty()) {
            return response()->json(["message" => "No data found", "code" => 404]);
        }
        return response()->json(["data" => $streams, "code" => 200]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validar datos entrantes
        $validated = $request->validate([
            'station_id' => 'required|integer|unique:radio_streaming,station_id',
            'radio_server_id' => 'nullable|integer',
            'ip' => 'nullable|string|max:45',
            'host' => 'nullable|string|max:255',
            'port' => 'nullable|integer|min:1|max:65535',
            'username' => 'nullable|string|max:100',
            'password' => 'nullable|string|max:100',
            'stream_password' => 'nullable|string|max:100',
            'stream_ssl_url' => 'nullable|string|max:255',
            'autodj_enabled' => 'nullable|boolean',
            'bitrate_limit' => 'nullable|integer|min:1',
            'listener_limit' => 'nullable|integer|min:1',
            'bandwidth_limit' => 'nullable|integer|min:1',
            'script_config' => 'nullable|string',
        ]);

        // Map radio_server_id to server_id
        $validated['server_id'] = $validated['radio_server_id'] ?? null;
        unset($validated['radio_server_id']);

        $stream = RadioStreaming::create($validated);
        
        // Registrar la actividad
        /*
        ActivityHelper::log(
            RadioStreaming::class, 
            $stream->station_id, 
            'created', 
            'Created a new radio streaming with station_id ' . $stream->station_id
        );
        */
        
        return response()->json(["data" => $stream, "code" => 201]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            // Validar datos entrantes
            $validated = $request->validate([
                'station_id' => 'required|integer|exists:radio_streaming,station_id',
                'radio_server_id' => 'nullable|integer',
                'ip' => 'nullable|string|max:45',
                'host' => 'nullable|string|max:255',
                'port' => 'nullable|integer|min:1|max:65535',
                'username' => 'nullable|string|max:100',
                'password' => 'nullable|string|max:100',
                'stream_password' => 'nullable|string|max:100',
                'stream_ssl_url' => 'nullable|string|max:255',
                'autodj_enabled' => 'nullable|boolean',
                'bitrate_limit' => 'nullable|integer|min:1',
                'listener_limit' => 'nullable|integer|min:1',
                'bandwidth_limit' => 'nullable|integer|min:1',
                'script_config' => 'nullable|string',
            ]);

            // Map radio_server_id to server_id
            $validated['server_id'] = $validated['radio_server_id'] ?? null;
            unset($validated['radio_server_id']);

            $stream = RadioStreaming::where('station_id', $validated['station_id'])->firstOrFail();
            $original = $stream->getOriginal();
            $stream->update($validated);
            
            // Obtener los cambios realizados
            $changes = $stream->getChanges();
            $changedFields = array_keys($changes);
            
            // Crear la descripción detallada
            $description = 'Updated radio streaming with station_id ' . $stream->station_id . '. Changed fields: ' . implode(', ', $changedFields);
            
            // Registrar la actividad
            /*
            ActivityHelper::log(
                RadioStreaming::class, 
                $stream->station_id, 
                'updated', 
                $description
            );
            */
            
            return response()->json(["data" => $stream, "code" => 200]);
        } catch (ModelNotFoundException $e) {
            return response()->json(["message" => "Data not found", "code" => 404]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        try {
            $validated = $request->validate(['station_id' => 'required|integer|exists:radio_streaming,station_id']);
            $stream = RadioStreaming::where('station_id', $validated['station_id'])->firstOrFail();
            $stream->delete();
            
            // Registrar la actividad
            /*
            ActivityHelper::log(
                RadioStreaming::class, 
                $stream->station_id, 
                'deleted', 
                'Deleted radio streaming with station_id ' . $stream->station_id
            );
            */
            
            return response()->json(["data" => null, "code" => 204]);
        } catch (ModelNotFoundException $e) {
            return response()->json(["message" => "Data not found", "code" => 404]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        try {
            $validated = $request->validate(['station_id' => 'required|integer|exists:radio_streaming,station_id']);
            $stream = RadioStreaming::where('station_id', $validated['station_id'])->firstOrFail();
            return response()->json(["data" => $stream, "code" => 200]);
        } catch (ModelNotFoundException $e) {
            return response()->json(["message" => "Data not found", "code" => 404]);
        }
    }
}
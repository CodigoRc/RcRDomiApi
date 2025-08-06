<?php
namespace App\Http\Controllers;

use App\Models\VideoStream;
use Illuminate\Http\Request;
use App\Helpers\ActivityHelper;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Exception;

class VideoStreamingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $streams = VideoStream::all();
            if ($streams->isEmpty()) {
                return response()->json(["message" => "No data found", "code" => 404]);
            }
            return response()->json(["data" => $streams, "code" => 200]);
        } catch (Exception $e) {
            Log::error('VideoStreaming index error: ' . $e->getMessage());
            return response()->json(["message" => "Internal server error", "code" => 500]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            Log::info('VideoStreaming store request:', $request->all());
            
            // Validate incoming data
            $validated = $request->validate([
                'station_id' => 'required|integer',
                'server_id' => 'nullable|integer',
                'ip' => 'nullable|string|max:45',
                'host' => 'nullable|string|max:255',
                'port' => 'nullable|integer|min:1|max:65535',
                'username' => 'nullable|string|max:100',
                'password' => 'nullable|string|max:100',
                'stream_password' => 'nullable|string|max:100',
                'stream_ssl_url' => 'nullable|string|max:255',
                'autodj_enabled' => 'nullable|boolean',
                'bitrate_limit' => 'nullable|integer|min:1',
                'viewer_limit' => 'nullable|integer|min:1',
                'bandwidth_limit' => 'nullable|integer|min:1',
                'script_config' => 'nullable|string',
                'stream_key' => 'nullable|string|max:255',
                'stream_username' => 'nullable|string|max:255',
            ]);

            Log::info('VideoStreaming validated data:', $validated);

            $stream = VideoStream::create($validated);
            Log::info('VideoStreaming created successfully:', ['id' => $stream->id]);
            
            return response()->json([
                "data" => $stream, 
                "code" => 201,
                "message" => "Video streaming created successfully"
            ]);

        } catch (Exception $e) {
            Log::error('VideoStreaming store error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                "message" => "Error creating video streaming: " . $e->getMessage(),
                "code" => 500
            ], 500);
        }
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
            // Validate incoming data
            $validated = $request->validate([
                'station_id' => 'required|integer|exists:video_streaming,station_id',
                'server_id' => 'nullable|integer',
                'ip' => 'nullable|string|max:45',
                'host' => 'nullable|string|max:255',
                'port' => 'nullable|integer|min:1|max:65535',
                'username' => 'nullable|string|max:100',
                'password' => 'nullable|string|max:100',
                'stream_password' => 'nullable|string|max:100',
                'stream_ssl_url' => 'nullable|string|max:255',
                'autodj_enabled' => 'nullable|boolean',
                'bitrate_limit' => 'nullable|integer|min:1',
                'viewer_limit' => 'nullable|integer|min:1',
                'bandwidth_limit' => 'nullable|integer|min:1',
                'script_config' => 'nullable|string',
                'stream_key' => 'nullable|string|max:255',
                'stream_username' => 'nullable|string|max:255',
            ]);

            $stream = VideoStream::where('station_id', $validated['station_id'])->firstOrFail();
            $stream->update($validated);
            
            // Get the changes made
            $changes = $stream->getChanges();
            $changedFields = array_keys($changes);
            
            // Create detailed description
            $description = 'Updated video streaming with station_id ' . $stream->station_id . '. Changed fields: ' . implode(', ', $changedFields);
            
            // Log the activity
            /*
            ActivityHelper::log(
                VideoStream::class, 
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
            $validated = $request->validate(['station_id' => 'required|integer|exists:video_streaming,station_id']);
            $stream = VideoStream::where('station_id', $validated['station_id'])->firstOrFail();
            $stream->delete();
            
            // Log the activity
            /*
            ActivityHelper::log(
                VideoStream::class, 
                $stream->station_id, 
                'deleted', 
                'Deleted video streaming with station_id ' . $stream->station_id
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
            $validated = $request->validate(['station_id' => 'required|integer|exists:video_streaming,station_id']);
            $stream = VideoStream::where('station_id', $validated['station_id'])->firstOrFail();
            return response()->json(["data" => $stream, "code" => 200]);
        } catch (ModelNotFoundException $e) {
            return response()->json(["message" => "Data not found", "code" => 404]);
        }
    }
}
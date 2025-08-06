<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Station;
use Illuminate\Http\Request;
use App\Http\Resources\ActivityResource;
use App\Services\ActivityService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\User;
use App\Models\UserNotification;
use App\Notifications\ActivityNotification;
use Illuminate\Support\Facades\Log;

class ActivityController extends Controller
{
    protected $activityService;

    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    // Obtener todas las actividades
    public function index()
    {
        $activities = Activity::orderBy('created_at', 'desc')->get();
        return response()->json(ActivityResource::collection($activities));
    }

    // Obtener una actividad específica por ID
    public function show($id)
    {
        $activity = Activity::find($id);

        if (!$activity) {
            return response()->json(['message' => 'Activity not found'], 404);
        }

        return response()->json($activity);
    }

    // Crear una nueva actividad
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|integer',
            'client_id' => 'nullable|integer',
            'station_id' => 'nullable|integer',
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
            'status' => 'nullable|string',
            'action' => 'required|string',
            'description' => 'nullable|string',
            'important_change' => 'nullable|string',
        ]);

        $activity = Activity::create($validatedData);

        return response()->json($activity, 201);
    }

    // Actualizar una actividad existente
    public function update(Request $request, $id)
    {
        $activity = Activity::find($id);

        if (!$activity) {
            return response()->json(['message' => 'Activity not found'], 404);
        }

        $validatedData = $request->validate([
            'user_id' => 'required|integer',
            'client_id' => 'nullable|integer',
            'station_id' => 'nullable|integer',
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
            'status' => 'nullable|string',
            'action' => 'required|string',
            'description' => 'nullable|string',
            'important_change' => 'nullable|string',
        ]);

        $activity->fill($validatedData);
        $activity->save();

        // Preparar los datos de la notificación
        $notificationData = [
            'icon' => 'activity-icon', // Puedes ajustar esto según sea necesario
            'image' => $validatedData['image'] ?? null,
            'title' => $validatedData['important_change'] ?? 'Activity Updated',
            'description' => $validatedData['description'] ?? 'No Description',
            'time' => now()->toIso8601String(),
            'link' => $this->generateLink($validatedData),
            'useRouter' => true,
        ];

        // Llamar al método createNotification
        $this->createNotification($notificationData);

        return response()->json($activity);
    }

    // Generar el enlace basado en client_id y station_id
    private function generateLink($validatedData)
    {
        if (!empty($validatedData['client_id'])) {
            return '/clients/' . $validatedData['client_id'];
        } elseif (!empty($validatedData['station_id'])) {
            return '/stations/' . $validatedData['station_id'];
        } else {
            return '/';
        }
    }

     // NOTIFICACIONES
     public function createNotification(array $data)
     {
         try {
             $validatedData = validator($data, [
                 'icon' => 'nullable|string',
                 'image' => 'nullable|string',
                 'title' => 'required|string',
                 'description' => 'required|string',
                 'time' => 'required|string',
                 'link' => 'nullable|string',
                 'useRouter' => 'nullable|boolean',
             ])->validate();
 
             $admins = User::where('role', 'admin')->get();
 
             if ($admins->isEmpty()) {
                 return response()->json(['message' => 'No admin users found'], 404);
             }
 
             foreach ($admins as $admin) {
                 $admin->notify(new ActivityNotification($validatedData));
 
                 // Crear una entrada en la tabla user_notifications
                 UserNotification::create([
                     'user_id' => $admin->id,
                     'notification_id' => $admin->notifications()->latest()->first()->id,
                     'is_read' => false,
                     'is_deleted' => false,
                 ]);
             }
 
             return response()->json(['message' => 'Notification sent to all admins successfully']);
         } catch (\Exception $e) {
             Log::error('Error sending notification: ' . $e->getMessage());
             return response()->json(['message' => 'Error sending notification', 'error' => $e->getMessage()], 500);
         }
     }

    // Eliminar una actividad
    public function destroy($id)
    {
        $activity = Activity::find($id);

        if (!$activity) {
            return response()->json(['message' => 'Activity not found'], 404);
        }

        $activity->delete();

        return response()->json(['message' => 'Activity deleted successfully']);
    }

    // Añadir un reporte de estación
    public function addStationReport(Request $request)
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
            $item = Station::find($id);

            if (!$item) {
                return response()->json(['message' => 'Station not found'], 404);
            }

            $description = $request->input('description');
            $importantChange = $request->input('important_change');
            $status = $request->input('status');

            // Registrar el reporte
            $this->activityService->logStationReport($item, $userId, $description, $importantChange, $status, $id, $request);

            return response()->json(['message' => 'Report logged successfully', 'code' => 200]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid token: ' . $e->getMessage()], 401);
        }
    }

    // Obtener actividades por client_id y model_type Client
    public function getActivitiesByClientId($clientId)
    {
        $activities = Activity::where('client_id', $clientId)
                              ->where('model_type', 'Client')
                              ->orderBy('created_at', 'desc')
                              ->get();

        return response()->json(ActivityResource::collection($activities));
    }

    // Obtener actividades por station_id y model_type Station
    public function getActivitiesByStationId($stationId)
    {
        $activities = Activity::where('station_id', $stationId)
                              ->where('model_type', 'Station')
                              ->orderBy('created_at', 'desc')
                              ->get();

        return response()->json(ActivityResource::collection($activities));
    }
}
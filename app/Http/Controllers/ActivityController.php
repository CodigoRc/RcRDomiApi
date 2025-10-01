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

    // Endpoint de prueba para verificar que funciona
    public function testLegacyActivities(Request $request)
    {
        try {
            Log::info('Testing legacy activities endpoint...');
            
            // Primero, vamos a ver todas las actividades con action='report'
            $allReportActivities = Activity::where('action', 'report')->get();
            Log::info('All activities with action=report:', ['count' => $allReportActivities->count()]);
            
            // Analizar todos los valores únicos de status
            $uniqueStatuses = $allReportActivities->pluck('status')->unique()->values()->toArray();
            Log::info('Unique status values in report activities:', $uniqueStatuses);
            
            // Contar actividades por status
            $statusCounts = $allReportActivities->groupBy('status')->map(function($group) {
                return $group->count();
            })->toArray();
            Log::info('Status counts in report activities:', $statusCounts);
            
            // Crear array simple para evitar problemas con map
            $sampleReportActivities = [];
            foreach ($allReportActivities->take(5) as $activity) {
                $sampleReportActivities[] = [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'action' => $activity->action,
                    'status' => $activity->status,
                    'status_type' => is_null($activity->status) ? 'null' : gettype($activity->status),
                    'status_raw' => var_export($activity->status, true),
                    'status_length' => is_string($activity->status) ? strlen($activity->status) : 'N/A'
                ];
            }
            Log::info('Sample report activities:', $sampleReportActivities);
            
            // Buscar todas las actividades legacy - action='report' con status='report'
            $legacyActivities = Activity::where('action', 'report')
                ->where('status', 'report')
                ->get();
            
            Log::info('Legacy activities found:', ['count' => $legacyActivities->count()]);
            
            // Crear array simple para legacy activities también
            $sampleLegacyActivities = [];
            foreach ($legacyActivities->take(5) as $activity) {
                $sampleLegacyActivities[] = [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'action' => $activity->action,
                    'status' => $activity->status,
                    'status_type' => is_null($activity->status) ? 'null' : gettype($activity->status)
                ];
            }
            
            $response = [
                'message' => 'Test endpoint working',
                'all_report_count' => $allReportActivities->count(),
                'found_count' => $legacyActivities->count(),
                'unique_status_values' => $uniqueStatuses,
                'status_counts' => $statusCounts,
                'sample_report_activities' => $sampleReportActivities,
                'sample_legacy_activities' => $sampleLegacyActivities
            ];
            
            Log::info('Test endpoint response:', $response);
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('Test endpoint error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['message' => 'Test endpoint error', 'error' => $e->getMessage()], 500);
        }
    }

    // Endpoint directo para encontrar actividades específicas del frontend
    public function findSpecificLegacyActivities(Request $request)
    {
        try {
            Log::info('Finding specific legacy activities...');
            
            // Buscar actividades con action='report' y descripciones específicas que aparecen en los logs
            $specificActivities = Activity::where('action', 'report')
                ->where(function($query) {
                    $query->where('description', 'like', '%NCCCCCCCCCCCCCCCCCCC%')
                          ->orWhere('description', 'like', '%ZZZZZZZZZZZZZZZ%')
                          ->orWhere('description', 'like', '%problema de audio%');
                })
                ->get();
            
            Log::info('Specific activities found:', ['count' => $specificActivities->count()]);
            
            $response = [
                'message' => 'Specific legacy activities found',
                'found_count' => $specificActivities->count(),
                'activities' => $specificActivities->map(function($activity) {
                    return [
                        'id' => $activity->id,
                        'description' => $activity->description,
                        'action' => $activity->action,
                        'status' => $activity->status,
                        'created_at' => $activity->created_at
                    ];
                })
            ];
            
            Log::info('Specific activities response:', $response);
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('Specific activities error: ' . $e->getMessage());
            return response()->json(['message' => 'Error finding specific activities', 'error' => $e->getMessage()], 500);
        }
    }

    // Eliminar permanentemente las actividades específicas encontradas
    public function deleteSpecificLegacyActivities(Request $request)
    {
        try {
            Log::info('Deleting specific legacy activities...');
            
            // IDs específicos de las actividades legacy encontradas
            $legacyIds = [717, 719, 720, 722, 723];
            
            $deletedCount = 0;
            $deletedActivities = [];
            
            foreach ($legacyIds as $id) {
                $activity = Activity::find($id);
                if ($activity) {
                    $deletedActivities[] = [
                        'activity_id' => $activity->id,
                        'description' => $activity->description,
                        'action' => $activity->action,
                        'status' => $activity->status
                    ];
                    
                    $activity->delete(); // Eliminación física
                    $deletedCount++;
                    
                    Log::info('Specific legacy activity permanently deleted:', [
                        'activity_id' => $activity->id,
                        'description' => $activity->description,
                        'action' => $activity->action,
                        'status' => $activity->status
                    ]);
                }
            }
            
            $response = [
                'message' => "Successfully permanently deleted {$deletedCount} specific legacy activity(ies)",
                'deleted_count' => $deletedCount,
                'deleted_activities' => $deletedActivities
            ];
            
            Log::info('Specific legacy activities permanently deleted:', $response);
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('Specific legacy activities deletion error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['message' => 'Error deleting specific legacy activities', 'error' => $e->getMessage()], 500);
        }
    }

    // Eliminar específicamente Activity ID 701
    public function deleteActivity701(Request $request)
    {
        try {
            Log::info('Deleting Activity ID 701 specifically...');
            
            // Buscar y eliminar Activity ID 701
            $activity = Activity::find(701);
            
            if (!$activity) {
                return response()->json(['message' => 'Activity ID 701 not found'], 404);
            }
            
            $activityData = [
                'id' => $activity->id,
                'description' => $activity->description,
                'action' => $activity->action,
                'status' => $activity->status,
                'created_at' => $activity->created_at,
                'user_id' => $activity->user_id
            ];
            
            // Eliminar definitivamente
            $activity->delete();
            
            $response = [
                'message' => 'Activity ID 701 deleted successfully',
                'deleted_activity' => $activityData
            ];
            
            Log::info('Activity ID 701 deleted:', $response);
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('Error deleting Activity ID 701: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['message' => 'Error deleting Activity ID 701', 'error' => $e->getMessage()], 500);
        }
    }

    // Eliminar actividad individual
    public function deleteIndividualActivity(Request $request)
    {
        try {
            $activityId = $request->input('activity_id');
            
            if (!$activityId) {
                return response()->json(['message' => 'Activity ID is required'], 400);
            }
            
            Log::info("Deleting individual activity: {$activityId}");
            
            $activity = Activity::find($activityId);
            
            if (!$activity) {
                return response()->json(['message' => 'Activity not found'], 404);
            }
            
            $activityData = [
                'id' => $activity->id,
                'description' => $activity->description,
                'action' => $activity->action,
                'status' => $activity->status,
                'created_at' => $activity->created_at
            ];
            
            $activity->delete();
            
            $response = [
                'message' => 'Individual activity deleted successfully',
                'deleted_activity' => $activityData
            ];
            
            Log::info('Individual activity deleted:', $response);
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('Individual delete error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['message' => 'Error deleting individual activity', 'error' => $e->getMessage()], 500);
        }
    }

    // Eliminar forzadamente todas las actividades en papelera
    public function forceDeleteTrashActivities(Request $request)
    {
        try {
            Log::info('Force deleting all trash activities...');
            
            // Buscar todas las actividades report que aparecen como eliminadas
            $allReportActivities = Activity::where('action', 'report')->get();
            
            $deletedCount = 0;
            $deletedActivities = [];
            
            foreach ($allReportActivities as $activity) {
                // Eliminar cada actividad report
                $deletedActivities[] = [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'status' => $activity->status,
                    'created_at' => $activity->created_at
                ];
                
                $activity->delete();
                $deletedCount++;
            }
            
            $response = [
                'message' => 'Force delete completed',
                'deleted_count' => $deletedCount,
                'deleted_activities' => $deletedActivities
            ];
            
            Log::info('Force delete completed:', $response);
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('Force delete error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['message' => 'Error force deleting trash activities', 'error' => $e->getMessage()], 500);
        }
    }

    // Analizar actividades en la papelera
    public function analyzeTrashActivities(Request $request)
    {
        try {
            Log::info('Analyzing trash activities...');
            
            // Buscar actividades que aparecen como eliminadas en el frontend
            $allActivities = Activity::where('action', 'report')->get();
            
            $trashAnalysis = [
                'total_report_activities' => $allActivities->count(),
                'by_status' => [],
                'by_description_pattern' => [],
                'sample_activities' => []
            ];
            
            // Agrupar por status
            $statusGroups = $allActivities->groupBy('status');
            foreach ($statusGroups as $status => $activities) {
                $trashAnalysis['by_status'][$status] = $activities->count();
            }
            
            // Agrupar por patrones de descripción
            $descriptionPatterns = [
                'No audio streaming' => 'No audio streaming',
                'sdfgsdfg' => 'sdfgsdfg',
                'problema de audio' => 'problema de audio',
                'TESTING' => 'TESTING',
                'Hosting' => 'Hosting',
                'Domain' => 'Domain',
                'STATION IS NOW ONLINE' => 'STATION IS NOW ONLINE'
            ];
            
            foreach ($descriptionPatterns as $patternName => $pattern) {
                $count = $allActivities->filter(function($activity) use ($pattern) {
                    return str_contains($activity->description, $pattern);
                })->count();
                
                if ($count > 0) {
                    $trashAnalysis['by_description_pattern'][$patternName] = $count;
                }
            }
            
            // Muestras de actividades
            $trashAnalysis['sample_activities'] = $allActivities->take(10)->map(function($activity) {
                return [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'action' => $activity->action,
                    'status' => $activity->status,
                    'created_at' => $activity->created_at,
                    'user_id' => $activity->user_id
                ];
            })->toArray();
            
            $response = [
                'message' => 'Trash activities analysis completed',
                'analysis' => $trashAnalysis
            ];
            
            Log::info('Trash activities analysis:', $response);
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('Trash activities analysis error: ' . $e->getMessage());
            return response()->json(['message' => 'Error analyzing trash activities', 'error' => $e->getMessage()], 500);
        }
    }

    // Vista previa de actividades ghost que se van a eliminar
    public function previewGhostActivities(Request $request)
    {
        try {
            Log::info('Previewing ghost activities...');
            
            // Buscar actividades "ghost" - report con patrones específicos que aparecen como undefined
            $ghostPatterns = [
                'mandorwerm',
                'raffaaaaa',
                'NCCCCCCCCCCCCCCCCCCC',
                'ZZZZZZZZZZZZZZZ',
                'problema de audio',
                'No audio streaming',
                'TESTING INTENAL TICKET',
                'Hosting issue',
                'STATION IS NOW ONLINE ON SERVER',
                'THIS STATION IS NOW ONLINE ON SERVER',
                'Domain not working',
                'sdfgsdfg'
            ];
            
            $ghostActivities = Activity::where('action', 'report')
                ->where(function($query) use ($ghostPatterns) {
                    foreach ($ghostPatterns as $pattern) {
                        $query->orWhere('description', 'like', "%{$pattern}%");
                    }
                })
                ->get();
            
            Log::info('Ghost activities preview:', ['count' => $ghostActivities->count()]);
            
            $previewActivities = [];
            foreach ($ghostActivities as $activity) {
                $previewActivities[] = [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'action' => $activity->action,
                    'status' => $activity->status,
                    'created_at' => $activity->created_at,
                    'user_id' => $activity->user_id
                ];
            }
            
            $response = [
                'message' => "Preview: {$ghostActivities->count()} ghost activities will be deleted",
                'count' => $ghostActivities->count(),
                'activities' => $previewActivities
            ];
            
            Log::info('Ghost activities preview response:', $response);
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('Ghost activities preview error: ' . $e->getMessage());
            return response()->json(['message' => 'Error previewing ghost activities', 'error' => $e->getMessage()], 500);
        }
    }

    // Eliminar permanentemente solo las actividades "ghost" (report con patrones específicos)
    public function deleteGhostActivities(Request $request)
    {
        try {
            Log::info('Deleting ghost activities...');
            
            // Buscar actividades "ghost" - report con patrones específicos que aparecen como undefined
            $ghostPatterns = [
                'mandorwerm',
                'raffaaaaa',
                'NCCCCCCCCCCCCCCCCCCC',
                'ZZZZZZZZZZZZZZZ',
                'problema de audio',
                'No audio streaming',
                'TESTING INTENAL TICKET',
                'Hosting issue',
                'STATION IS NOW ONLINE ON SERVER',
                'THIS STATION IS NOW ONLINE ON SERVER',
                'Domain not working',
                'sdfgsdfg'
            ];
            
            $ghostActivities = Activity::where('action', 'report')
                ->where(function($query) use ($ghostPatterns) {
                    foreach ($ghostPatterns as $pattern) {
                        $query->orWhere('description', 'like', "%{$pattern}%");
                    }
                })
                ->get();
            
            Log::info('Ghost activities found:', ['count' => $ghostActivities->count()]);
            
            $deletedCount = 0;
            $deletedActivities = [];
            
            foreach ($ghostActivities as $activity) {
                try {
                    $deletedActivities[] = [
                        'activity_id' => $activity->id,
                        'description' => $activity->description,
                        'action' => $activity->action,
                        'status' => $activity->status
                    ];
                    
                    $activity->delete(); // Eliminación física
                    $deletedCount++;
                    
                    Log::info('Ghost activity permanently deleted:', [
                        'activity_id' => $activity->id,
                        'description' => $activity->description,
                        'action' => $activity->action,
                        'status' => $activity->status
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error deleting individual ghost activity:', [
                        'activity_id' => $activity->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            $response = [
                'message' => "Successfully permanently deleted {$deletedCount} ghost activity(ies)",
                'deleted_count' => $deletedCount,
                'deleted_activities' => $deletedActivities
            ];
            
            Log::info('Ghost activities permanently deleted:', $response);
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('Ghost activities deletion error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['message' => 'Error deleting ghost activities', 'error' => $e->getMessage()], 500);
        }
    }

    // Eliminar permanentemente todas las actividades con action='report' (legacy completo)
    public function deleteAllReportActivities(Request $request)
    {
        try {
            Log::info('Deleting all report activities...');
            
            // Buscar todas las actividades con action='report'
            $allReportActivities = Activity::where('action', 'report')->get();
            
            Log::info('All report activities found:', ['count' => $allReportActivities->count()]);
            
            $deletedCount = 0;
            $deletedActivities = [];
            
            foreach ($allReportActivities as $activity) {
                try {
                    $deletedActivities[] = [
                        'activity_id' => $activity->id,
                        'description' => $activity->description,
                        'action' => $activity->action,
                        'status' => $activity->status
                    ];
                    
                    $activity->delete(); // Eliminación física
                    $deletedCount++;
                    
                    Log::info('Report activity permanently deleted:', [
                        'activity_id' => $activity->id,
                        'description' => $activity->description,
                        'action' => $activity->action,
                        'status' => $activity->status
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error deleting individual report activity:', [
                        'activity_id' => $activity->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            $response = [
                'message' => "Successfully permanently deleted {$deletedCount} report activity(ies)",
                'deleted_count' => $deletedCount,
                'deleted_activities' => $deletedActivities
            ];
            
            Log::info('All report activities permanently deleted:', $response);
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('All report activities deletion error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['message' => 'Error deleting all report activities', 'error' => $e->getMessage()], 500);
        }
    }

    // Eliminar permanentemente todas las actividades legacy (action='report' con status=NULL)
    public function deleteLegacyActivities(Request $request)
    {
        try {
            Log::info('Starting legacy activities deletion...');
            
            // Buscar todas las actividades legacy - action='report' con status='report'
            $legacyActivities = Activity::where('action', 'report')
                ->where('status', 'report')
                ->get();
            
            Log::info('Found legacy activities:', ['count' => $legacyActivities->count()]);
            
            $deletedCount = 0;
            $deletedActivities = [];
            
            foreach ($legacyActivities as $activity) {
                try {
                    // Eliminar permanentemente del database
                    $deletedActivities[] = [
                        'activity_id' => $activity->id,
                        'description' => $activity->description,
                        'action' => $activity->action,
                        'status' => $activity->status
                    ];
                    
                    $activity->delete(); // Eliminación física
                    $deletedCount++;
                    
                    Log::info('Legacy activity permanently deleted:', [
                        'activity_id' => $activity->id,
                        'description' => $activity->description,
                        'action' => $activity->action
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error deleting individual activity:', [
                        'activity_id' => $activity->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            $response = [
                'message' => "Successfully permanently deleted {$deletedCount} legacy activity(ies)",
                'deleted_count' => $deletedCount,
                'deleted_activities' => $deletedActivities
            ];
            
            Log::info('Legacy activities permanently deleted:', $response);
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('Legacy activities deletion error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['message' => 'Error deleting legacy activities', 'error' => $e->getMessage()], 500);
        }
    }
}
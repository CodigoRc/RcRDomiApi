<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceStats;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;

class RdomiServiceStatsController extends Controller
{
    /**
     * Incrementar el contador de visualizaciones (+1) para un servicio específico
     */
    public function incrementView(Request $request)
    {
        try {
            $request->validate([
                'service_id' => 'required|integer'
            ]);

            $serviceId = $request->input('service_id');
            $clientIp = $request->ip();
            
            // Crear clave única para IP + servicio específico
            $rateLimitKey = "stats_increment_{$clientIp}_{$serviceId}";
            
            // Verificar si la IP ya incrementó este servicio en los últimos 9 minutos
            if (Cache::has($rateLimitKey)) {
                $lastIncrement = Cache::get($rateLimitKey);
                $timeRemaining = 540 - (now()->timestamp - $lastIncrement); // 540 segundos = 9 minutos
                
                return response()->json([
                    'message' => 'Request processed successfully',
                    'service_id' => $serviceId,
                    'code' => 200
                ]);
            }

            // Buscar o crear el registro de estadísticas para este servicio
            $stats = ServiceStats::firstOrCreate(
                [
                    'service_id' => $serviceId,
                    'type' => 'view'
                ],
                [
                    'count' => 0
                ]
            );

            // Incrementar el contador
            $stats->increment('count');
            
            // Guardar timestamp en cache por 9 minutos (540 segundos)
            Cache::put($rateLimitKey, now()->timestamp, 540);

            return response()->json([
                'message' => 'Request processed successfully',
                'service_id' => $serviceId,
                'code' => 200
            ]);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Database error occurred',
                'error' => $e->getMessage(),
                'code' => 500
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * Obtener las estadísticas de un servicio específico
     */
    public function getStats(Request $request)
    {
        try {
            $request->validate([
                'service_id' => 'required|integer'
            ]);

            $serviceId = $request->input('service_id');

            $stats = ServiceStats::where('service_id', $serviceId)
                                 ->where('type', 'view')
                                 ->first();

            $count = $stats ? $stats->count : 0;

            return response()->json([
                'service_id' => $serviceId,
                'count' => $count,
                'code' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de múltiples servicios
     */
    public function getMultipleStats(Request $request)
    {
        try {
            $request->validate([
                'service_ids' => 'required|array',
                'service_ids.*' => 'integer'
            ]);

            $serviceIds = $request->input('service_ids');

            $stats = ServiceStats::whereIn('service_id', $serviceIds)
                                 ->where('type', 'view')
                                 ->get()
                                 ->keyBy('service_id');

            $result = [];
            foreach ($serviceIds as $serviceId) {
                $result[] = [
                    'service_id' => $serviceId,
                    'count' => isset($stats[$serviceId]) ? $stats[$serviceId]->count : 0
                ];
            }

            return response()->json([
                'data' => $result,
                'code' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }
} 
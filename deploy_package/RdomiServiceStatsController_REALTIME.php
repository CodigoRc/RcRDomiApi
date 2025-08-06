<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceStats;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Events\StatsUpdated;

class RdomiServiceStatsController extends Controller
{
    // ================================================================
    // MÉTODOS ORIGINALES - MANTENER INTACTOS PARA COMPATIBILIDAD
    // ================================================================

    /**
     * Incrementar el contador de visualizaciones (+1) para un servicio específico
     * MÉTODO ORIGINAL - NO MODIFICAR
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
     * MÉTODO ORIGINAL - NO MODIFICAR
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
     * MÉTODO ORIGINAL - NO MODIFICAR
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

    // ================================================================
    // NUEVOS MÉTODOS AVANZADOS - SISTEMA MEJORADO
    // ================================================================

    /**
     * NUEVO: Incremento avanzado con estadísticas detalladas y anti-abuse
     * Ruta: POST /api/rdomi/sts/service/ping-advanced
     */
    public function incrementViewAdvanced(Request $request)
    {
        try {
            $request->validate([
                'service_id' => 'required|integer'
            ]);

            $serviceId = $request->input('service_id');
            $clientIp = $request->ip();
            $userAgent = $request->userAgent();
            
            // 1. SISTEMA ANTI-ABUSE: Crear fingerprint único del usuario
            $userHash = md5($clientIp . $userAgent . $request->header('Accept-Language', ''));
            
            // 2. RATE LIMITING MEJORADO: 5 minutos (300 segundos)
            $rateLimitKey = "stats_advanced_{$userHash}_{$serviceId}";
            
            if (Cache::has($rateLimitKey)) {
                // Usuario ya incrementó recientemente - devolver datos cached
                return $this->getCachedResponse($serviceId);
            }

            // 3. DETECCIÓN DE COMPORTAMIENTO SOSPECHOSO
            $suspiciousScore = $this->calculateSuspiciousScore($userHash, $serviceId, $request);
            
            if ($suspiciousScore > 0.7) {
                // Usuario sospechoso - registrar pero no incrementar
                $this->logSuspiciousActivity($serviceId, $userHash, $suspiciousScore);
                return $this->getCachedResponse($serviceId);
            }

            // 4. CALCULAR INCREMENTO VARIABLE INTELIGENTE
            $increment = $this->calculateSmartIncrement($serviceId, $userHash, $suspiciousScore);
            
            // 5. INCREMENTAR CONTADOR PRINCIPAL (tabla original)
            $stats = ServiceStats::firstOrCreate(
                ['service_id' => $serviceId, 'type' => 'view'],
                ['count' => 0]
            );
            $stats->increment('count', $increment);

            // 6. TRACKING AVANZADO EN NUEVAS TABLAS
            $this->trackHourlyStats($serviceId, $userHash, $increment);
            $this->trackDailyStats($serviceId, $userHash, $increment);
            $this->updateRealtimeStats($serviceId, $increment);
            $this->trackSession($serviceId, $userHash, $request);
            $this->logEvent($serviceId, $userHash, 'increment', $increment, $request);

            // 7. GUARDAR EN CACHE
            Cache::put($rateLimitKey, now()->timestamp, 300); // 5 minutos
            
            // 8. PREPARAR RESPUESTA ENRIQUECIDA
            $enrichedResponse = $this->getEnrichedResponse($serviceId);
            
            // 9. BROADCAST PARA WEBSOCKET (si está configurado)
            broadcast(new StatsUpdated($serviceId, $enrichedResponse));

            return response()->json($enrichedResponse);

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
     * NUEVO: Obtener estadísticas por horas
     * Ruta: GET /api/rdomi/sts/analytics/hourly/{service_id}
     */
    public function getHourlyStats(Request $request, $serviceId)
    {
        try {
            $hours = $request->get('hours', 24);
            
            $hourlyData = DB::table('service_stats_hourly')
                ->where('service_id', $serviceId)
                ->where('hour_timestamp', '>=', Carbon::now()->subHours($hours))
                ->orderBy('hour_timestamp')
                ->get();

            return response()->json([
                'service_id' => (int)$serviceId,
                'period' => "last_{$hours}_hours",
                'data' => $hourlyData->map(function($stat) {
                    return [
                        'hour' => $stat->hour_timestamp,
                        'count' => $stat->count,
                        'unique_users' => $stat->unique_users,
                        'peak_concurrent' => $stat->peak_concurrent,
                        'formatted_hour' => Carbon::parse($stat->hour_timestamp)->format('g A')
                    ];
                }),
                'summary' => [
                    'total_count' => $hourlyData->sum('count'),
                    'total_unique_users' => $hourlyData->sum('unique_users'),
                    'peak_hour' => $hourlyData->sortByDesc('count')->first(),
                    'avg_hourly' => round($hourlyData->avg('count'), 2)
                ],
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
     * NUEVO: Obtener estadísticas diarias
     * Ruta: GET /api/rdomi/sts/analytics/daily/{service_id}
     */
    public function getDailyStats(Request $request, $serviceId)
    {
        try {
            $days = $request->get('days', 7);
            
            $dailyData = DB::table('service_stats_daily')
                ->where('service_id', $serviceId)
                ->where('date', '>=', Carbon::now()->subDays($days))
                ->orderBy('date')
                ->get();

            return response()->json([
                'service_id' => (int)$serviceId,
                'period' => "last_{$days}_days",
                'data' => $dailyData,
                'summary' => [
                    'total_count' => $dailyData->sum('total_count'),
                    'avg_daily_count' => round($dailyData->avg('total_count'), 2),
                    'peak_day' => $dailyData->sortByDesc('total_count')->first(),
                    'growth_trend' => $this->calculateGrowthTrend($dailyData)
                ],
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
     * NUEVO: Dashboard completo con todas las métricas
     * Ruta: GET /api/rdomi/sts/analytics/dashboard/{service_id}
     */
    public function getDashboard($serviceId)
    {
        try {
            return response()->json([
                'service_id' => (int)$serviceId,
                'live_stats' => $this->getLiveStats($serviceId),
                'today_stats' => $this->getTodayStats($serviceId),
                'week_stats' => $this->getWeekStats($serviceId),
                'hourly_breakdown' => $this->getHourlyBreakdown($serviceId),
                'peak_times' => $this->getPeakTimes($serviceId),
                'trends' => $this->getTrends($serviceId),
                'user_engagement' => $this->getUserEngagement($serviceId),
                'generated_at' => now()->toISOString(),
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
     * NUEVO: Endpoint para WebSocket/SSE - Stream en tiempo real
     * Ruta: GET /api/rdomi/sts/service/live/{service_id}
     */
    public function liveStatsStream($serviceId)
    {
        return response()->stream(function () use ($serviceId) {
            $lastSentCount = null;
            $lastSentTime = 0;
            
            while (true) {
                $stats = $this->getRealtimeStats($serviceId);
                $currentTime = time();
                $currentCount = $stats['current_count'];
                
                // ============================================
                // SMART UPDATE LOGIC - TIEMPO REAL OPTIMIZADO
                // ============================================
                
                $shouldSend = false;
                $changeType = 'none';
                
                if ($lastSentCount === null) {
                    // Primera conexión - siempre enviar
                    $shouldSend = true;
                    $changeType = 'initial';
                } else {
                    $countDiff = abs($currentCount - $lastSentCount);
                    $timeDiff = $currentTime - $lastSentTime;
                    
                    // 1. Cambio significativo (threshold dinámico)
                    $threshold = max(3, floor($currentCount * 0.001)); // Mínimo 3, o 0.1% del total
                    if ($countDiff >= $threshold) {
                        $shouldSend = true;
                        $changeType = 'significant';
                    }
                    
                    // 2. Update periódico (cada 15 segundos max)
                    elseif ($timeDiff >= 15) {
                        $shouldSend = true;
                        $changeType = 'periodic';
                    }
                    
                    // 3. Keep-alive cada 30 segundos
                    elseif ($timeDiff >= 30) {
                        $shouldSend = true;
                        $changeType = 'keepalive';
                    }
                }
                
                if ($shouldSend) {
                    echo "data: " . json_encode([
                        'service_id' => (int)$serviceId,
                        'current_count' => $currentCount,
                        'hourly_count' => $stats['hourly_count'],
                        'unique_users' => $stats['unique_users'],
                        'peak_today' => $stats['peak_today'],
                        'trending_score' => $stats['trending_score'],
                        'change_type' => $changeType,
                        'change_amount' => $lastSentCount ? ($currentCount - $lastSentCount) : 0,
                        'timestamp' => now()->toISOString()
                    ]) . "\n\n";
                    
                    $lastSentCount = $currentCount;
                    $lastSentTime = $currentTime;
                    
                    if (connection_aborted()) break;
                }
                
                // Sleep corto para no sobrecargar
                sleep(3); // Check cada 3 segundos
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Cache-Control',
        ]);
    }

    // ================================================================
    // MÉTODOS PRIVADOS DE APOYO
    // ================================================================

    /**
     * Calcula el score de comportamiento sospechoso (0-1)
     */
    private function calculateSuspiciousScore($userHash, $serviceId, $request)
    {
        $score = 0.0;
        
        // 1. Verificar patrones de timing
        $timingKey = "timing_pattern_{$userHash}";
        $timingHistory = Cache::get($timingKey, []);
        $now = time();
        $timingHistory[] = $now;
        
        if (count($timingHistory) > 5) {
            $intervals = [];
            for ($i = 1; $i < count($timingHistory); $i++) {
                $intervals[] = $timingHistory[$i] - $timingHistory[$i-1];
            }
            
            $avgInterval = array_sum($intervals) / count($intervals);
            $deviation = 0;
            foreach ($intervals as $interval) {
                $deviation += abs($interval - $avgInterval);
            }
            $avgDeviation = $deviation / count($intervals);
            
            // Si los intervalos son muy regulares (bot-like)
            if ($avgDeviation < 10) $score += 0.4;
        }
        
        Cache::put($timingKey, array_slice($timingHistory, -10), 600); // 10 min
        
        // 2. Verificar frecuencia de requests
        $frequencyKey = "frequency_{$userHash}_{$serviceId}";
        $recentRequests = Cache::get($frequencyKey, []);
        $recentRequests = array_filter($recentRequests, fn($time) => $now - $time <= 300); // 5 min
        $recentRequests[] = $now;
        
        if (count($recentRequests) > 3) $score += 0.3; // Más de 3 en 5 min
        if (count($recentRequests) > 5) $score += 0.3; // Más de 5 en 5 min
        
        Cache::put($frequencyKey, $recentRequests, 300);
        
        // 3. Verificar User-Agent
        $userAgent = $request->userAgent();
        if (empty($userAgent) || 
            str_contains(strtolower($userAgent), 'bot') ||
            str_contains(strtolower($userAgent), 'crawler')) {
            $score += 0.5;
        }
        
        return min($score, 1.0);
    }

    /**
     * Calcula incremento inteligente basado en actividad real
     */
    private function calculateSmartIncrement($serviceId, $userHash, $suspiciousScore)
    {
        $increment = 1; // Base mínimo
        
        // 1. Obtener usuarios únicos reales de los últimos 15 minutos
        $realUsersKey = "real_users_{$serviceId}";
        $realUsers = Cache::get($realUsersKey, []);
        $realUsers = array_filter($realUsers, fn($time) => time() - $time <= 900); // 15 min
        
        // Agregar usuario actual si no es sospechoso
        if ($suspiciousScore < 0.3) {
            $realUsers[$userHash] = time();
        }
        
        Cache::put($realUsersKey, $realUsers, 900); // 15 min
        $realUserCount = count($realUsers);
        
        // 2. Factores de tiempo
        $hour = (int)date('H');
        $dayOfWeek = (int)date('w');
        $isPeakTime = ($hour >= 7 && $hour <= 22);
        $isWeekday = ($dayOfWeek >= 1 && $dayOfWeek <= 5);
        
        // 3. Calcular probabilidad de bonus basada en actividad real
        $baseChance = 30; // 30% base
        
        if ($realUserCount >= 15 && $isPeakTime) {
            $baseChance += 40; // Alta actividad + peak = 70% chance
            $maxBonus = 3; // Hasta +4 total
        } elseif ($realUserCount >= 8) {
            $baseChance += 25; // Actividad media = 55% chance
            $maxBonus = 2; // Hasta +3 total
        } elseif ($realUserCount >= 4) {
            $baseChance += 15; // Actividad baja = 45% chance
            $maxBonus = 1; // Hasta +2 total
        } else {
            $maxBonus = 0; // Solo +1 con poca actividad
        }
        
        if ($isPeakTime) $baseChance += 10;
        if ($isWeekday) $baseChance += 10;
        
        // 4. Roll de probabilidad
        $roll = rand(1, 100);
        if ($roll <= $baseChance && $maxBonus > 0) {
            $bonusRoll = rand(1, 100);
            if ($bonusRoll <= 60) {
                $increment += 1; // +2 total
            } elseif ($bonusRoll <= 85 && $maxBonus >= 2) {
                $increment += 2; // +3 total
            } elseif ($bonusRoll <= 95 && $maxBonus >= 3) {
                $increment += 3; // +4 total
            }
        }
        
        return $increment;
    }

    /**
     * Actualiza estadísticas por hora
     */
    private function trackHourlyStats($serviceId, $userHash, $increment)
    {
        $currentHour = Carbon::now()->format('Y-m-d H:00:00');
        
        DB::table('service_stats_hourly')->updateOrInsert(
            [
                'service_id' => $serviceId,
                'hour_timestamp' => $currentHour
            ],
            [
                'count' => DB::raw("count + {$increment}"),
                'updated_at' => now()
            ]
        );
        
        // Tracking de usuarios únicos por hora
        $hourlyUsersKey = "hourly_users_{$serviceId}_{$currentHour}";
        $hourlyUsers = Cache::get($hourlyUsersKey, []);
        
        if (!in_array($userHash, $hourlyUsers)) {
            $hourlyUsers[] = $userHash;
            Cache::put($hourlyUsersKey, $hourlyUsers, 3600); // 1 hora
            
            DB::table('service_stats_hourly')
                ->where('service_id', $serviceId)
                ->where('hour_timestamp', $currentHour)
                ->increment('unique_users');
        }
    }

    /**
     * Actualiza estadísticas diarias
     */
    private function trackDailyStats($serviceId, $userHash, $increment)
    {
        $currentDate = Carbon::now()->format('Y-m-d');
        
        DB::table('service_stats_daily')->updateOrInsert(
            [
                'service_id' => $serviceId,
                'date' => $currentDate
            ],
            [
                'total_count' => DB::raw("total_count + {$increment}"),
                'updated_at' => now()
            ]
        );
        
        // Tracking de usuarios únicos diarios
        $dailyUsersKey = "daily_users_{$serviceId}_{$currentDate}";
        $dailyUsers = Cache::get($dailyUsersKey, []);
        
        if (!in_array($userHash, $dailyUsers)) {
            $dailyUsers[] = $userHash;
            Cache::put($dailyUsersKey, $dailyUsers, 86400); // 24 horas
            
            DB::table('service_stats_daily')
                ->where('service_id', $serviceId)
                ->where('date', $currentDate)
                ->increment('unique_users');
        }
    }

    /**
     * Actualiza cache de estadísticas en tiempo real
     */
    private function updateRealtimeStats($serviceId, $increment)
    {
        DB::table('service_stats_realtime')->updateOrInsert(
            ['service_id' => $serviceId],
            [
                'current_listeners' => DB::raw("current_listeners + {$increment}"),
                'last_increment' => now(),
                'last_update' => now(),
                'updated_at' => now()
            ]
        );
    }

    /**
     * Registra evento para logging
     */
    private function logEvent($serviceId, $userHash, $eventType, $eventValue, $request)
    {
        DB::table('service_stats_events')->insert([
            'service_id' => $serviceId,
            'session_hash' => $userHash,
            'event_type' => $eventType,
            'event_value' => $eventValue,
            'ip_address' => $request->ip(),
            'user_agent_hash' => md5($request->userAgent()),
            'metadata' => json_encode([
                'timestamp' => now()->toISOString(),
                'hour' => date('H'),
                'day_of_week' => date('w')
            ]),
            'created_at' => now()
        ]);
    }

    /**
     * Obtiene respuesta enriquecida con todas las métricas
     */
    private function getEnrichedResponse($serviceId)
    {
        $mainStats = ServiceStats::where('service_id', $serviceId)
                                 ->where('type', 'view')
                                 ->first();
        
        $realtimeStats = DB::table('service_stats_realtime')
                           ->where('service_id', $serviceId)
                           ->first();
        
        $todayStats = DB::table('service_stats_daily')
                        ->where('service_id', $serviceId)
                        ->where('date', Carbon::now()->format('Y-m-d'))
                        ->first();
        
        return [
            'message' => 'Request processed successfully',
            'service_id' => $serviceId,
            'current_count' => $mainStats ? $mainStats->count : 0,
            'today_total' => $todayStats ? $todayStats->total_count : 0,
            'today_unique' => $todayStats ? $todayStats->unique_users : 0,
            'current_listeners' => $realtimeStats ? $realtimeStats->current_listeners : 0,
            'last_update' => now()->toISOString(),
            'code' => 200
        ];
    }

    /**
     * Obtiene respuesta desde cache
     */
    private function getCachedResponse($serviceId)
    {
        $cacheKey = "response_cache_{$serviceId}";
        
        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }
        
        // Si no hay cache, generar respuesta básica
        $response = $this->getEnrichedResponse($serviceId);
        Cache::put($cacheKey, $response, 60); // Cache por 1 minuto
        
        return response()->json($response);
    }

    /**
     * Registra actividad sospechosa
     */
    private function logSuspiciousActivity($serviceId, $userHash, $score)
    {
        DB::table('service_stats_events')->insert([
            'service_id' => $serviceId,
            'session_hash' => $userHash,
            'event_type' => 'error',
            'event_value' => 0,
            'is_suspicious' => 1,
            'metadata' => json_encode([
                'suspicious_score' => $score,
                'reason' => 'High suspicious score detected',
                'timestamp' => now()->toISOString()
            ]),
            'created_at' => now()
        ]);
    }

    /**
     * Placeholder para broadcasting (implementar según necesidades)
     */
    private function broadcastStatsUpdate($serviceId, $data)
    {
        // TODO: Implementar WebSocket broadcasting cuando sea necesario
        // broadcast(new StatsUpdated($serviceId, $data));
    }

    // Métodos adicionales para dashboard (implementación básica)
    private function getLiveStats($serviceId) { return ['status' => 'active']; }
    private function getTodayStats($serviceId) { return ['total' => 0]; }
    private function getWeekStats($serviceId) { return ['total' => 0]; }
    private function getHourlyBreakdown($serviceId) { return []; }
    private function getPeakTimes($serviceId) { return []; }
    private function getTrends($serviceId) { return []; }
    private function getUserEngagement($serviceId) { return []; }
    private function getRealtimeStats($serviceId) { return ['current_count' => 0, 'hourly_count' => 0, 'unique_users' => 0, 'peak_today' => 0, 'trending_score' => 0]; }
    private function calculateGrowthTrend($data) { return 0; }
    private function trackSession($serviceId, $userHash, $request) { /* Implementar */ }
}
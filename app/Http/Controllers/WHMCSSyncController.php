<?php

namespace App\Http\Controllers;

use App\Exceptions\WHMCSException;
use App\Services\WHMCS\WHMCSApiService;
use App\Models\WhmcsSyncMap;
use App\Models\WhmcsSyncLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WHMCSSyncController extends Controller
{
    protected $whmcsApi;

    public function __construct(WHMCSApiService $whmcsApi)
    {
        $this->whmcsApi = $whmcsApi;
    }

    /**
     * Test WHMCS connection
     * GET /api/whmcs/sync/test
     */
    public function testConnection(): JsonResponse
    {
        try {
            $result = $this->whmcsApi->testConnection();
            
            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get sync logs
     * POST /api/whmcs/sync/logs
     */
    public function getSyncLogs(Request $request): JsonResponse
    {
        try {
            $query = WhmcsSyncLog::query();

            // Filters
            if ($request->has('entity_type')) {
                $query->byEntityType($request->input('entity_type'));
            }

            if ($request->has('operation')) {
                $query->byOperation($request->input('operation'));
            }

            if ($request->has('status')) {
                $query->where('status', $request->input('status'));
            }

            if ($request->has('laravel_id')) {
                $query->where('laravel_id', $request->input('laravel_id'));
            }

            if ($request->has('whmcs_id')) {
                $query->where('whmcs_id', $request->input('whmcs_id'));
            }

            if ($request->has('user_id')) {
                $query->byUser($request->input('user_id'));
            }

            if ($request->has('days')) {
                $query->recent($request->input('days'));
            }

            // Pagination
            $limit = $request->input('limit', 50);
            $offset = $request->input('offset', 0);

            $total = $query->count();
            $logs = $query->orderBy('created_at', 'desc')
                ->skip($offset)
                ->take($limit)
                ->get();

            return response()->json([
                'success' => true,
                'logs' => $logs,
                'total' => $total,
                'offset' => $offset,
                'limit' => $limit,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get sync map
     * POST /api/whmcs/sync/map
     */
    public function getSyncMap(Request $request): JsonResponse
    {
        try {
            $query = WhmcsSyncMap::query();

            // Filters
            if ($request->has('entity_type')) {
                $query->byEntityType($request->input('entity_type'));
            }

            if ($request->has('sync_status')) {
                $query->where('sync_status', $request->input('sync_status'));
            }

            if ($request->has('laravel_id')) {
                $query->where('laravel_id', $request->input('laravel_id'));
            }

            if ($request->has('whmcs_id')) {
                $query->where('whmcs_id', $request->input('whmcs_id'));
            }

            // Pagination
            $limit = $request->input('limit', 50);
            $offset = $request->input('offset', 0);

            $total = $query->count();
            $maps = $query->with('logs')
                ->orderBy('last_synced_at', 'desc')
                ->skip($offset)
                ->take($limit)
                ->get();

            return response()->json([
                'success' => true,
                'maps' => $maps,
                'total' => $total,
                'offset' => $offset,
                'limit' => $limit,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Unlink entity from WHMCS
     * POST /api/whmcs/sync/unlink
     */
    public function unlinkEntity(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'entity_type' => 'required|string',
                'laravel_id' => 'required|integer',
            ]);

            $syncMap = WhmcsSyncMap::findByLaravelEntity(
                $request->input('entity_type'),
                $request->input('laravel_id')
            );

            if (!$syncMap) {
                return response()->json([
                    'success' => false,
                    'error' => 'Entity is not synced with WHMCS',
                ], 404);
            }

            $whmcsId = $syncMap->whmcs_id;
            $syncMap->unlink();

            // Log the unlink operation
            WhmcsSyncLog::logSuccess(
                $request->input('entity_type'),
                'delete',
                $request->input('laravel_id'),
                $whmcsId,
                null,
                ['unlinked' => true]
            );

            return response()->json([
                'success' => true,
                'message' => 'Entity unlinked from WHMCS successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get sync statistics
     * GET /api/whmcs/sync/stats
     */
    public function getSyncStats(): JsonResponse
    {
        try {
            $stats = [
                'total_synced' => WhmcsSyncMap::synced()->count(),
                'total_pending' => WhmcsSyncMap::pending()->count(),
                'total_errors' => WhmcsSyncMap::error()->count(),
                'total_conflicts' => WhmcsSyncMap::conflict()->count(),
                
                'by_entity_type' => WhmcsSyncMap::selectRaw('entity_type, COUNT(*) as count, sync_status')
                    ->groupBy('entity_type', 'sync_status')
                    ->get()
                    ->groupBy('entity_type'),
                
                'recent_logs' => WhmcsSyncLog::recent(7)
                    ->selectRaw('DATE(created_at) as date, status, COUNT(*) as count')
                    ->groupBy('date', 'status')
                    ->orderBy('date', 'desc')
                    ->get(),
                
                'last_sync' => WhmcsSyncMap::orderBy('last_synced_at', 'desc')
                    ->first()
                    ?->last_synced_at,
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get WHMCS configuration info
     * GET /api/whmcs/sync/config
     */
    public function getConfig(): JsonResponse
    {
        try {
            $config = $this->whmcsApi->getConfig();

            return response()->json([
                'success' => true,
                'config' => $config,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear WHMCS cache
     * POST /api/whmcs/sync/clear-cache
     */
    public function clearCache(Request $request): JsonResponse
    {
        try {
            if ($request->has('action') && $request->has('params')) {
                // Clear specific cache
                $this->whmcsApi->clearCache(
                    $request->input('action'),
                    $request->input('params', [])
                );
                $message = 'Specific cache cleared';
            } else {
                // Clear all cache
                $this->whmcsApi->clearAllCache();
                $message = 'All WHMCS cache cleared';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}


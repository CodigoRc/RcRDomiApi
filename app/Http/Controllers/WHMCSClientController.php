<?php

namespace App\Http\Controllers;

use App\Exceptions\WHMCSException;
use App\Services\WHMCS\WHMCSClientService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Client;
use App\Models\WhmcsSyncMap;
use App\Models\WhmcsSyncLog;

class WHMCSClientController extends Controller
{
    protected $whmcsClientService;

    public function __construct(WHMCSClientService $whmcsClientService)
    {
        $this->whmcsClientService = $whmcsClientService;
    }

    /**
     * Push Laravel client to WHMCS
     * POST /api/whmcs/clients/push/{client_id}
     */
    public function pushToWHMCS(Request $request, int $clientId): JsonResponse
    {
        try {
            // Get Laravel client
            $client = Client::findOrFail($clientId);

            $forceCreate = $request->input('force_create', false);
            
            $result = $this->whmcsClientService->pushToWHMCS($client, $forceCreate);

            return response()->json($result);

        } catch (WHMCSException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'whmcs_result' => $e->getWhmcsResult(),
                'whmcs_message' => $e->getWhmcsMessage(),
            ], $e->getCode() ?: 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update client in WHMCS
     * POST /api/whmcs/clients/update/{client_id}
     */
    public function updateInWHMCS(Request $request, int $clientId): JsonResponse
    {
        try {
            $client = Client::findOrFail($clientId);

            $result = $this->whmcsClientService->updateInWHMCS($client);

            return response()->json($result);

        } catch (WHMCSException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'whmcs_result' => $e->getWhmcsResult(),
            ], $e->getCode() ?: 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Pull client from WHMCS
     * POST /api/whmcs/clients/pull/{whmcs_id}
     */
    public function pullFromWHMCS(Request $request, int $whmcsId): JsonResponse
    {
        try {
            $laravelId = $request->input('laravel_id');

            $result = $this->whmcsClientService->pullFromWHMCS($whmcsId, $laravelId);

            return response()->json($result);

        } catch (WHMCSException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'whmcs_result' => $e->getWhmcsResult(),
            ], $e->getCode() ?: 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List WHMCS clients
     * POST /api/whmcs/clients/list
     */
    public function listWHMCSClients(Request $request): JsonResponse
    {
        try {
            $filters = [
                'offset' => $request->input('offset', 0),
                'limit' => $request->input('limit', 25),
                'search' => $request->input('search'),
            ];

            // Remove null values
            $filters = array_filter($filters, fn($value) => !is_null($value));

            $result = $this->whmcsClientService->listClients($filters);

            return response()->json($result);

        } catch (WHMCSException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get WHMCS client details
     * POST /api/whmcs/clients/get/{whmcs_id}
     */
    public function getWHMCSClient(int $whmcsId): JsonResponse
    {
        try {
            $result = $this->whmcsClientService->getClient($whmcsId);

            return response()->json($result);

        } catch (WHMCSException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if client is synced with WHMCS
     * POST /api/whmcs/clients/check/{client_id}
     */
    public function checkSync(int $clientId): JsonResponse
    {
        try {
            $result = $this->whmcsClientService->checkSync($clientId);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete client from WHMCS (use with caution!)
     * POST /api/whmcs/clients/delete/{whmcs_id}
     */
    public function deleteFromWHMCS(Request $request, int $whmcsId): JsonResponse
    {
        try {
            // Require confirmation
            $confirm = $request->input('confirm', false);
            if (!$confirm) {
                return response()->json([
                    'success' => false,
                    'error' => 'Confirmation required. Send { "confirm": true } to proceed.',
                ], 400);
            }

            $result = $this->whmcsClientService->deleteFromWHMCS($whmcsId);

            return response()->json($result);

        } catch (WHMCSException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // ============================================
    // MANUAL LINKING (NO SYNC - READ ONLY)
    // ============================================

    /**
     * Link Laravel client with WHMCS client manually
     * POST /api/clients/{client_id}/link-whmcs
     * Body: { "whmcs_id": 123 }
     */
    public function linkToWHMCS(Request $request, int $clientId): JsonResponse
    {
        try {
            // Validate request
            $request->validate([
                'whmcs_id' => 'required|integer|min:1',
            ]);

            $whmcsId = $request->input('whmcs_id');

            // Check if Laravel client exists
            $client = Client::findOrFail($clientId);

            // Check if already linked
            $existingMap = WhmcsSyncMap::where('entity_type', 'client')
                ->where('laravel_id', $clientId)
                ->first();

            if ($existingMap) {
                return response()->json([
                    'success' => false,
                    'error' => 'Client is already linked to WHMCS',
                    'existing_whmcs_id' => $existingMap->whmcs_id,
                ], 400);
            }

            // Check if WHMCS client exists
            try {
                $whmcsClient = $this->whmcsClientService->getClient($whmcsId);
            } catch (WHMCSException $e) {
                return response()->json([
                    'success' => false,
                    'error' => 'WHMCS client not found',
                    'whmcs_error' => $e->getMessage(),
                ], 404);
            }

            // Create link (no data sync, just save the relationship)
            $syncMap = WhmcsSyncMap::create([
                'entity_type' => 'client',
                'laravel_id' => $clientId,
                'whmcs_id' => $whmcsId,
                'sync_direction' => 'bidirectional',
                'sync_status' => 'synced',
                'last_synced_at' => now(),
                'synced_by' => auth()->id(),
                'metadata' => [
                    'linked_manually' => true,
                    'link_date' => now()->toDateTimeString(),
                    'whmcs_client_name' => $whmcsClient['client']['fullname'] ?? null,
                ],
            ]);

            // Log the operation
            WhmcsSyncLog::logSuccess(
                'client',
                'push',
                $clientId,
                $whmcsId,
                auth()->id(),
                [
                    'action' => 'manual_link',
                    'note' => 'Client linked manually, no data synchronization',
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Client linked to WHMCS successfully',
                'sync_map' => $syncMap,
                'whmcs_client' => [
                    'id' => $whmcsId,
                    'name' => $whmcsClient['client']['fullname'] ?? null,
                    'email' => $whmcsClient['client']['email'] ?? null,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get WHMCS info for a linked Laravel client (read-only)
     * GET /api/clients/{client_id}/whmcs-info
     */
    public function getWHMCSInfo(int $clientId): JsonResponse
    {
        try {
            // Check if client exists
            $client = Client::findOrFail($clientId);

            // Check if linked to WHMCS
            $syncMap = WhmcsSyncMap::where('entity_type', 'client')
                ->where('laravel_id', $clientId)
                ->first();

            if (!$syncMap) {
                return response()->json([
                    'success' => false,
                    'linked' => false,
                    'message' => 'Client is not linked to WHMCS',
                ], 404);
            }

            // Get WHMCS client data
            try {
                $whmcsData = $this->whmcsClientService->getClient($syncMap->whmcs_id);

                return response()->json([
                    'success' => true,
                    'linked' => true,
                    'whmcs_id' => $syncMap->whmcs_id,
                    'laravel_id' => $clientId,
                    'linked_at' => $syncMap->created_at,
                    'last_synced_at' => $syncMap->last_synced_at,
                    'whmcs_data' => $whmcsData,
                ]);

            } catch (WHMCSException $e) {
                return response()->json([
                    'success' => false,
                    'linked' => true,
                    'error' => 'WHMCS client not found or API error',
                    'whmcs_error' => $e->getMessage(),
                    'whmcs_id' => $syncMap->whmcs_id,
                ], 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get WHMCS products for a linked client
     * GET /api/clients/{client_id}/whmcs-products
     */
    public function getWHMCSProducts(int $clientId): JsonResponse
    {
        try {
            // Check if linked to WHMCS
            $syncMap = WhmcsSyncMap::where('entity_type', 'client')
                ->where('laravel_id', $clientId)
                ->first();

            if (!$syncMap) {
                return response()->json([
                    'success' => false,
                    'error' => 'Client is not linked to WHMCS',
                ], 404);
            }

            // Get products from WHMCS
            try {
                $productsData = $this->whmcsClientService->getClientProducts($syncMap->whmcs_id);

                return response()->json($productsData);

            } catch (WHMCSException $e) {
                return response()->json([
                    'success' => false,
                    'error' => 'Error fetching products from WHMCS',
                    'whmcs_error' => $e->getMessage(),
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get WHMCS invoices for a linked client
     * GET /api/clients/{client_id}/whmcs-invoices
     */
    public function getWHMCSInvoices(Request $request, int $clientId): JsonResponse
    {
        try {
            // Check if linked to WHMCS
            $syncMap = WhmcsSyncMap::where('entity_type', 'client')
                ->where('laravel_id', $clientId)
                ->first();

            if (!$syncMap) {
                return response()->json([
                    'success' => false,
                    'error' => 'Client is not linked to WHMCS',
                ], 404);
            }

            // Get invoices from WHMCS
            try {
                $filters = [
                    'offset' => $request->input('offset', 0),
                    'limit' => $request->input('limit', 25),
                    'status' => $request->input('status'), // Paid, Unpaid, etc.
                ];

                $invoicesData = $this->whmcsClientService->getClientInvoices($syncMap->whmcs_id, $filters);

                return response()->json($invoicesData);

            } catch (WHMCSException $e) {
                return response()->json([
                    'success' => false,
                    'error' => 'Error fetching invoices from WHMCS',
                    'whmcs_error' => $e->getMessage(),
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get WHMCS domains for a linked client
     * GET /api/clients/{client_id}/whmcs-domains
     */
    public function getWHMCSDomains(Request $request, int $clientId): JsonResponse
    {
        try {
            // Check if linked to WHMCS
            $syncMap = WhmcsSyncMap::where('entity_type', 'client')
                ->where('laravel_id', $clientId)
                ->first();

            if (!$syncMap) {
                return response()->json([
                    'success' => false,
                    'error' => 'Client is not linked to WHMCS',
                ], 404);
            }

            // Get domains from WHMCS
            try {
                $filters = [
                    'offset' => $request->input('offset', 0),
                    'limit' => $request->input('limit', 25),
                ];

                $domainsData = $this->whmcsClientService->getClientDomains($syncMap->whmcs_id, $filters);

                return response()->json($domainsData);

            } catch (WHMCSException $e) {
                return response()->json([
                    'success' => false,
                    'error' => 'Error fetching domains from WHMCS',
                    'whmcs_error' => $e->getMessage(),
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate SSO token for client to access WHMCS without login
     * POST /api/clients/{client_id}/whmcs-sso
     * Body: { "destination": "clientarea.php?action=invoices" } (optional)
     */
    public function generateSsoToken(Request $request, int $clientId): JsonResponse
    {
        try {
            // Check if linked to WHMCS
            $syncMap = WhmcsSyncMap::where('entity_type', 'client')
                ->where('laravel_id', $clientId)
                ->first();

            if (!$syncMap) {
                return response()->json([
                    'success' => false,
                    'error' => 'Client is not linked to WHMCS',
                ], 404);
            }

            // Get destination from request (optional)
            $destination = $request->input('destination', null);

            // Generate SSO token
            try {
                $ssoData = $this->whmcsClientService->createSsoToken($syncMap->whmcs_id, $destination);

                return response()->json($ssoData);

            } catch (WHMCSException $e) {
                return response()->json([
                    'success' => false,
                    'error' => 'Error generating SSO token',
                    'whmcs_error' => $e->getMessage(),
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Unlink Laravel client from WHMCS (does not delete anything)
     * DELETE /api/clients/{client_id}/unlink-whmcs
     */
    public function unlinkFromWHMCS(int $clientId): JsonResponse
    {
        try {
            // Check if client exists
            $client = Client::findOrFail($clientId);

            // Find sync map
            $syncMap = WhmcsSyncMap::where('entity_type', 'client')
                ->where('laravel_id', $clientId)
                ->first();

            if (!$syncMap) {
                return response()->json([
                    'success' => false,
                    'message' => 'Client is not linked to WHMCS',
                ], 404);
            }

            $whmcsId = $syncMap->whmcs_id;

            // Delete the link (soft delete)
            $syncMap->delete();

            // Log the operation
            WhmcsSyncLog::logSuccess(
                'client',
                'delete',
                $clientId,
                $whmcsId,
                auth()->id(),
                [
                    'action' => 'manual_unlink',
                    'note' => 'Client unlinked from WHMCS, no data deleted',
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Client unlinked from WHMCS successfully',
                'note' => 'No data was deleted from either system',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}


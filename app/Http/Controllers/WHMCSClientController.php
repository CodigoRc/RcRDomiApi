<?php

namespace App\Http\Controllers;

use App\Exceptions\WHMCSException;
use App\Services\WHMCS\WHMCSClientService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\RcControlClient; // Adjust to your actual client model

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
            $client = RcControlClient::findOrFail($clientId);

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
            $client = RcControlClient::findOrFail($clientId);

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
}


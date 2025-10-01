<?php

namespace App\Http\Controllers;

use App\Exceptions\WHMCSException;
use App\Services\WHMCS\WHMCSStationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Station;

class WHMCSStationController extends Controller
{
    protected $whmcsStationService;

    public function __construct(WHMCSStationService $whmcsStationService)
    {
        $this->whmcsStationService = $whmcsStationService;
    }

    /**
     * Get available products for client (to link to station)
     * GET /stations/{station_id}/whmcs-available-products
     */
    public function getAvailableProducts(int $stationId): JsonResponse
    {
        try {
            // Get station and its client
            $station = Station::findOrFail($stationId);
            
            if (!$station->client_id) {
                return response()->json([
                    'success' => false,
                    'error' => 'Station does not have a client assigned',
                ], 400);
            }

            $products = $this->whmcsStationService->getClientProducts($station->client_id);

            return response()->json($products);

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
     * Get available domains for client (to link to station)
     * GET /stations/{station_id}/whmcs-available-domains
     */
    public function getAvailableDomains(int $stationId): JsonResponse
    {
        try {
            // Get station and its client
            $station = Station::findOrFail($stationId);
            
            if (!$station->client_id) {
                return response()->json([
                    'success' => false,
                    'error' => 'Station does not have a client assigned',
                ], 400);
            }

            $domains = $this->whmcsStationService->getClientDomains($station->client_id);

            return response()->json($domains);

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
     * Link station to WHMCS product
     * POST /stations/{station_id}/link-whmcs-product
     * Body: { "product_id": 214 }
     */
    public function linkToProduct(Request $request, int $stationId): JsonResponse
    {
        try {
            $request->validate([
                'product_id' => 'required|integer|min:1',
            ]);

            $station = Station::findOrFail($stationId);

            $result = $this->whmcsStationService->linkStationToProduct(
                $stationId,
                $request->input('product_id'),
                $station->client_id
            );

            return response()->json($result);

        } catch (WHMCSException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Link station to WHMCS domain
     * POST /stations/{station_id}/link-whmcs-domain
     * Body: { "domain_id": 123 }
     */
    public function linkToDomain(Request $request, int $stationId): JsonResponse
    {
        try {
            $request->validate([
                'domain_id' => 'required|integer|min:1',
            ]);

            $station = Station::findOrFail($stationId);

            $result = $this->whmcsStationService->linkStationToDomain(
                $stationId,
                $request->input('domain_id'),
                $station->client_id
            );

            return response()->json($result);

        } catch (WHMCSException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get WHMCS product info for station
     * GET /stations/{station_id}/whmcs-product
     */
    public function getProductInfo(int $stationId): JsonResponse
    {
        try {
            $result = $this->whmcsStationService->getStationProductInfo($stationId);

            if (!$result['linked']) {
                return response()->json($result, 404);
            }

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get WHMCS domain info for station
     * GET /stations/{station_id}/whmcs-domain
     */
    public function getDomainInfo(int $stationId): JsonResponse
    {
        try {
            $result = $this->whmcsStationService->getStationDomainInfo($stationId);

            if (!$result['linked']) {
                return response()->json($result, 404);
            }

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Unlink station from product
     * DELETE /stations/{station_id}/unlink-whmcs-product
     */
    public function unlinkFromProduct(int $stationId): JsonResponse
    {
        try {
            $result = $this->whmcsStationService->unlinkStationFromProduct($stationId);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Unlink station from domain
     * DELETE /stations/{station_id}/unlink-whmcs-domain
     */
    public function unlinkFromDomain(int $stationId): JsonResponse
    {
        try {
            $result = $this->whmcsStationService->unlinkStationFromDomain($stationId);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}


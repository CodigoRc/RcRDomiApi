<?php

namespace App\Services\WHMCS;

use App\Exceptions\WHMCSException;
use App\Models\WhmcsSyncMap;
use App\Models\WhmcsSyncLog;

class WHMCSStationService
{
    protected $clientService;
    protected $api;

    public function __construct(WHMCSClientService $clientService, WHMCSApiService $api)
    {
        $this->clientService = $clientService;
        $this->api = $api;
    }

    /**
     * Get available products for a client (to link to station)
     */
    public function getClientProducts(int $clientId): array
    {
        // Get WHMCS ID from client sync map
        $clientSyncMap = WhmcsSyncMap::where('entity_type', 'client')
            ->where('laravel_id', $clientId)
            ->first();

        if (!$clientSyncMap) {
            throw new WHMCSException('Client is not linked to WHMCS');
        }

        return $this->clientService->getClientProducts($clientSyncMap->whmcs_id);
    }

    /**
     * Get available domains for a client (to link to station)
     */
    public function getClientDomains(int $clientId): array
    {
        // Get WHMCS ID from client sync map
        $clientSyncMap = WhmcsSyncMap::where('entity_type', 'client')
            ->where('laravel_id', $clientId)
            ->first();

        if (!$clientSyncMap) {
            throw new WHMCSException('Client is not linked to WHMCS');
        }

        return $this->clientService->getClientDomains($clientSyncMap->whmcs_id);
    }

    /**
     * Link station to WHMCS product
     */
    public function linkStationToProduct(int $stationId, int $productId, int $clientId): array
    {
        // Verify client is linked to WHMCS
        $clientSyncMap = WhmcsSyncMap::where('entity_type', 'client')
            ->where('laravel_id', $clientId)
            ->first();

        if (!$clientSyncMap) {
            throw new WHMCSException('Client is not linked to WHMCS');
        }

        // Check if station is already linked (excluding soft-deleted)
        $existing = WhmcsSyncMap::where('entity_type', 'station_product')
            ->where('laravel_id', $stationId)
            ->first();

        if ($existing) {
            throw new WHMCSException('Station is already linked to a product');
        }

        // Check for soft-deleted link
        $softDeleted = WhmcsSyncMap::withTrashed()
            ->where('entity_type', 'station_product')
            ->where('laravel_id', $stationId)
            ->whereNotNull('deleted_at')
            ->first();

        if ($softDeleted) {
            // Restore and update the soft-deleted link
            $softDeleted->restore();
            $softDeleted->update([
                'whmcs_id' => $productId,
                'sync_direction' => 'bidirectional',
                'sync_status' => 'synced',
                'last_synced_at' => now(),
                'synced_by' => auth()->id(),
                'metadata' => [
                    'client_id' => $clientId,
                    'whmcs_client_id' => $clientSyncMap->whmcs_id,
                    'linked_manually' => true,
                    'restored_at' => now(),
                ],
            ]);

            $syncMap = $softDeleted;
        } else {
            // Create new link
            $syncMap = WhmcsSyncMap::create([
                'entity_type' => 'station_product',
                'laravel_id' => $stationId,
                'whmcs_id' => $productId,
                'sync_direction' => 'bidirectional',
                'sync_status' => 'synced',
                'last_synced_at' => now(),
                'synced_by' => auth()->id(),
                'metadata' => [
                    'client_id' => $clientId,
                    'whmcs_client_id' => $clientSyncMap->whmcs_id,
                    'linked_manually' => true,
                ],
            ]);
        }

        // Log the operation
        WhmcsSyncLog::logSuccess(
            'station_product',
            'push',
            $stationId,
            $productId,
            auth()->id(),
            ['action' => 'manual_link'],
            null
        );

        return [
            'success' => true,
            'message' => 'Station linked to product successfully',
            'sync_map' => $syncMap,
        ];
    }

    /**
     * Link station to WHMCS domain
     */
    public function linkStationToDomain(int $stationId, int $domainId, int $clientId): array
    {
        // Verify client is linked to WHMCS
        $clientSyncMap = WhmcsSyncMap::where('entity_type', 'client')
            ->where('laravel_id', $clientId)
            ->first();

        if (!$clientSyncMap) {
            throw new WHMCSException('Client is not linked to WHMCS');
        }

        // Check if station is already linked (excluding soft-deleted)
        $existing = WhmcsSyncMap::where('entity_type', 'station_domain')
            ->where('laravel_id', $stationId)
            ->first();

        if ($existing) {
            throw new WHMCSException('Station is already linked to a domain');
        }

        // Check for soft-deleted link
        $softDeleted = WhmcsSyncMap::withTrashed()
            ->where('entity_type', 'station_domain')
            ->where('laravel_id', $stationId)
            ->whereNotNull('deleted_at')
            ->first();

        if ($softDeleted) {
            // Restore and update the soft-deleted link
            $softDeleted->restore();
            $softDeleted->update([
                'whmcs_id' => $domainId,
                'sync_direction' => 'bidirectional',
                'sync_status' => 'synced',
                'last_synced_at' => now(),
                'synced_by' => auth()->id(),
                'metadata' => [
                    'client_id' => $clientId,
                    'whmcs_client_id' => $clientSyncMap->whmcs_id,
                    'linked_manually' => true,
                    'restored_at' => now(),
                ],
            ]);

            $syncMap = $softDeleted;
        } else {
            // Create new link
            $syncMap = WhmcsSyncMap::create([
                'entity_type' => 'station_domain',
                'laravel_id' => $stationId,
                'whmcs_id' => $domainId,
                'sync_direction' => 'bidirectional',
                'sync_status' => 'synced',
                'last_synced_at' => now(),
                'synced_by' => auth()->id(),
                'metadata' => [
                    'client_id' => $clientId,
                    'whmcs_client_id' => $clientSyncMap->whmcs_id,
                    'linked_manually' => true,
                ],
            ]);
        }

        // Log the operation
        WhmcsSyncLog::logSuccess(
            'station_domain',
            'push',
            $stationId,
            $domainId,
            auth()->id(),
            ['action' => 'manual_link'],
            null
        );

        return [
            'success' => true,
            'message' => 'Station linked to domain successfully',
            'sync_map' => $syncMap,
        ];
    }

    /**
     * Get product info for linked station
     */
    public function getStationProductInfo(int $stationId): array
    {
        $syncMap = WhmcsSyncMap::where('entity_type', 'station_product')
            ->where('laravel_id', $stationId)
            ->first();

        if (!$syncMap) {
            return [
                'success' => false,
                'linked' => false,
                'message' => 'Station is not linked to a product',
            ];
        }

        // Get product details from WHMCS
        try {
            $response = $this->api->request('GetClientsProducts', [
                'serviceid' => $syncMap->whmcs_id,
            ], true);

            // WHMCS returns the product in different structures depending on single/multiple results
            $product = null;
            if (isset($response['products']['product'][0])) {
                // Multiple products or array format
                $product = $response['products']['product'][0];
            } elseif (isset($response['products']['product'])) {
                // Single product as direct object
                $product = $response['products']['product'];
            }

            // If still no product found, check if it's directly in the response
            if (!$product && isset($response['id'])) {
                $product = $response;
            }

            if (!$product) {
                return [
                    'success' => false,
                    'linked' => true,
                    'error' => 'Product data structure not recognized',
                    'raw_response' => $response,
                ];
            }

            // Normalize product data to ensure it has required fields
            if (!isset($product['id']) && isset($product['serviceid'])) {
                $product['id'] = $product['serviceid'];
            }
            
            // Normalize name field
            if (!isset($product['name'])) {
                if (isset($product['productname'])) {
                    $product['name'] = $product['productname'];
                } elseif (isset($product['product'])) {
                    $product['name'] = $product['product'];
                } elseif (isset($product['groupname'])) {
                    $product['name'] = $product['groupname'];
                }
            }
            
            // Normalize amount field
            if (!isset($product['amount'])) {
                if (isset($product['recurringamount'])) {
                    $product['amount'] = $product['recurringamount'];
                } elseif (isset($product['firstpaymentamount'])) {
                    $product['amount'] = $product['firstpaymentamount'];
                }
            }
            
            // Get related invoices to check for unpaid/overdue ones
            $unpaidInvoices = [];
            try {
                $invoicesResponse = $this->api->request('GetInvoices', [
                    'userid' => $product['clientid'],
                    'serviceid' => $syncMap->whmcs_id,
                    'status' => 'Unpaid',
                    'limitnum' => 10,
                ], true);
                
                if (isset($invoicesResponse['invoices']['invoice'])) {
                    $invoices = $invoicesResponse['invoices']['invoice'];
                    // Ensure it's an array
                    if (!isset($invoices[0])) {
                        $invoices = [$invoices];
                    }
                    $unpaidInvoices = $invoices;
                }
            } catch (\Exception $e) {
                // If invoice check fails, just continue without it
            }
            
            // Add unpaid invoices info to product
            $product['unpaid_invoices'] = $unpaidInvoices;
            $product['has_unpaid_invoices'] = count($unpaidInvoices) > 0;

            return [
                'success' => true,
                'linked' => true,
                'product' => $product,
                'sync_map' => $syncMap,
                'unpaid_invoices_count' => count($unpaidInvoices),
            ];
        } catch (WHMCSException $e) {
            return [
                'success' => false,
                'linked' => true,
                'error' => 'Product not found in WHMCS',
                'whmcs_error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get domain info for linked station
     */
    public function getStationDomainInfo(int $stationId): array
    {
        $syncMap = WhmcsSyncMap::where('entity_type', 'station_domain')
            ->where('laravel_id', $stationId)
            ->first();

        if (!$syncMap) {
            return [
                'success' => false,
                'linked' => false,
                'message' => 'Station is not linked to a domain',
            ];
        }

        // Get domain details from WHMCS
        try {
            $response = $this->api->request('GetClientsDomains', [
                'domainid' => $syncMap->whmcs_id,
            ], true);

            // WHMCS returns the domain in different structures depending on single/multiple results
            $domain = null;
            if (isset($response['domains']['domain'][0])) {
                // Multiple domains or array format
                $domain = $response['domains']['domain'][0];
            } elseif (isset($response['domains']['domain'])) {
                // Single domain as direct object
                $domain = $response['domains']['domain'];
            }

            // If still no domain found, check if it's directly in the response
            if (!$domain && isset($response['id'])) {
                $domain = $response;
            }

            if (!$domain) {
                return [
                    'success' => false,
                    'linked' => true,
                    'error' => 'Domain data structure not recognized',
                    'raw_response' => $response,
                ];
            }

            // Normalize domain data to ensure it has an 'id' field
            if (!isset($domain['id']) && isset($domain['domainid'])) {
                $domain['id'] = $domain['domainid'];
            }

            return [
                'success' => true,
                'linked' => true,
                'domain' => $domain,
                'sync_map' => $syncMap,
            ];
        } catch (WHMCSException $e) {
            return [
                'success' => false,
                'linked' => true,
                'error' => 'Domain not found in WHMCS',
                'whmcs_error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Unlink station from product
     */
    public function unlinkStationFromProduct(int $stationId): array
    {
        $syncMap = WhmcsSyncMap::where('entity_type', 'station_product')
            ->where('laravel_id', $stationId)
            ->first();

        if (!$syncMap) {
            return [
                'success' => false,
                'message' => 'Station is not linked to a product',
            ];
        }

        $productId = $syncMap->whmcs_id;
        $syncMap->delete();

        // Log the operation
        WhmcsSyncLog::logSuccess(
            'station_product',
            'delete',
            $stationId,
            $productId,
            auth()->id(),
            ['action' => 'manual_unlink'],
            null
        );

        return [
            'success' => true,
            'message' => 'Station unlinked from product successfully',
        ];
    }

    /**
     * Unlink station from domain
     */
    public function unlinkStationFromDomain(int $stationId): array
    {
        $syncMap = WhmcsSyncMap::where('entity_type', 'station_domain')
            ->where('laravel_id', $stationId)
            ->first();

        if (!$syncMap) {
            return [
                'success' => false,
                'message' => 'Station is not linked to a domain',
            ];
        }

        $domainId = $syncMap->whmcs_id;
        $syncMap->delete();

        // Log the operation
        WhmcsSyncLog::logSuccess(
            'station_domain',
            'delete',
            $stationId,
            $domainId,
            auth()->id(),
            ['action' => 'manual_unlink'],
            null
        );

        return [
            'success' => true,
            'message' => 'Station unlinked from domain successfully',
        ];
    }
}


<?php

namespace App\Services\WHMCS;

use App\Exceptions\WHMCSException;
use App\Models\WhmcsSyncMap;
use App\Models\WhmcsSyncLog;
use Illuminate\Support\Facades\DB;

class WHMCSClientService
{
    protected $api;

    public function __construct(WHMCSApiService $api)
    {
        $this->api = $api;
    }

    /**
     * Push Laravel client to WHMCS (create or update)
     *
     * @param object $client Laravel client object
     * @param bool $forceCreate Force create new client even if mapped
     * @return array
     */
    public function pushToWHMCS(object $client, bool $forceCreate = false): array
    {
        $startTime = microtime(true);

        try {
            // Check if already synced
            $syncMap = WhmcsSyncMap::findByLaravelEntity('client', $client->id);

            if ($syncMap && !$forceCreate) {
                // Update existing
                return $this->updateInWHMCS($client, $syncMap->whmcs_id);
            }

            // Create new client in WHMCS
            $whmcsData = $this->mapClientToWHMCS($client);
            $response = $this->api->request('AddClient', $whmcsData);

            $whmcsId = $response['clientid'] ?? null;

            if (!$whmcsId) {
                throw new WHMCSException('WHMCS did not return a client ID');
            }

            // Create or update sync mapping
            if ($syncMap) {
                $syncMap->update([
                    'whmcs_id' => $whmcsId,
                    'sync_status' => 'synced',
                    'last_synced_at' => now(),
                ]);
            } else {
                $syncMap = WhmcsSyncMap::create([
                    'entity_type' => 'client',
                    'laravel_id' => $client->id,
                    'whmcs_id' => $whmcsId,
                    'sync_direction' => 'laravel_to_whmcs',
                    'sync_status' => 'synced',
                    'last_synced_at' => now(),
                    'synced_by' => auth()->id(),
                ]);
            }

            $executionTime = (int)((microtime(true) - $startTime) * 1000);

            // Log success
            WhmcsSyncLog::logSuccess(
                'client',
                'push',
                $client->id,
                $whmcsId,
                $whmcsData,
                $response,
                $executionTime
            );

            return [
                'success' => true,
                'message' => 'Client pushed to WHMCS successfully',
                'whmcs_id' => $whmcsId,
                'sync_map_id' => $syncMap->id,
                'operation' => $syncMap->wasRecentlyCreated ? 'created' : 'updated',
            ];

        } catch (WHMCSException $e) {
            $executionTime = (int)((microtime(true) - $startTime) * 1000);

            // Log error
            WhmcsSyncLog::logError(
                'client',
                'push',
                $e->getMessage(),
                $client->id,
                null,
                $this->mapClientToWHMCS($client),
                $e->getWhmcsResponse(),
                $executionTime
            );

            // Mark sync as error if exists
            if (isset($syncMap)) {
                $syncMap->markAsError($e->getMessage());
            }

            throw $e;
        }
    }

    /**
     * Update client in WHMCS
     */
    public function updateInWHMCS(object $client, ?int $whmcsId = null): array
    {
        $startTime = microtime(true);

        try {
            // Get WHMCS ID from sync map if not provided
            if (!$whmcsId) {
                $syncMap = WhmcsSyncMap::findByLaravelEntity('client', $client->id);
                if (!$syncMap) {
                    throw new WHMCSException('Client is not synced with WHMCS');
                }
                $whmcsId = $syncMap->whmcs_id;
            }

            // Prepare update data
            $whmcsData = array_merge(
                ['clientid' => $whmcsId],
                $this->mapClientToWHMCS($client, true)
            );

            $response = $this->api->request('UpdateClient', $whmcsData);

            // Update sync mapping
            $syncMap = WhmcsSyncMap::findByLaravelEntity('client', $client->id);
            if ($syncMap) {
                $syncMap->markAsSynced();
            }

            $executionTime = (int)((microtime(true) - $startTime) * 1000);

            // Log success
            WhmcsSyncLog::logSuccess(
                'client',
                'update_whmcs',
                $client->id,
                $whmcsId,
                $whmcsData,
                $response,
                $executionTime
            );

            return [
                'success' => true,
                'message' => 'Client updated in WHMCS successfully',
                'whmcs_id' => $whmcsId,
            ];

        } catch (WHMCSException $e) {
            $executionTime = (int)((microtime(true) - $startTime) * 1000);

            WhmcsSyncLog::logError(
                'client',
                'update_whmcs',
                $e->getMessage(),
                $client->id,
                $whmcsId,
                null,
                $e->getWhmcsResponse(),
                $executionTime
            );

            throw $e;
        }
    }

    /**
     * Pull client from WHMCS to Laravel
     */
    public function pullFromWHMCS(int $whmcsId, ?int $laravelId = null): array
    {
        $startTime = microtime(true);

        try {
            // Get client from WHMCS
            $response = $this->api->request('GetClientsDetails', [
                'clientid' => $whmcsId,
                'stats' => true,
            ], true);

            if (!isset($response['client'])) {
                throw new WHMCSException('Client not found in WHMCS');
            }

            $whmcsClient = $response['client'];
            $laravelData = $this->mapWHMCSToClient($whmcsClient);

            $executionTime = (int)((microtime(true) - $startTime) * 1000);

            // Log success
            WhmcsSyncLog::logSuccess(
                'client',
                'pull',
                $laravelId,
                $whmcsId,
                ['clientid' => $whmcsId],
                $response,
                $executionTime
            );

            return [
                'success' => true,
                'message' => 'Client pulled from WHMCS successfully',
                'whmcs_client' => $whmcsClient,
                'laravel_data' => $laravelData,
                'needs_mapping' => !$laravelId,
            ];

        } catch (WHMCSException $e) {
            $executionTime = (int)((microtime(true) - $startTime) * 1000);

            WhmcsSyncLog::logError(
                'client',
                'pull',
                $e->getMessage(),
                $laravelId,
                $whmcsId,
                ['clientid' => $whmcsId],
                $e->getWhmcsResponse(),
                $executionTime
            );

            throw $e;
        }
    }

    /**
     * List clients from WHMCS
     */
    public function listClients(array $filters = []): array
    {
        $params = array_merge([
            'limitstart' => $filters['offset'] ?? 0,
            'limitnum' => $filters['limit'] ?? 25,
        ], $filters);

        $response = $this->api->request('GetClients', $params, true);

        return [
            'success' => true,
            'clients' => $response['clients']['client'] ?? [],
            'total' => $response['totalresults'] ?? 0,
            'num_returned' => $response['numreturned'] ?? 0,
        ];
    }

    /**
     * Get client details from WHMCS
     */
    public function getClient(int $whmcsId): array
    {
        $response = $this->api->request('GetClientsDetails', [
            'clientid' => $whmcsId,
            'stats' => true,
        ], true);

        return [
            'success' => true,
            'client' => $response['client'] ?? null,
            'stats' => $response['stats'] ?? null,
        ];
    }

    /**
     * Check if Laravel client is synced with WHMCS
     */
    public function checkSync(int $laravelId): array
    {
        $syncMap = WhmcsSyncMap::findByLaravelEntity('client', $laravelId);

        if (!$syncMap) {
            return [
                'synced' => false,
                'message' => 'Client is not synced with WHMCS',
            ];
        }

        // Optionally verify in WHMCS
        try {
            $whmcsClient = $this->getClient($syncMap->whmcs_id);
            
            return [
                'synced' => true,
                'whmcs_id' => $syncMap->whmcs_id,
                'sync_status' => $syncMap->sync_status,
                'last_synced_at' => $syncMap->last_synced_at,
                'whmcs_client' => $whmcsClient['client'] ?? null,
            ];
        } catch (WHMCSException $e) {
            return [
                'synced' => true,
                'whmcs_id' => $syncMap->whmcs_id,
                'sync_status' => 'error',
                'error' => 'Client mapped but not found in WHMCS',
                'whmcs_error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Map Laravel client to WHMCS format
     */
    protected function mapClientToWHMCS(object $client, bool $isUpdate = false): array
    {
        $mapping = config('whmcs.field_mapping.client', []);
        $defaults = config('whmcs.defaults.client', []);

        $data = [];

        // Map fields according to configuration
        foreach ($mapping as $whmcsField => $laravelField) {
            if (isset($client->$laravelField) && !empty($client->$laravelField)) {
                $data[$whmcsField] = $client->$laravelField;
            }
        }

        // Add defaults only for new clients
        if (!$isUpdate) {
            $data = array_merge($defaults, $data);

            // Required fields for new client
            if (empty($data['firstname'])) {
                $data['firstname'] = $client->client_name ?? 'Unknown';
            }
            if (empty($data['lastname'])) {
                $data['lastname'] = $client->client_lastname ?? 'Client';
            }
            if (empty($data['email'])) {
                $data['email'] = $client->email ?? '';
            }

            // Generate password for new clients
            $data['password2'] = bin2hex(random_bytes(8));
            $data['noemail'] = true; // Don't send welcome email by default
        }

        // Additional custom mappings
        if (isset($client->country_id)) {
            $data['country'] = $this->getCountryCode($client->country_id);
        }

        return $data;
    }

    /**
     * Map WHMCS client to Laravel format
     */
    protected function mapWHMCSToClient(array $whmcsClient): array
    {
        return [
            'client_name' => $whmcsClient['firstname'] ?? '',
            'client_lastname' => $whmcsClient['lastname'] ?? '',
            'email' => $whmcsClient['email'] ?? '',
            'phone' => $whmcsClient['phonenumber'] ?? '',
            'address' => $whmcsClient['address1'] ?? '',
            'city' => $whmcsClient['city'] ?? '',
            'state' => $whmcsClient['state'] ?? '',
            'postal_code' => $whmcsClient['postcode'] ?? '',
            'country_code' => $whmcsClient['country'] ?? '',
            'company_name' => $whmcsClient['companyname'] ?? '',
            'notes' => $whmcsClient['notes'] ?? '',
        ];
    }

    /**
     * Get country code from country ID (implement based on your system)
     */
    protected function getCountryCode(?int $countryId): string
    {
        if (!$countryId) {
            return 'US'; // Default
        }

        // TODO: Implement country lookup from your database
        // For now, return default
        return 'US';
    }

    /**
     * Create SSO token for client (allows client to access WHMCS without login)
     */
    public function createSsoToken(int $whmcsId, ?string $destination = null): array
    {
        try {
            $params = [
                'client_id' => $whmcsId,
            ];

            // Add destination if provided
            if (!empty($destination)) {
                $params['destination'] = $destination;
            }

            \Log::info('Creating SSO token for WHMCS client', [
                'whmcs_id' => $whmcsId,
                'destination' => $destination,
                'params' => $params
            ]);

            $response = $this->api->request('CreateSsoToken', $params, false);

            \Log::info('SSO token created successfully', [
                'whmcs_id' => $whmcsId,
                'has_redirect_url' => isset($response['redirect_url']),
                'has_token' => isset($response['access_token'])
            ]);

            return [
                'success' => true,
                'redirect_url' => $response['redirect_url'] ?? null,
                'token' => $response['access_token'] ?? null,
                'whmcs_response' => $response,
            ];

        } catch (WHMCSException $e) {
            \Log::error('Error creating SSO token', [
                'whmcs_id' => $whmcsId,
                'error' => $e->getMessage(),
                'response' => $e->getWhmcsResponse()
            ]);
            throw new WHMCSException('Error creating SSO token: ' . $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Unexpected error creating SSO token', [
                'whmcs_id' => $whmcsId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get client products from WHMCS
     */
    public function getClientProducts(int $whmcsId): array
    {
        $response = $this->api->request('GetClientsProducts', [
            'clientid' => $whmcsId,
            'stats' => true,
        ], true);

        $products = $response['products']['product'] ?? [];
        
        // Ensure it's an array (single product returns object, not array)
        if (!empty($products) && !isset($products[0])) {
            $products = [$products];
        }

        // Process custom fields and normalize data
        foreach ($products as &$product) {
            // Normalize ID field
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
            
            // WHMCS custom fields come as 'customfields' array
            // Convert to easier format
            if (isset($product['customfields']['customfield'])) {
                $customFieldsArray = $product['customfields']['customfield'];
                
                // Ensure it's an array
                if (!isset($customFieldsArray[0])) {
                    $customFieldsArray = [$customFieldsArray];
                }
                
                // Convert to key-value format
                $customFieldsFormatted = [];
                foreach ($customFieldsArray as $field) {
                    $fieldName = strtolower($field['name'] ?? '');
                    $customFieldsFormatted[$fieldName] = $field['value'] ?? '';
                }
                
                $product['customfields'] = $customFieldsFormatted;
            }
        }

        return [
            'success' => true,
            'products' => $products,
            'total' => count($products),
        ];
    }

    /**
     * Get client invoices from WHMCS
     */
    public function getClientInvoices(int $whmcsId, array $filters = []): array
    {
        $params = array_merge([
            'userid' => $whmcsId,
            'limitstart' => $filters['offset'] ?? 0,
            'limitnum' => $filters['limit'] ?? 25,
        ], $filters);

        $response = $this->api->request('GetInvoices', $params, true);

        $invoices = $response['invoices']['invoice'] ?? [];
        
        // Ensure it's an array
        if (!empty($invoices) && !isset($invoices[0])) {
            $invoices = [$invoices];
        }

        return [
            'success' => true,
            'invoices' => $invoices,
            'total' => $response['totalresults'] ?? count($invoices),
        ];
    }

    /**
     * Get client domains from WHMCS
     */
    public function getClientDomains(int $whmcsId, array $filters = []): array
    {
        $params = array_merge([
            'clientid' => $whmcsId,
            'limitstart' => $filters['offset'] ?? 0,
            'limitnum' => $filters['limit'] ?? 25,
        ], $filters);

        $response = $this->api->request('GetClientsDomains', $params, true);

        $domains = $response['domains']['domain'] ?? [];
        
        // Ensure it's an array
        if (!empty($domains) && !isset($domains[0])) {
            $domains = [$domains];
        }

        // Normalize domain data
        foreach ($domains as &$domain) {
            // Normalize ID field
            if (!isset($domain['id']) && isset($domain['domainid'])) {
                $domain['id'] = $domain['domainid'];
            }
            
            // Normalize domain name field
            if (!isset($domain['domain']) && isset($domain['domainname'])) {
                $domain['domain'] = $domain['domainname'];
            }
        }

        return [
            'success' => true,
            'domains' => $domains,
            'total' => $response['totalresults'] ?? count($domains),
        ];
    }

    /**
     * Delete client from WHMCS (careful with this!)
     */
    public function deleteFromWHMCS(int $whmcsId): array
    {
        $startTime = microtime(true);

        try {
            $response = $this->api->request('DeleteClient', [
                'clientid' => $whmcsId,
            ]);

            $executionTime = (int)((microtime(true) - $startTime) * 1000);

            WhmcsSyncLog::logSuccess(
                'client',
                'delete',
                null,
                $whmcsId,
                ['clientid' => $whmcsId],
                $response,
                $executionTime
            );

            return [
                'success' => true,
                'message' => 'Client deleted from WHMCS successfully',
            ];

        } catch (WHMCSException $e) {
            $executionTime = (int)((microtime(true) - $startTime) * 1000);

            WhmcsSyncLog::logError(
                'client',
                'delete',
                $e->getMessage(),
                null,
                $whmcsId,
                ['clientid' => $whmcsId],
                $e->getWhmcsResponse(),
                $executionTime
            );

            throw $e;
        }
    }
}


<?php

namespace App\Services\WHMCS;

use App\Exceptions\WHMCSException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WHMCSApiService
{
    protected $apiUrl;
    protected $apiIdentifier;
    protected $apiSecret;
    protected $timeout;
    protected $cacheEnabled;
    protected $cacheTtl;
    protected $loggingEnabled;

    public function __construct()
    {
        $this->apiUrl = config('whmcs.api_url');
        $this->apiIdentifier = config('whmcs.api_identifier');
        $this->apiSecret = config('whmcs.api_secret');
        $this->timeout = config('whmcs.timeout', 30);
        $this->cacheEnabled = config('whmcs.cache.enabled', true);
        $this->cacheTtl = config('whmcs.cache.ttl', 300);
        $this->loggingEnabled = config('whmcs.logging.enabled', true);
    }

    /**
     * Make API request to WHMCS
     *
     * @param string $action WHMCS API action
     * @param array $params Additional parameters
     * @param bool $useCache Whether to use cache for this request
     * @return array
     * @throws WHMCSException
     */
    public function request(string $action, array $params = [], bool $useCache = false): array
    {
        // Check if WHMCS is enabled
        if (!config('whmcs.enabled', true)) {
            throw new WHMCSException('WHMCS integration is disabled');
        }

        // Validate credentials
        if (empty($this->apiIdentifier) || empty($this->apiSecret)) {
            throw new WHMCSException('WHMCS API credentials not configured');
        }

        $startTime = microtime(true);

        // Build request data
        $requestData = array_merge([
            'action' => $action,
            'identifier' => $this->apiIdentifier,
            'secret' => $this->apiSecret,
            'responsetype' => 'json',
        ], $params);

        // Generate cache key
        $cacheKey = $this->generateCacheKey($action, $params);

        // Try cache first for read operations
        if ($useCache && $this->cacheEnabled && $this->isReadOperation($action)) {
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                $this->log('Cache hit', $action, $params);
                return $cached;
            }
        }

        try {
            // Make HTTP request
            $response = Http::timeout($this->timeout)
                ->asForm()
                ->post($this->getApiEndpoint(), $requestData);

            $executionTime = (int)((microtime(true) - $startTime) * 1000);

            // Check if request was successful
            if (!$response->successful()) {
                $this->logError(
                    $action,
                    "HTTP Error: " . $response->status(),
                    $params,
                    $response->json(),
                    $executionTime
                );

                throw new WHMCSException(
                    "WHMCS API returned HTTP {$response->status()}",
                    $response->status(),
                    null,
                    $response->json()
                );
            }

            $responseData = $response->json();

            // Check WHMCS result
            if (isset($responseData['result']) && $responseData['result'] !== 'success') {
                $errorMessage = $responseData['message'] ?? 'Unknown WHMCS error';
                
                $this->logError(
                    $action,
                    $errorMessage,
                    $params,
                    $responseData,
                    $executionTime
                );

                throw new WHMCSException(
                    "WHMCS Error: {$errorMessage}",
                    0,
                    null,
                    $responseData
                );
            }

            // Log success
            $this->logSuccess($action, $params, $responseData, $executionTime);

            // Cache successful read operations
            if ($useCache && $this->cacheEnabled && $this->isReadOperation($action)) {
                Cache::put($cacheKey, $responseData, $this->cacheTtl);
            }

            return $responseData;

        } catch (WHMCSException $e) {
            throw $e;
        } catch (\Exception $e) {
            $executionTime = (int)((microtime(true) - $startTime) * 1000);
            
            $this->logError(
                $action,
                $e->getMessage(),
                $params,
                null,
                $executionTime
            );

            throw new WHMCSException(
                "WHMCS API connection error: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Get API endpoint URL
     */
    protected function getApiEndpoint(): string
    {
        return rtrim($this->apiUrl, '/') . '/includes/api.php';
    }

    /**
     * Generate cache key for request
     */
    protected function generateCacheKey(string $action, array $params): string
    {
        $paramsHash = md5(serialize($params));
        return "whmcs:{$action}:{$paramsHash}";
    }

    /**
     * Check if action is a read operation (safe to cache)
     */
    protected function isReadOperation(string $action): bool
    {
        $readActions = [
            'GetClients',
            'GetClientsDetails',
            'GetProducts',
            'GetInvoices',
            'GetOrders',
            'GetTickets',
            'GetSupportStatuses',
            'GetCurrencies',
            'GetPaymentMethods',
        ];

        return in_array($action, $readActions) || 
               str_starts_with($action, 'Get');
    }

    /**
     * Clear cache for specific action
     */
    public function clearCache(string $action, array $params = []): void
    {
        if ($this->cacheEnabled) {
            $cacheKey = $this->generateCacheKey($action, $params);
            Cache::forget($cacheKey);
        }
    }

    /**
     * Clear all WHMCS cache
     */
    public function clearAllCache(): void
    {
        if ($this->cacheEnabled) {
            Cache::flush();
            // Or use tags if available: Cache::tags(['whmcs'])->flush();
        }
    }

    /**
     * Test WHMCS API connection
     */
    public function testConnection(): array
    {
        try {
            $startTime = microtime(true);
            $response = $this->request('GetCurrencies', [], false);
            $executionTime = (int)((microtime(true) - $startTime) * 1000);
            
            return [
                'success' => true,
                'message' => 'Connection successful',
                'whmcs_version' => $response['version'] ?? ($response['whmcsVersion'] ?? 'Unknown'),
                'response_time_ms' => $executionTime,
                'currencies_count' => isset($response['currencies']) ? count($response['currencies']['currency'] ?? []) : 0,
            ];
        } catch (WHMCSException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_type' => $this->getErrorType($e),
            ];
        }
    }

    /**
     * Get error type from exception
     */
    protected function getErrorType(WHMCSException $e): string
    {
        if ($e->isAuthError()) {
            return 'authentication';
        }
        if ($e->isConnectionError()) {
            return 'connection';
        }
        return 'general';
    }

    /**
     * Log success
     */
    protected function logSuccess(
        string $action,
        array $params,
        array $response,
        int $executionTime
    ): void {
        if (!$this->loggingEnabled) {
            return;
        }

        if (config('whmcs.logging.log_requests')) {
            Log::channel('daily')->info("WHMCS API Success: {$action}", [
                'action' => $action,
                'params' => $this->sanitizeForLog($params),
                'result' => $response['result'] ?? null,
                'execution_time_ms' => $executionTime,
            ]);
        }
    }

    /**
     * Log error
     */
    protected function logError(
        string $action,
        string $error,
        array $params,
        ?array $response,
        int $executionTime
    ): void {
        if (!$this->loggingEnabled) {
            return;
        }

        Log::channel('daily')->error("WHMCS API Error: {$action}", [
            'action' => $action,
            'error' => $error,
            'params' => $this->sanitizeForLog($params),
            'response' => $this->sanitizeForLog($response ?? []),
            'execution_time_ms' => $executionTime,
        ]);
    }

    /**
     * Log general info
     */
    protected function log(string $message, string $action, array $params): void
    {
        if (!$this->loggingEnabled) {
            return;
        }

        Log::channel('daily')->info("WHMCS: {$message}", [
            'action' => $action,
            'params' => $this->sanitizeForLog($params),
        ]);
    }

    /**
     * Sanitize sensitive data for logging
     */
    protected function sanitizeForLog(array $data): array
    {
        $sensitive = ['password', 'password2', 'secret', 'identifier', 'cardnum', 'cvv'];
        
        foreach ($sensitive as $field) {
            if (isset($data[$field])) {
                $data[$field] = '***REDACTED***';
            }
        }
        
        return $data;
    }

    /**
     * Get API configuration info (for debugging)
     */
    public function getConfig(): array
    {
        return [
            'api_url' => $this->apiUrl,
            'api_identifier' => substr($this->apiIdentifier, 0, 10) . '...',
            'timeout' => $this->timeout,
            'cache_enabled' => $this->cacheEnabled,
            'cache_ttl' => $this->cacheTtl,
            'logging_enabled' => $this->loggingEnabled,
            'enabled' => config('whmcs.enabled', true),
        ];
    }
}


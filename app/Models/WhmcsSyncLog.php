<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhmcsSyncLog extends Model
{
    use HasFactory;

    protected $table = 'whmcs_sync_logs';

    public $timestamps = false; // Only created_at

    protected $fillable = [
        'entity_type',
        'operation',
        'laravel_id',
        'whmcs_id',
        'sync_map_id',
        'status',
        'request_data',
        'response_data',
        'error_message',
        'whmcs_result',
        'ip_address',
        'user_agent',
        'user_id',
        'execution_time_ms',
        'created_at',
    ];

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
        'execution_time_ms' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Boot method - auto-set created_at
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = now();
            
            // Auto-capture request info if available
            if (request()) {
                $model->ip_address = request()->ip();
                $model->user_agent = request()->userAgent();
            }
        });
    }

    /**
     * Get related sync map
     */
    public function syncMap()
    {
        return $this->belongsTo(WhmcsSyncMap::class, 'sync_map_id');
    }

    /**
     * Get the user who performed the operation
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scopes
     */
    public function scopeByEntityType($query, string $type)
    {
        return $query->where('entity_type', $type);
    }

    public function scopeByOperation($query, string $operation)
    {
        return $query->where('operation', $operation);
    }

    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeError($query)
    {
        return $query->where('status', 'error');
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Static method to log an operation
     */
    public static function logOperation(array $data): self
    {
        return self::create($data);
    }

    /**
     * Static method to log success
     */
    public static function logSuccess(
        string $entityType,
        string $operation,
        ?int $laravelId = null,
        ?int $whmcsId = null,
        ?array $requestData = null,
        ?array $responseData = null,
        ?int $executionTime = null
    ): self {
        return self::logOperation([
            'entity_type' => $entityType,
            'operation' => $operation,
            'laravel_id' => $laravelId,
            'whmcs_id' => $whmcsId,
            'status' => 'success',
            'request_data' => $requestData,
            'response_data' => $responseData,
            'execution_time_ms' => $executionTime,
            'whmcs_result' => $responseData['result'] ?? null,
        ]);
    }

    /**
     * Static method to log error
     */
    public static function logError(
        string $entityType,
        string $operation,
        string $errorMessage,
        ?int $laravelId = null,
        ?int $whmcsId = null,
        ?array $requestData = null,
        ?array $responseData = null,
        ?int $executionTime = null
    ): self {
        return self::logOperation([
            'entity_type' => $entityType,
            'operation' => $operation,
            'laravel_id' => $laravelId,
            'whmcs_id' => $whmcsId,
            'status' => 'error',
            'error_message' => $errorMessage,
            'request_data' => $requestData,
            'response_data' => $responseData,
            'execution_time_ms' => $executionTime,
            'whmcs_result' => $responseData['result'] ?? null,
        ]);
    }

    /**
     * Get human-readable status
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'success' => 'Exitoso',
            'error' => 'Error',
            'warning' => 'Advertencia',
            default => 'Desconocido',
        };
    }

    /**
     * Get human-readable operation
     */
    public function getOperationLabelAttribute(): string
    {
        return match($this->operation) {
            'push' => 'Enviar a WHMCS',
            'pull' => 'Traer de WHMCS',
            'update_whmcs' => 'Actualizar en WHMCS',
            'update_laravel' => 'Actualizar en Laravel',
            'delete' => 'Eliminar sincronización',
            'test' => 'Probar conexión',
            'list' => 'Listar',
            'get' => 'Obtener',
            default => ucfirst($this->operation),
        };
    }

    /**
     * Check if operation was successful
     */
    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Check if operation had error
     */
    public function isError(): bool
    {
        return $this->status === 'error';
    }
}


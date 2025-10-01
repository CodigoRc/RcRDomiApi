<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhmcsSyncMap extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'whmcs_sync_map';

    protected $fillable = [
        'entity_type',
        'laravel_id',
        'whmcs_id',
        'sync_direction',
        'sync_status',
        'last_synced_at',
        'last_error_at',
        'metadata',
        'last_error',
        'sync_attempts',
        'synced_by',
    ];

    protected $casts = [
        'metadata' => 'array',
        'last_synced_at' => 'datetime',
        'last_error_at' => 'datetime',
        'sync_attempts' => 'integer',
    ];

    /**
     * Get sync logs for this mapping
     */
    public function logs()
    {
        return $this->hasMany(WhmcsSyncLog::class, 'sync_map_id');
    }

    /**
     * Get the user who synced this entity
     */
    public function syncedBy()
    {
        return $this->belongsTo(User::class, 'synced_by');
    }

    /**
     * Scopes
     */
    public function scopeByEntityType($query, string $type)
    {
        return $query->where('entity_type', $type);
    }

    public function scopeSynced($query)
    {
        return $query->where('sync_status', 'synced');
    }

    public function scopePending($query)
    {
        return $query->where('sync_status', 'pending');
    }

    public function scopeError($query)
    {
        return $query->where('sync_status', 'error');
    }

    public function scopeConflict($query)
    {
        return $query->where('sync_status', 'conflict');
    }

    /**
     * Find mapping by Laravel entity
     */
    public static function findByLaravelEntity(string $entityType, int $laravelId)
    {
        return self::where('entity_type', $entityType)
            ->where('laravel_id', $laravelId)
            ->first();
    }

    /**
     * Find mapping by WHMCS entity
     */
    public static function findByWhmcsEntity(string $entityType, int $whmcsId)
    {
        return self::where('entity_type', $entityType)
            ->where('whmcs_id', $whmcsId)
            ->first();
    }

    /**
     * Check if Laravel entity is synced
     */
    public static function isSynced(string $entityType, int $laravelId): bool
    {
        return self::where('entity_type', $entityType)
            ->where('laravel_id', $laravelId)
            ->where('sync_status', 'synced')
            ->exists();
    }

    /**
     * Mark as synced
     */
    public function markAsSynced(): void
    {
        $this->update([
            'sync_status' => 'synced',
            'last_synced_at' => now(),
            'sync_attempts' => 0,
            'last_error' => null,
        ]);
    }

    /**
     * Mark as error
     */
    public function markAsError(string $error): void
    {
        $this->update([
            'sync_status' => 'error',
            'last_error_at' => now(),
            'last_error' => $error,
            'sync_attempts' => $this->sync_attempts + 1,
        ]);
    }

    /**
     * Mark as pending
     */
    public function markAsPending(): void
    {
        $this->update([
            'sync_status' => 'pending',
        ]);
    }

    /**
     * Unlink (soft delete)
     */
    public function unlink(): void
    {
        $this->update(['sync_status' => 'unlinked']);
        $this->delete();
    }

    /**
     * Get human-readable status
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->sync_status) {
            'synced' => 'Sincronizado',
            'pending' => 'Pendiente',
            'error' => 'Error',
            'conflict' => 'Conflicto',
            'unlinked' => 'Desvinculado',
            default => 'Desconocido',
        };
    }

    /**
     * Get human-readable entity type
     */
    public function getEntityTypeLabelAttribute(): string
    {
        return match($this->entity_type) {
            'client' => 'Cliente',
            'station' => 'Estación',
            'product' => 'Producto',
            'invoice' => 'Factura',
            'order' => 'Orden',
            'ticket' => 'Ticket',
            'domain' => 'Dominio',
            default => ucfirst($this->entity_type),
        };
    }
}


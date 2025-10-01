<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('whmcs_sync_map', function (Blueprint $table) {
            $table->id();
            
            // Entity information
            $table->string('entity_type', 50)->index(); // client, station, product, invoice, ticket, etc.
            $table->unsignedBigInteger('laravel_id')->index(); // ID in Laravel system
            $table->unsignedBigInteger('whmcs_id')->index(); // ID in WHMCS
            
            // Sync information
            $table->enum('sync_direction', [
                'laravel_to_whmcs',
                'whmcs_to_laravel',
                'bidirectional'
            ])->default('laravel_to_whmcs');
            
            $table->enum('sync_status', [
                'synced',
                'pending',
                'error',
                'conflict',
                'unlinked'
            ])->default('synced')->index();
            
            // Timestamps
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamp('last_error_at')->nullable();
            
            // Metadata
            $table->json('metadata')->nullable(); // Extra data, custom mappings, etc.
            $table->text('last_error')->nullable();
            $table->integer('sync_attempts')->default(0);
            
            // User tracking
            $table->unsignedBigInteger('synced_by')->nullable(); // User ID who performed sync
            
            $table->timestamps();
            $table->softDeletes();
            
            // Unique constraint: one Laravel entity can only map to one WHMCS entity
            $table->unique(['entity_type', 'laravel_id'], 'unique_laravel_entity');
            
            // Unique constraint: one WHMCS entity can only map to one Laravel entity
            $table->unique(['entity_type', 'whmcs_id'], 'unique_whmcs_entity');
            
            // Indexes for common queries
            $table->index(['entity_type', 'sync_status']);
            $table->index(['last_synced_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whmcs_sync_map');
    }
};


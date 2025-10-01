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
        Schema::create('whmcs_sync_logs', function (Blueprint $table) {
            $table->id();
            
            // Operation details
            $table->string('entity_type', 50)->index(); // client, station, product, etc.
            $table->enum('operation', [
                'push',           // Laravel -> WHMCS (create)
                'pull',           // WHMCS -> Laravel (import)
                'update_whmcs',   // Update in WHMCS
                'update_laravel', // Update in Laravel
                'delete',         // Delete sync relationship
                'test',           // Test connection
                'list',           // List items
                'get'             // Get single item
            ])->index();
            
            // Entity IDs
            $table->unsignedBigInteger('laravel_id')->nullable()->index();
            $table->unsignedBigInteger('whmcs_id')->nullable()->index();
            $table->unsignedBigInteger('sync_map_id')->nullable()->index(); // Reference to sync_map
            
            // Status
            $table->enum('status', ['success', 'error', 'warning'])->index();
            
            // Request/Response data
            $table->json('request_data')->nullable(); // Data sent to WHMCS
            $table->json('response_data')->nullable(); // Response from WHMCS
            $table->text('error_message')->nullable();
            $table->string('whmcs_result', 50)->nullable(); // WHMCS result code
            
            // Metadata
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index(); // Who performed the action
            
            // Performance tracking
            $table->integer('execution_time_ms')->nullable(); // Time in milliseconds
            
            $table->timestamp('created_at')->index();
            
            // Indexes for common queries
            $table->index(['entity_type', 'operation']);
            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whmcs_sync_logs');
    }
};


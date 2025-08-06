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
        Schema::table('video_streaming', function (Blueprint $table) {
            // Drop existing columns except id and timestamps
            $table->dropColumn([
                'server',
                'app_number',
                'fms_url',
                'stream_name',
                'stream_user',
                'stream_pw',
                'https_port',
                'fms_port',
                'secure_port',
                'dns'
            ]);

            // Add new columns similar to radio_streaming
            $table->integer('server_id')->nullable()->after('station_id');
            $table->string('ip', 45)->nullable()->after('server_id');
            $table->string('host')->nullable()->after('ip');
            $table->integer('port')->nullable()->after('host');
            $table->string('username', 100)->nullable()->after('port');
            $table->string('password', 100)->nullable()->after('username');
            $table->string('stream_password', 100)->nullable()->after('password');
            $table->string('stream_ssl_url')->nullable()->after('stream_password');
            $table->boolean('autodj_enabled')->default(false)->after('stream_ssl_url');
            $table->integer('bitrate_limit')->nullable()->after('autodj_enabled');
            $table->integer('viewer_limit')->nullable()->after('bitrate_limit');
            $table->integer('bandwidth_limit')->nullable()->after('viewer_limit');
            $table->text('script_config')->nullable()->after('bandwidth_limit');

            // Add unique constraint on station_id
            $table->unique('station_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('video_streaming', function (Blueprint $table) {
            // Remove new columns
            $table->dropColumn([
                'server_id',
                'ip',
                'host',
                'port',
                'username',
                'password',
                'stream_password',
                'stream_ssl_url',
                'autodj_enabled',
                'bitrate_limit',
                'viewer_limit',
                'bandwidth_limit',
                'script_config'
            ]);

            // Restore original columns
            $table->integer('server')->nullable();
            $table->integer('app_number')->nullable();
            $table->string('fms_url')->nullable();
            $table->string('stream_name')->nullable();
            $table->string('stream_user')->nullable();
            $table->string('stream_pw')->nullable();
            $table->integer('https_port')->nullable();
            $table->integer('fms_port')->nullable();
            $table->boolean('secure_port')->nullable();
            $table->string('dns')->nullable();

            // Remove unique constraint on station_id
            $table->dropUnique(['station_id']);
        });
    }
}; 
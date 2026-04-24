<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── access_logs 필드 확장 ──
        Schema::table('access_logs', function (Blueprint $table) {
            $table->string('device_id', 100)->nullable()->after('session_id');
            $table->string('guest_id', 100)->nullable()->after('device_id');
            $table->string('device_source', 20)->nullable()->after('device_type'); // web, app, pwa
            $table->string('os_version', 30)->nullable()->after('os');
            $table->string('device_model', 100)->nullable()->after('os_version');
            $table->string('app_version', 30)->nullable()->after('device_model');
            $table->string('build_version', 30)->nullable()->after('app_version');
            $table->string('client_timezone', 50)->nullable()->after('is_logged_in');
            $table->smallInteger('client_timezone_offset')->nullable()->after('client_timezone');
            $table->string('language', 10)->nullable()->after('client_timezone_offset');
            $table->index('device_id');
            $table->index('guest_id');
        });

        // ── 사용자 기기 레지스트리 ──
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_id', 100)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('platform', 20);          // web, android, ios
            $table->string('device_model', 100)->nullable();
            $table->string('os', 50)->nullable();
            $table->string('os_version', 30)->nullable();
            $table->string('browser', 50)->nullable();
            $table->string('app_version', 30)->nullable();
            $table->string('build_version', 30)->nullable();
            $table->string('last_ip', 45)->nullable();
            $table->boolean('push_token_linked')->default(false);
            $table->timestamp('first_seen_at')->useCurrent();
            $table->timestamp('last_seen_at')->useCurrent();
            $table->timestamps();
            $table->index('user_id');
            $table->index('platform');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_devices');

        Schema::table('access_logs', function (Blueprint $table) {
            $table->dropIndex(['device_id']);
            $table->dropIndex(['guest_id']);
            $table->dropColumn([
                'device_id', 'guest_id', 'device_source', 'os_version',
                'device_model', 'app_version', 'build_version',
                'client_timezone', 'client_timezone_offset', 'language',
            ]);
        });
    }
};

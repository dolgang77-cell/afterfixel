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
        Schema::table('users', function (Blueprint $table) {
            $table->json('favorites')->nullable()->after('remember_token');
            $table->json('recent_views')->nullable()->after('favorites');
            $table->json('preferred_genres')->nullable()->after('recent_views');
            $table->json('recent_areas')->nullable()->after('preferred_genres');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['favorites', 'recent_views', 'preferred_genres', 'recent_areas']);
        });
    }
};

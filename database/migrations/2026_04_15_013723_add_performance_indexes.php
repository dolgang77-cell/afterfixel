<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // parties, clubs, community_posts indexes already applied

        Schema::table('nite_notifications', function (Blueprint $table) {
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::table('nite_notifications', function (Blueprint $table) {
            $table->dropIndex(['type']);
        });
    }
};

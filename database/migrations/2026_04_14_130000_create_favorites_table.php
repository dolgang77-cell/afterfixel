<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 100)->index();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('target_type', 30); // club, party, tour
            $table->unsignedBigInteger('target_id');
            $table->timestamps();

            $table->unique(['session_id', 'target_type', 'target_id']);
            $table->index(['target_type', 'target_id']);
        });

        // 기존 favorite_parties 데이터 마이그레이션
        if (Schema::hasTable('favorite_parties')) {
            DB::statement("
                INSERT INTO favorites (session_id, user_id, target_type, target_id, created_at, updated_at)
                SELECT session_id, user_id, 'party', party_id, created_at, updated_at
                FROM favorite_parties
            ");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};

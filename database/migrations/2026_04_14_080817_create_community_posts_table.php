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
        Schema::create('community_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nickname')->default('익명');
            $table->foreignId('club_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['review', 'tip', 'realtime'])->default('review');
            $table->string('title')->nullable();
            $table->text('content');
            $table->json('images')->nullable();
            $table->integer('like_count')->default(0);
            $table->integer('report_count')->default(0);
            $table->boolean('is_hidden')->default(false);
            $table->string('session_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('community_posts');
    }
};

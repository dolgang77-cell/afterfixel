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
        Schema::create('tour_recommendations', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->json('input_condition');        // area, time, genre, budget
            $table->json('ordered_stops');          // [{club_id, arrival, reason, cost, travel_min}, ...]
            $table->integer('total_cost')->default(0);
            $table->integer('total_travel_minutes')->default(0);
            $table->float('probability_score')->default(0);
            $table->float('calorie_score')->default(0);
            $table->text('summary')->nullable();
            $table->integer('like_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_recommendations');
    }
};

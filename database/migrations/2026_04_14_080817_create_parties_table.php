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
        Schema::create('parties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->date('event_date');
            $table->string('start_time', 10)->nullable();
            $table->string('end_time', 10)->nullable();
            $table->text('lineup')->nullable();
            $table->integer('ticket_price_min')->default(0);
            $table->integer('ticket_price_max')->default(0);
            $table->string('genre')->nullable();
            $table->json('tags')->nullable();
            $table->string('booking_link')->nullable();
            $table->string('dress_code')->nullable();
            $table->text('entry_condition')->nullable();
            $table->text('description')->nullable();
            $table->json('images')->nullable();
            $table->string('thumbnail')->nullable();
            $table->enum('status', ['upcoming', 'ongoing', 'ended', 'cancelled'])->default('upcoming');
            $table->integer('view_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parties');
    }
};

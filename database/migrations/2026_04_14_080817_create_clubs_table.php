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
        Schema::create('clubs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('area');
            $table->string('genre');
            $table->string('subgenre')->nullable();
            $table->string('vibe')->nullable();
            $table->string('open_time', 10)->nullable();
            $table->string('close_time', 10)->nullable();
            $table->integer('entry_fee_min')->default(0);
            $table->integer('entry_fee_max')->default(0);
            $table->boolean('foreigner_allowed')->default(true);
            $table->string('dress_code')->nullable();
            $table->text('dress_code_detail')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('instagram')->nullable();
            $table->float('rating_avg')->default(0);
            $table->integer('rating_count')->default(0);
            $table->text('rating_summary')->nullable();
            $table->text('description')->nullable();
            $table->json('images')->nullable();
            $table->string('thumbnail')->nullable();
            $table->json('tags')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('view_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clubs');
    }
};

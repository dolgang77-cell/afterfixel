<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->string('intent_type', 30)->default('question')->after('status');
            $table->unsignedInteger('budget_min')->nullable()->after('party_size');
            $table->unsignedInteger('budget_max')->nullable()->after('budget_min');
            $table->string('visit_time_slot', 50)->nullable()->after('budget_max');
            $table->string('gender_mix', 50)->nullable()->after('visit_time_slot');
            $table->text('special_request')->nullable()->after('gender_mix');

            $table->index('intent_type');
        });
    }

    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropIndex(['intent_type']);
            $table->dropColumn([
                'intent_type',
                'budget_min',
                'budget_max',
                'visit_time_slot',
                'gender_mix',
                'special_request',
            ]);
        });
    }
};

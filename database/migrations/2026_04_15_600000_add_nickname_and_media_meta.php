<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── users에 nickname 추가 ──
        Schema::table('users', function (Blueprint $table) {
            $table->string('nickname', 30)->nullable()->after('name');
        });

        // 기존 사용자에게 name 기반 닉네임 채우기
        DB::table('users')->whereNull('nickname')->update([
            'nickname' => DB::raw("CONCAT(name, '_', id)"),
        ]);

        // unique 제약 추가
        Schema::table('users', function (Blueprint $table) {
            $table->string('nickname', 30)->nullable(false)->change();
            $table->unique('nickname');
        });

        // ── media에 이미지 메타 필드 추가 ──
        Schema::table('media', function (Blueprint $table) {
            $table->string('original_name', 255)->nullable()->after('file_url');
            $table->unsignedInteger('original_size')->nullable()->after('original_name');
            $table->unsignedInteger('optimized_size')->nullable()->after('original_size');
            $table->string('mime_type', 50)->nullable()->after('optimized_size');
            $table->unsignedSmallInteger('width')->nullable()->after('mime_type');
            $table->unsignedSmallInteger('height')->nullable()->after('width');
            $table->string('thumbnail_path', 500)->nullable()->after('height');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn(['original_name', 'original_size', 'optimized_size', 'mime_type', 'width', 'height', 'thumbnail_path']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['nickname']);
            $table->dropColumn('nickname');
        });
    }
};

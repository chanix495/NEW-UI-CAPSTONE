<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Appearance preferences (per user): colour theme and font size. dark_mode already exists. */
    public function up(): void
    {
        Schema::table('owner_settings', function (Blueprint $table) {
            $table->string('color_theme', 20)->default('violet')->after('dark_mode');
            $table->string('font_size', 10)->default('medium')->after('color_theme');
        });
    }

    public function down(): void
    {
        Schema::table('owner_settings', function (Blueprint $table) {
            $table->dropColumn(['color_theme', 'font_size']);
        });
    }
};

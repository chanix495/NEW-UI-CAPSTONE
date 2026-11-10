<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Language & region preferences (per user), backup history, and
     * system-wide key/value settings (backup schedule).
     */
    public function up(): void
    {
        Schema::table('owner_settings', function (Blueprint $table) {
            $table->string('language', 10)->default('fil')->after('dark_mode');
            $table->string('date_format', 10)->default('m/d/Y')->after('language');
            $table->string('currency', 5)->default('PHP')->after('date_format');
            $table->string('timezone', 50)->default('Asia/Manila')->after('currency');
        });

        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('type', 20)->default('manual');     // manual | automatic | pre-restore
            $table->string('status', 20)->default('success');  // success | failed
            $table->text('message')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('app_settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
        Schema::dropIfExists('backups');

        Schema::table('owner_settings', function (Blueprint $table) {
            $table->dropColumn(['language', 'date_format', 'currency', 'timezone']);
        });
    }
};

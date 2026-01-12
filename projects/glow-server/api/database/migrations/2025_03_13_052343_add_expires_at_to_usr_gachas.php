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
        Schema::table('usr_gachas', function (Blueprint $table) {
            $table->timestampTz('expires_at')->nullable()->default(null)->after('daily_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_gachas', function (Blueprint $table) {
            $table->dropColumn('expires_at');
        });
    }
};

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
        Schema::table('usr_pvps', function (Blueprint $table) {
            $table->timestampTz('latest_reset_at')->nullable()->comment('リセット日時')->after('last_played_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_pvps', function (Blueprint $table) {
            $table->dropColumn('latest_reset_at');
        });
    }
};

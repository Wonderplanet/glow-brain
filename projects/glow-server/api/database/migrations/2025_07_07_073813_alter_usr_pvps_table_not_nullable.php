<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('usr_pvps')
            ->whereNull('latest_reset_at')
            ->update(['latest_reset_at' => now()]);

        Schema::table('usr_pvps', function (Blueprint $table) {
            $table->timestampTz('latest_reset_at')->comment('リセット日時')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_pvps', function (Blueprint $table) {
            $table->timestampTz('latest_reset_at')->nullable()->comment('リセット日時')->change();
        });
    }
};

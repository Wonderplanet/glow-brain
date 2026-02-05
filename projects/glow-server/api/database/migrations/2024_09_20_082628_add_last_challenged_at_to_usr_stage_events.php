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
        Schema::table('usr_stage_events', function (Blueprint $table) {
            $table
                ->timestampTz('last_challenged_at')
                ->nullable()
                ->default(null)
                ->comment('最終挑戦日時')
                ->after('latest_reset_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_stage_events', function (Blueprint $table) {
            $table->dropColumn('last_challenged_at');
        });
    }
};

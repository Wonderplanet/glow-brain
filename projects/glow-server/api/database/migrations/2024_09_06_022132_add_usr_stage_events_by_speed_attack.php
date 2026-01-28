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
            $table->integer('reset_clear_time_ms')->unsigned()->nullable()->default(null)->comment('開催期間中のクリアタイム(ミリ秒)')->after('reset_ad_challenge_count');
            $table->timestampTz('latest_event_setting_end_at')->default('2000-01-01 00:00:00')->comment('mst_stage_event_settings.end_at')->after('latest_reset_at');
        });
        Schema::table('usr_stage_events', function (Blueprint $table) {
            $table->integer('clear_time_ms')->unsigned()->nullable()->default(null)->comment('クリアタイム(ミリ秒)')->after('reset_clear_time_ms');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_stage_events', function (Blueprint $table) {
            $table->dropColumn('reset_clear_time_ms');
            $table->dropColumn('clear_time_ms');
            $table->dropColumn('latest_event_setting_end_at');
        });
    }
};

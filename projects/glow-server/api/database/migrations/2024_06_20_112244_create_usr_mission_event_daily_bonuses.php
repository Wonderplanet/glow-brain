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
        Schema::create('usr_mission_event_daily_bonuses', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('usr_user_id')->index()->comment('usr_users.id');
            $table->string('mst_mission_event_daily_bonus_id')->comment('mst_mission_event_daily_bonuses.id');
            $table->integer('status')->unsigned()->comment('0: 未クリア 1: クリア 2: 報酬受取済');
            $table->timestampTz('cleared_at')->nullable()->comment('達成日時');
            $table->timestampTz('received_reward_at')->nullable()->comment('報酬受取日時');
            $table->timestampsTz();

            $table->unique(['usr_user_id', 'mst_mission_event_daily_bonus_id'], 'uk_usr_user_id_mst_mission_event_daily_bonus_id');
            $table->index(['usr_user_id', 'status'], 'idx_usr_user_id_status');
        });

        Schema::create('usr_mission_event_daily_bonus_progresses', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('usr_user_id')->index()->comment('usr_users.id');
            $table->string('mst_mission_event_daily_bonus_schedule_id')->comment('mst_mission_event_daily_bonus_schedules.id');
            $table->integer('progress')->unsigned()->comment('ログイン回数進捗');
            $table->timestampTz('latest_update_at')->nullable()->comment('ログイン更新日時');
            $table->timestampsTz();

            $table->unique(['usr_user_id', 'mst_mission_event_daily_bonus_schedule_id'], 'uk_usr_user_id_event_daily_bonus_schedule_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usr_mission_event_daily_bonuses');
    }
};

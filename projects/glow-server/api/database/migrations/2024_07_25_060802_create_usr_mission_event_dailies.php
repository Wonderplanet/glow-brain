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
        Schema::create('usr_mission_event_dailies', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('mst_mission_event_daily_id', 255)->comment('mst_mission_event_dailies.id');
            $table->tinyInteger('status')->comment('0: 未クリア 1: クリア 2: 報酬受取済');
            $table->timestampTz('cleared_at')->nullable()->comment('達成日時');
            $table->timestampTz('received_reward_at')->nullable()->comment('報酬受取日時');
            $table->timestampTz('latest_update_at')->comment('日跨ぎリセット判定用。ステータス変更をした最終更新日時');
            $table->timestampsTz();
            $table->unique(['usr_user_id', 'mst_mission_event_daily_id'], 'usr_user_id_mst_mission_event_daily_id_unique');
            $table->index(['usr_user_id', 'status'], 'usr_user_id_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usr_mission_event_dailies');
    }
};

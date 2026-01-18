<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mst_mission_event_daily_bonuses', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('mst_mission_event_daily_bonus_schedule_id')->comment('mst_mission_event_daily_bonus_schedules.id');
            $table->unsignedInteger('login_day_count')->nullable(false)->comment('条件とするログイン日数');
            $table->string('mst_mission_reward_group_id')->comment('mst_mission_reward_groups.id');
            $table->unsignedInteger('sort_order')->default(0)->comment('表示順');
            $table->bigInteger('release_key')->default(1);

            $table->unique(['mst_mission_event_daily_bonus_schedule_id', 'login_day_count'], 'uk_schedule_id_login_day_count');
        });

        Schema::create('mst_mission_event_daily_bonus_schedules', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('mst_event_id')->comment('mst_events.id');
            $table->timestampTz('start_at')->comment('開始日時');
            $table->timestampTz('end_at')->comment('終了日時');
            $table->bigInteger('release_key')->default(1);

            $table->index(['mst_event_id'], 'index_mst_event_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_mission_event_daily_bonuses');
        Schema::dropIfExists('mst_mission_event_daily_bonus_schedules');
    }
};

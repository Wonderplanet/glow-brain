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
        Schema::create('mst_mission_events', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->bigInteger('release_key')->default(1);
            $table->string('mst_event_id', 255)->comment('イベントID');
            $table->string('criterion_type', 255)->comment('達成条件タイプ');
            $table->string('criterion_value', 255)->nullable()->comment('達成条件値');
            $table->bigInteger('criterion_count')->unsigned()->comment('達成回数');
            $table->string('unlock_criterion_type', 255)->nullable()->comment('開放条件タイプ');
            $table->string('unlock_criterion_value', 255)->nullable()->comment('開放条件値');
            $table->bigInteger('unlock_criterion_count')->unsigned()->comment('達成回数');
            $table->string('group_key', 255)->nullable()->comment('分類キー');
            $table->string('mst_mission_reward_group_id', 255)->comment('mst_mission_reward_groups.group_id');
            $table->integer('sort_order')->unsigned()->comment('並び順');
            $table->string('destination_scene', 255)->comment('ミッションから遷移する画面');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_mission_events');
    }
};

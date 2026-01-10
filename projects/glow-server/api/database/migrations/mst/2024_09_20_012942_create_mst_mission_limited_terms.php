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
        Schema::create('mst_mission_limited_terms', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->bigInteger('release_key')->default(1);
            $table->string('progress_group_key', 255)->comment('進捗グループ');
            $table->string('criterion_type', 255)->comment('達成条件タイプ');
            $table->string('criterion_value', 255)->nullable()->comment('達成条件値');
            $table->bigInteger('criterion_count')->unsigned()->comment('達成回数');
            $table->enum('mission_category', ['AdventBattle'])->comment('ミッションカテゴリー');
            $table->string('mst_mission_reward_group_id', 255)->comment('mst_mission_reward_groups.group_id');
            $table->integer('sort_order')->unsigned()->comment('並び順');
            $table->string('destination_scene', 255)->comment('ミッションから遷移する画面');
            $table->timestampTz('start_at')->comment('開始日時');
            $table->timestampTz('end_at')->comment('終了日時');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_mission_limited_terms');
    }
};

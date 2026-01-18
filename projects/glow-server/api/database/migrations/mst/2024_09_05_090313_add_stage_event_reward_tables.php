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
        $resourceTypes = [
            'Exp',
            'Coin',
            'FreeDiamond',
            'Item',
            'Emblem',
            'Unit',
        ];
        $rewardCategories = [
            'Always',
            'FirstClear',
        ];
        Schema::rename('mst_stage_reward_groups', 'mst_stage_rewards');
        Schema::table('mst_stage_rewards', function (Blueprint $table) {
            $table->dropIndex('stage_reward_group_index');
            $table->dropColumn('mst_stage_reward_group_id');
            $table->string('mst_stage_id', 255)->comment('mst_stages.id')->after('id');
            $table->index(['mst_stage_id'], 'mst_stage_id_index');
        });
        Schema::table('mst_stages', function (Blueprint $table) {
            $table->dropColumn('mst_stage_reward_group_id');
            $table->dropColumn('reward_amount');
        });
        Schema::create('mst_stage_event_rewards', function (Blueprint $table)use ($resourceTypes, $rewardCategories) {
            $table->string('id', 255)->primary();
            $table->string('mst_stage_id', 255)->comment('mst_stages.id');
            $table->enum('reward_category', $rewardCategories);
            $table->enum('resource_type', $resourceTypes);
            $table->string('resource_id', 255)->nullable();
            $table->integer('resource_amount')->unsigned()->comment('報酬数');
            $table->integer('drop_percentage')->unsigned()->comment('ドロップの確率(パーセント)');
            $table->integer('sort_order')->unsigned()->comment('ソート順序');
            $table->bigInteger('release_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_stage_event_rewards');
        Schema::table('mst_stages', function (Blueprint $table) {
            $table->string('mst_stage_reward_group_id', 255)->comment('mst_stage_reward_groups.id')->after('coin');
        });
        Schema::table('mst_stages', function (Blueprint $table) {
            $table->integer('reward_amount')->comment('報酬数')->after('mst_stage_reward_group_id');
        });
        Schema::table('mst_stage_rewards', function (Blueprint $table) {
            $table->string('mst_stage_reward_group_id', 255)->comment('mst_stage_reward_groups.id')->after('id');
            $table->dropIndex('mst_stage_id_index');
            $table->dropColumn('mst_stage_id');
            $table->index(['stage_reward_group_id'], 'stage_reward_group_index');
        });
        Schema::rename('mst_stage_rewards', 'mst_stage_reward_groups');
    }
};

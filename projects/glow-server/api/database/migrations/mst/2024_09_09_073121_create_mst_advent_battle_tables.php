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
            'Coin',
            'FreeDiamond',
            'Item',
            'Emblem',
        ];

        $rankTypes = [
            'Bronze',
            'Silver',
            'Gold',
            'Master',
        ];

        Schema::create('mst_advent_battles', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->enum('advent_battle_type', ['ScoreChallenge', 'Raid'])->comment('降臨バトルタイプ');
            $table->mediumInteger('time_limit_seconds')->unsigned()->default(0)->comment('制限時間秒');
            $table->string('mst_stage_rule_group_id', 255)->nullable()->default(null)->comment('mst_stage_event_rules.group_id');
            $table->smallInteger('challengeable_count')->unsigned()->default(0)->comment('1日の挑戦可能回数');
            $table->smallInteger('ad_challengeable_count')->unsigned()->default(0)->comment('1日の広告視聴での挑戦可能回数');
            $table->string('display_mst_unit_id1', 255)->nullable()->default(null)->comment('降臨バトルトップ場所1に表示するキャラ');
            $table->string('display_mst_unit_id2', 255)->nullable()->default(null)->comment('降臨バトルトップ場所2に表示するキャラ');
            $table->string('display_mst_unit_id3', 255)->nullable()->default(null)->comment('降臨バトルトップ場所3に表示するキャラ');
            $table->timestamp('start_at')->comment('降臨バトル開始日');
            $table->timestamp('end_at')->comment('降臨バトル終了日');
            $table->bigInteger('release_key')->default(1);
        });

        Schema::create('mst_advent_battle_reward_groups', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('mst_advent_battle_id', 255)->comment('mst_advent_battles.id');
            $table->enum('reward_category', ['ChallengeCount', 'Ranking', 'Rank', 'RaidTotalScore'])->comment('報酬カテゴリー');
            $table->string('condition_value', 255)->comment('報酬条件値');
            $table->bigInteger('release_key')->default(1);
        });

        Schema::create('mst_advent_battle_rewards', function (Blueprint $table) use ($resourceTypes) {
            $table->string('id')->primary();
            $table->string('mst_advent_battle_reward_group_id', 255)->comment('mst_advent_battle_reward_groups.id');
            $table->enum('resource_type', $resourceTypes)->comment('報酬タイプ');
            $table->string('resource_id', 255)->nullable()->comment('報酬ID');
            $table->integer('resource_amount')->unsigned()->comment('報酬数量');
            $table->bigInteger('release_key')->default(1);
        });

        Schema::create('mst_advent_battle_ranks', function (Blueprint $table) use ($rankTypes) {
            $table->string('id')->primary();
            $table->string('mst_advent_battle_id', 255)->comment('mst_advent_battles.id');
            $table->enum('rank_type', $rankTypes)->comment('降臨バトルランクタイプ');
            $table->tinyInteger('rank_level')->unsigned()->comment('ランクレベル');
            $table->bigInteger('required_lower_score')->unsigned()->comment('このランクタイプとレベル到達に必要な最低スコア');
            $table->string('asset_key');
            $table->bigInteger('release_key')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_advent_battle_ranks');
        Schema::dropIfExists('mst_advent_battle_rewards');
        Schema::dropIfExists('mst_advent_battle_reward_groups');
        Schema::dropIfExists('mst_advent_battles');
    }
};

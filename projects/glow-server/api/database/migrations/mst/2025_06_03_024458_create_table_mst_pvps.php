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
        $rankClassTypes = [
            'Bronze',
            'Silver',
            'Gold',
            'Platinum',
        ];

        $pvpRewardCategoryTypes = [
            'Ranking',
            'RankClass',
        ];

        $resourceTypes = [
            'Coin',
            'FreeDiamond',
            'Item',
            'Emblem',
        ];

        $pvpBonusTypes = [
            'ClearTime',
            'WinOverBonus',
            'WinNormalBonus',
            'WinUnderBonus',
        ];

        // mst_pvps.idはデフォルトデータであるid = default_pvp(11文字)を考慮して16文字に設定
        // 上書き設定などは西暦4桁と週番号2桁を使った自動採番IDを使用し、最大8文字を想定している。
        Schema::create('mst_pvps', function (Blueprint $table) use ($rankClassTypes) {
            $table->string('id', 16)->primary()->comment('西暦4桁と週番号2桁を使った自動採番IDを使用');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->string('reward_group_id', 255)->comment('mst_pvp_reward_groups.id');
            $table->enum('ranking_min_pvp_rank_class', $rankClassTypes)->nullable()->comment('ランキングに含む最小PVPランク区分');
            $table->unsignedInteger('max_daily_challenge_count')->default(0)->comment('1日のアイテム消費なし挑戦可能回数');
            $table->unsignedInteger('max_daily_item_challenge_count')->default(0)->comment('1日のアイテム消費あり挑戦可能回数');
            $table->unsignedInteger('item_challenge_cost_amount')->default(0)->comment('アイテム消費あり挑戦時の消費アイテム数');

            $table->comment('PVP情報のマスターテーブル');
        });

        Schema::create('mst_pvps_i18n', function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('id');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->string('mst_pvp_id', 16)->comment('mst_pvps.id');
            $table->enum('language', ['ja'])->default('ja')->comment('言語');
            $table->string('name', 255)->nullable()->comment('PVP名');
            $table->string('description', 255)->default('')->comment('PVP説明');

            $table->unique(['mst_pvp_id', 'language'], 'mst_pvps_i18n_unique');
            $table->comment('PVP情報の多言語対応テーブル');
        });
        
        Schema::create('mst_pvp_reward_groups', function (Blueprint $table) use ($pvpRewardCategoryTypes) {
            $table->string('id', 255)->primary()->comment('id');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->enum('pvp_reward_category', $pvpRewardCategoryTypes)->comment('PVP報酬カテゴリ');
            $table->string('condition_value', 255)->comment('報酬条件値');

            $table->comment('PVP報酬グループのマスターテーブル');
        });
        
        Schema::create('mst_pvp_rewards', function (Blueprint $table) use ($resourceTypes) {
            $table->string('id', 255)->primary()->comment('id');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->string('mst_pvp_reward_group_id', 255)->comment('mst_pvp_reward_groups.id');
            $table->enum('resource_type', $resourceTypes)->comment('報酬タイプ');
            $table->string('resource_id', 255)->nullable()->comment('報酬ID');
            $table->unsignedInteger('resource_amount')->default(0)->comment('報酬数');

            $table->index('mst_pvp_reward_group_id');
            $table->comment('PVP報酬のマスターテーブル');
        });
        
        Schema::create('mst_pvp_ranks', function (Blueprint $table) use ($rankClassTypes) {
            $table->string('id', 255)->primary()->comment('id');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->enum('rank_class_type', $rankClassTypes)->comment('PVPランク区分');
            $table->unsignedInteger('rank_class_level')->default(1)->comment('PVPランクの最小値');
            $table->bigInteger('required_lower_score')->default(1)->comment('PVPランクの最小スコア');
            $table->unsignedInteger('win_add_point')->default(0)->comment('勝利時のスコア加算値');
            $table->integer('lose_sub_point')->default(0)->comment('敗北時のスコア減算値');

            $table->unique(['rank_class_type', 'rank_class_level'], 'mst_pvp_ranks_unique');
            $table->comment('PVPランクのマスターテーブル');
        });
        
        Schema::create('mst_pvp_bonus_points', function (Blueprint $table) use ($pvpBonusTypes) {
            $table->string('id', 255)->primary()->comment('id');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->string('threshold', 255)->comment('しきい値');
            $table->unsignedInteger('bonus_point')->default(0)->comment('ボーナスポイント');
            $table->enum('bonus_type', $pvpBonusTypes)->comment('PVPボーナスタイプ');

            $table->unique(['threshold', 'bonus_type']);
            $table->comment('PVPボーナスポイントのマスターテーブル');
        });
        
        Schema::create('mst_pvp_dummies', function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('id');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->string('mst_dummy_user_id', 255)->comment('mst_dummy_users.id');
            $table->unsignedInteger('score')->default(0)->comment('PVPスコア');

            $table->index('score');
            $table->comment('PVPダミーユーザーのマスターテーブル');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_pvps');

        Schema::dropIfExists('mst_pvps_i18n');

        Schema::dropIfExists('mst_pvp_reward_groups');

        Schema::dropIfExists('mst_pvp_rewards');

        Schema::dropIfExists('mst_pvp_ranks');

        Schema::dropIfExists('mst_pvp_bonus_points');

        Schema::dropIfExists('mst_pvp_dummies');
    }
};

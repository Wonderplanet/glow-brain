<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // 変更前
    // CREATE TABLE `mst_quests` (
    //     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `quest_type` enum('Normal','Event') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'クエストの種類',
    //     `mst_event_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'mst_events.id',
    //     `mst_series_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'mst_series.id',
    //     `sort_order` int NOT NULL DEFAULT '0',
    //     `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `start_date` timestamp NOT NULL,
    //     `end_date` timestamp NOT NULL,
    //     `release_key` bigint NOT NULL DEFAULT '1',
    //     `quest_group` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '同クエストとして表示をまとめるグループ',
    //     `difficulty` enum('Normal','Hard','VeryHard') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Normal' COMMENT '難易度',
    //     PRIMARY KEY (`id`),
    //     KEY `idx_mst_event_id` (`mst_event_id`),
    //     KEY `idx_quest_type` (`quest_type`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // 変更内容
    // mst_questsのquest_typeにEnhanceを追加

    // 新規テーブル追加
//     INDEX	table	column	データ型	NULL許容	デフォルト値	カラムの説明
// PRI	mst_stage_enhance_reward_params	id	varchar(255)	FALSE		ID
// UNIQUE1	mst_stage_enhance_reward_params	min_threshold_score	bigint	FALSE		乗数が適用されるスコアの下限値
// 	mst_stage_enhance_reward_params	reward_amount_multiplier	DECIMAL(5, 2)	FALSE		報酬量の乗数
// 	mst_stage_enhance_reward_params	release_key	bigint	FALSE

    protected $connection = Database::MST_CONNECTION;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE mst_quests MODIFY COLUMN quest_type ENUM('Normal','Event','Enhance') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'クエストの種類'");

        Schema::create('mst_stage_enhance_reward_params', function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('ID');
            $table->bigInteger('min_threshold_score')->unsigned()->comment('乗数が適用されるスコアの下限値');
            $table->decimal('reward_amount_multiplier', 5, 2)->unsigned()->comment('報酬量の乗数');
            $table->bigInteger('release_key')->unsigned()->comment('リリースキー');

            $table->unique(['min_threshold_score'], 'uk_min_threshold_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE mst_quests MODIFY COLUMN quest_type ENUM('Normal','Event') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'クエストの種類'");

        Schema::dropIfExists('mst_stage_enhance_reward_params');
    }
};

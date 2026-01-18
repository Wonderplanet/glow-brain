<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // 変更前
    //CREATE TABLE `mst_stage_rewards` (
    //  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `mst_stage_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_stages.id',
    //  `reward_category` enum('Always','FirstClear') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //  `resource_type` enum('Exp','Coin','FreeDiamond','Item','Emblem','Unit') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '報酬タイプ',
    //  `resource_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //  `resource_amount` int unsigned NOT NULL,
    //  `weight` int unsigned NOT NULL,
    //  `release_key` bigint unsigned NOT NULL DEFAULT '1',
    //  PRIMARY KEY (`id`),
    //  KEY `mst_stage_id_index` (`mst_stage_id`)
    //) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci

    //CREATE TABLE `mst_stage_event_rewards` (
    //  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //  `mst_stage_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_stages.id',
    //  `reward_category` enum('Always','FirstClear') COLLATE utf8mb4_bin NOT NULL,
    //  `resource_type` enum('Exp','Coin','FreeDiamond','Item','Emblem','Unit') COLLATE utf8mb4_bin NOT NULL,
    //  `resource_id` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
    //  `resource_amount` int unsigned NOT NULL COMMENT '報酬数',
    //  `percentage` int unsigned NOT NULL COMMENT 'ドロップの確率(パーセント)',
    //  `sort_order` int unsigned NOT NULL COMMENT 'ソート順序',
    //  `release_key` bigint NOT NULL,
    //  PRIMARY KEY (`id`)
    //) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // mst_stage_rewardsのweightをpercentageとして使用
        Schema::table('mst_stage_rewards', function (Blueprint $table) {
            $table->renameColumn('weight', 'percentage');
            $table->unsignedInteger('sort_order')->after('percentage')->comment('ソート順序');
        });
        $rewardCategories = "'Always', 'FirstClear', 'Random'";
        DB::statement("ALTER TABLE mst_stage_rewards MODIFY COLUMN reward_category enum({$rewardCategories}) NOT NULL COMMENT '報酬カテゴリー'");
        DB::statement("ALTER TABLE mst_stage_event_rewards MODIFY COLUMN reward_category enum({$rewardCategories})  NOT NULL COMMENT '報酬カテゴリー'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $rewardCategories = "'Always', 'FirstClear'";
        DB::statement("ALTER TABLE mst_stage_rewards MODIFY COLUMN reward_category enum({$rewardCategories})  NOT NULL COMMENT '報酬カテゴリー'");
        DB::statement("ALTER TABLE mst_stage_event_rewards MODIFY COLUMN reward_category enum({$rewardCategories}) NOT NULL COMMENT '報酬カテゴリー'");
        Schema::table('mst_stage_rewards', function (Blueprint $table) {
            $table->renameColumn('percentage', 'weight');
            $table->dropColumn('sort_order');
        });
    }
};

<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // 変更前
    //CREATE TABLE `mst_pvp_reward_groups` (
    //  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'id',
    //  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
    //  `reward_category` enum('Ranking','RankClass') COLLATE utf8mb4_bin NOT NULL COMMENT 'PVP報酬カテゴリ',
    //  `condition_value` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '報酬条件値',
    //  `mst_pvp_id` varchar(16) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_pvps.id',
    //  PRIMARY KEY (`id`),
    //  UNIQUE KEY `mst_pvp_reward_groups_unique` (`mst_pvp_id`,`reward_category`,`condition_value`)
    //) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='PVP報酬グループのマスターテーブル'

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE mst_pvp_reward_groups MODIFY reward_category enum("Ranking","RankClass","TotalScore") CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT "PVP報酬カテゴリ";');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE mst_pvp_reward_groups MODIFY reward_category enum("Ranking","RankClass") CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT "PVP報酬カテゴリ";');
    }
};

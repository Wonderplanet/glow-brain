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
    // CREATE TABLE `mst_quests` (
    //     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `quest_type` enum('Normal','Event','Enhance') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'クエストの種類',
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

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // quest_typeにTutorial追加
        DB::statement('ALTER TABLE mst_quests MODIFY COLUMN quest_type enum("Normal","Event","Enhance","Tutorial") CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT "クエストの種類"');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // quest_typeからTutorial削除
        DB::statement('ALTER TABLE mst_quests MODIFY COLUMN quest_type enum("Normal","Event","Enhance") CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT "クエストの種類"');
    }
};

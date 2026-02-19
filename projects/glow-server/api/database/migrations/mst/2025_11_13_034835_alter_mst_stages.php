<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    //変更前
    //CREATE TABLE `mst_stages` (
    //  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
    //  `mst_quest_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'クエストID(mst_quest.id)',
    //  `mst_in_game_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'インゲーム設定ID(mst_in_game.id)',
    //  `stage_number` int NOT NULL DEFAULT '0' COMMENT 'ステージ番号',
    //  `recommended_level` int NOT NULL DEFAULT '1' COMMENT 'おすすめレベル',
    //  `cost_stamina` int unsigned NOT NULL COMMENT '消費スタミナ',
    //  `exp` int unsigned NOT NULL COMMENT '獲得EXP',
    //  `coin` int unsigned NOT NULL COMMENT '獲得コイン',
    //  `mst_artwork_fragment_drop_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'mst_artwork_fragments.drop_group_id',
    //  `prev_mst_stage_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '解放条件のステージID',
    //  `mst_stage_tips_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'tipsID',
    //  `sort_order` int unsigned NOT NULL COMMENT 'ソート順序',
    //  `start_at` timestamp NOT NULL COMMENT 'ステージ公開開始日時',
    //  `end_at` timestamp NOT NULL COMMENT 'ステージ公開終了日時',
    //  `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'アセットキー',
    //  `release_key` bigint unsigned NOT NULL DEFAULT '1' COMMENT 'リリースキー',
    //  PRIMARY KEY (`id`)
    //) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ステージの基本設定'

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mst_stages', function (Blueprint $table) {
            $table->enum('auto_lap_type', ['AfterClear', 'Initial'])->nullable()->comment('スタミナブーストタイプ')->after('mst_stage_tips_group_id');
            $table->unsignedInteger('max_auto_lap_count')->default(1)->comment('最大スタミナブースト周回指定可能数')->after('auto_lap_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_stages', function (Blueprint $table) {
            $table->dropColumn('auto_lap_type');
            $table->dropColumn('max_auto_lap_count');
        });
    }
};

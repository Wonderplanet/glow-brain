<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // 変更前
    //CREATE TABLE `mst_stages` (
    //  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `mst_quest_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `stage_number` int NOT NULL DEFAULT '0',
    //  `cost_stamina` int unsigned NOT NULL,
    //  `exp` int unsigned NOT NULL,
    //  `coin` int unsigned NOT NULL,
    //  `mst_stage_reward_group_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //  `reward_amount` int unsigned NOT NULL,
    //  `mst_artwork_fragment_drop_group_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'mst_artwork_fragments.drop_group_id',
    //  `prev_mst_stage_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //  `mst_stage_tips_group_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //  `sort_order` int unsigned NOT NULL,
    //  `asset_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //  `bgm_asset_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //  `loop_background_asset_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
    //  `player_outpost_asset_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `mst_page_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //  `mst_enemy_outpost_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //  `boss_mst_enemy_stage_parameter_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //  `normal_enemy_hp_coef` decimal(10,2) NOT NULL DEFAULT '1.00',
    //  `normal_enemy_attack_coef` decimal(10,2) NOT NULL DEFAULT '1.00',
    //  `normal_enemy_speed_coef` decimal(10,2) NOT NULL DEFAULT '1.00',
    //  `boss_enemy_hp_coef` decimal(10,2) NOT NULL DEFAULT '1.00',
    //  `boss_enemy_attack_coef` decimal(10,2) NOT NULL DEFAULT '1.00',
    //  `boss_enemy_speed_coef` decimal(10,2) NOT NULL DEFAULT '1.00',
    //  `enemy_sequence_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //  `mst_stage_limit_status_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `release_key` bigint unsigned NOT NULL DEFAULT '1',
    //  PRIMARY KEY (`id`)
    //) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // 変更内容
    // - name: timeLimit
    //   type: int

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mst_stages', function (Blueprint $table) {
            $table->integer('time_limit')->default(0)->after('mst_enemy_outpost_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_stages', function (Blueprint $table) {
            $table->dropColumn('time_limit');
        });
    }
};

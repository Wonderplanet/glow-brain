<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // 変更前
    // CREATE TABLE `mst_in_games` (
    //     `id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //     `release_key` bigint NOT NULL DEFAULT '1',
    //     `mst_auto_player_sequence_id` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    //     `bgm_asset_key` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    //     `loop_background_asset_key` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    //     `player_outpost_asset_key` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    //     `mst_page_id` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    //     `mst_enemy_outpost_id` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    //     `time_limit` int NOT NULL,
    //     `boss_mst_enemy_stage_parameter_id` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    //     `normal_enemy_hp_coef` decimal(10,2) NOT NULL,
    //     `normal_enemy_attack_coef` decimal(10,2) NOT NULL,
    //     `normal_enemy_speed_coef` decimal(10,2) NOT NULL,
    //     `boss_enemy_hp_coef` decimal(10,2) NOT NULL,
    //     `boss_enemy_attack_coef` decimal(10,2) NOT NULL,
    //     `boss_enemy_speed_coef` decimal(10,2) NOT NULL,
    //     PRIMARY KEY (`id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

    // 変更内容
    // time_limit列の後に、mst_defense_target_id varchar(255) default null列を追加

    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::table('mst_in_games', function (Blueprint $table) {
            $table->string('mst_defense_target_id')->nullable()->after('time_limit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_in_games', function (Blueprint $table) {
            $table->dropColumn('mst_defense_target_id');
        });
    }
};

<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // 変更前
    // CREATE TABLE `mst_stages` (
    //  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `mst_quest_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `mst_in_game_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
    //  `stage_number` int NOT NULL DEFAULT '0',
    //  `cost_stamina` int unsigned NOT NULL,
    //  `exp` int unsigned NOT NULL,
    //  `coin` int unsigned NOT NULL,
    //  `mst_artwork_fragment_drop_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'mst_artwork_fragments.drop_group_id',
    //  `prev_mst_stage_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //  `mst_stage_tips_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //  `sort_order` int unsigned NOT NULL,
    //  `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //  `mst_stage_limit_status_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `release_key` bigint unsigned NOT NULL DEFAULT '1',
    //  PRIMARY KEY (`id`)
    //) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // 変更内容
    // mst_auto_player_sequencesテーブルを追加
    // mst_stages.mst_auto_player_sequence_idを追加

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mst_auto_player_sequences', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('sequence_element_id');
            $table->string('sequence_group_id');
            $table->string('condition_type')->default('None');
            $table->string('condition_value');
            $table->string('action_type')->default('None');
            $table->string('action_value');
            $table->string('summon_animation_type')->default('None');
            $table->integer('summon_count')->default(0);
            $table->integer('summon_interval')->default(0);
            $table->integer('action_delay')->default(0);
            $table->float('summon_position')->default(0);
            $table->string('move_start_condition_type')->default('None');
            $table->bigInteger('move_start_condition_value')->default(0);
            $table->tinyInteger('last_boss_trigger')->default(0);
            $table->integer('override_drop_battle_point')->nullable();
            $table->float('enemy_hp_coef')->default(0);
            $table->float('enemy_attack_coef')->default(0);
            $table->float('enemy_speed_coef')->default(0);
            $table->string('deactivation_condition_type')->default('None');
            $table->string('deactivation_condition_value');
        });

        Schema::table('mst_stages', function (Blueprint $table) {
            $table->string('mst_auto_player_sequence_id')->default('')->after('asset_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_auto_player_sequences');

        Schema::table('mst_stages', function (Blueprint $table) {
            $table->dropColumn('mst_auto_player_sequence_id');
        });
    }
};

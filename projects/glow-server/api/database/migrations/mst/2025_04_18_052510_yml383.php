<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // CREATE TABLE `mst_advent_battles` (
    //  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //  `mst_in_game_id` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    //  `asset_key` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    //  `advent_battle_type` enum('ScoreChallenge','Raid') COLLATE utf8mb4_bin NOT NULL COMMENT '降臨バトルタイプ',
    //  `initial_battle_point` int NOT NULL DEFAULT '0',
    //  `mst_stage_rule_group_id` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'mst_stage_event_rules.group_id',
    //  `event_bonus_group_id` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'mst_event_bonus_units.event_bonus_group_id',
    //  `challengeable_count` smallint unsigned NOT NULL DEFAULT '0' COMMENT '1日の挑戦可能回数',
    //  `ad_challengeable_count` smallint unsigned NOT NULL DEFAULT '0' COMMENT '1日の広告視聴での挑戦可能回数',
    //  `display_mst_unit_id1` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT '降臨バトルトップ場所1に表示するキャラ',
    //  `display_mst_unit_id2` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT '降臨バトルトップ場所2に表示するキャラ',
    //  `display_mst_unit_id3` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT '降臨バトルトップ場所3に表示するキャラ',
    //  `exp` int unsigned NOT NULL DEFAULT '0' COMMENT '獲得リーダーEXP',
    //  `coin` int unsigned NOT NULL DEFAULT '0' COMMENT '獲得コイン',
    //  `start_at` timestamp NOT NULL COMMENT '降臨バトル開始日',
    //  `end_at` timestamp NOT NULL COMMENT '降臨バトル終了日',
    //  `release_key` bigint NOT NULL DEFAULT '1',
    //  `score_addition_type` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT 'AllEnemiesAndOutPost',
    //  PRIMARY KEY (`id`)
    //) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

    // CREATE TABLE `mst_attack_elements` (
    //  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `release_key` bigint NOT NULL DEFAULT '1',
    //  `mst_attack_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `sort_order` int NOT NULL,
    //  `attack_delay` int NOT NULL,
    //  `attack_type` enum('None','Direct','Deck') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `range_start_type` enum('Distance','Koma','KomaLine','Page') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `range_start_parameter` double(8,2) NOT NULL,
    //  `range_end_type` enum('Distance','Koma','KomaLine','Page') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `range_end_parameter` double(8,2) NOT NULL,
    //  `max_target_count` int NOT NULL,
    //  `target` enum('Friend','Foe','Self') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `target_type` enum('All','Character','Outpost') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `target_colors` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `target_roles` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `damage_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'None',
    //  `hit_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Normal',
    //  `hit_parameter1` int unsigned NOT NULL DEFAULT '0',
    //  `hit_parameter2` int unsigned NOT NULL DEFAULT '0',
    //  `hit_onomatopoeia_group_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `is_hit_stop` tinyint NOT NULL DEFAULT '0',
    //  `probability` int NOT NULL,
    //  `power_parameter_type` enum('Percentage','Fixed','MaxHpPercentage') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `power_parameter` int NOT NULL,
    //  `effect_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'None',
    //  `effective_count` int NOT NULL,
    //  `effective_duration` int NOT NULL,
    //  `effect_parameter` double NOT NULL DEFAULT '0',
    //  PRIMARY KEY (`id`)
    //) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // CREATE TABLE `mst_event_display_units` (
    //  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //  `release_key` bigint NOT NULL DEFAULT '1',
    //  `mst_event_id` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    //  `mst_unit_id` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    //  PRIMARY KEY (`id`)
    //) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mst_advent_battles', function (Blueprint $table) {
            $table->string('mst_event_id')->default('')->after('id');
            $table->decimal('score_additional_coef', 5, 3)->after('score_addition_type')->default(0);
        });

        Schema::table('mst_attack_elements', function (Blueprint $table) {
            $table->string('hit_effect_id')->default('')->after('hit_onomatopoeia_group_id');
        });

        Schema::table('mst_event_display_units', function (Blueprint $table) {
            $table->renameColumn('mst_event_id', 'mst_quest_id');
        });

        Schema::create('mst_attack_hit_effects', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->bigInteger('release_key')->default(1);
            $table->string('onomatopoeia1_asset_key', 255)->default('');
            $table->string('onomatopoeia2_asset_key', 255)->default('');
            $table->string('onomatopoeia3_asset_key', 255)->default('');
            $table->string('sound_effect_asset_key', 255)->default('');
            $table->string('killer_sound_effect_asset_key', 255)->default('');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_advent_battles', function (Blueprint $table) {
            $table->dropColumn(['mst_event_id', 'score_additional_coef']);
        });

        Schema::table('mst_attack_elements', function (Blueprint $table) {
            $table->dropColumn('hit_effect_id');
        });

        Schema::table('mst_event_display_units', function (Blueprint $table) {
            $table->renameColumn('mst_quest_id', 'mst_event_id');
        });

        Schema::dropIfExists('mst_attack_hit_effects');
    }
};

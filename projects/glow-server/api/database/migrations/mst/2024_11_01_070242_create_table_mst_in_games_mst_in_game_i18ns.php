<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // 変更前
    // CREATE TABLE `mst_advent_battles` (
    //     `id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //     `advent_battle_type` enum('ScoreChallenge','Raid') COLLATE utf8mb4_bin NOT NULL COMMENT '降臨バトルタイプ',
    //     `time_limit_seconds` mediumint unsigned NOT NULL DEFAULT '0' COMMENT '制限時間秒',
    //     `mst_stage_rule_group_id` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'mst_stage_event_rules.group_id',
    //     `event_bonus_group_id` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'mst_event_bonus_units.event_bonus_group_id',
    //     `challengeable_count` smallint unsigned NOT NULL DEFAULT '0' COMMENT '1日の挑戦可能回数',
    //     `ad_challengeable_count` smallint unsigned NOT NULL DEFAULT '0' COMMENT '1日の広告視聴での挑戦可能回数',
    //     `display_mst_unit_id1` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT '降臨バトルトップ場所1に表示するキャラ',
    //     `display_mst_unit_id2` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT '降臨バトルトップ場所2に表示するキャラ',
    //     `display_mst_unit_id3` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT '降臨バトルトップ場所3に表示するキャラ',
    //     `exp` int unsigned NOT NULL DEFAULT '0' COMMENT '獲得リーダーEXP',
    //     `coin` int unsigned NOT NULL DEFAULT '0' COMMENT '獲得コイン',
    //     `start_at` timestamp NOT NULL COMMENT '降臨バトル開始日',
    //     `end_at` timestamp NOT NULL COMMENT '降臨バトル終了日',
    //     `release_key` bigint NOT NULL DEFAULT '1',
    //     PRIMARY KEY (`id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
    // CREATE TABLE `mst_outposts` (
    //     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `start_at` timestamp NOT NULL,
    //     `end_at` timestamp NOT NULL,
    //     `release_key` bigint NOT NULL DEFAULT '1',
    //     PRIMARY KEY (`id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    // CREATE TABLE `mst_stages` (
    //     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `mst_quest_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `stage_number` int NOT NULL DEFAULT '0',
    //     `cost_stamina` int unsigned NOT NULL,
    //     `exp` int unsigned NOT NULL,
    //     `coin` int unsigned NOT NULL,
    //     `mst_artwork_fragment_drop_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'mst_artwork_fragments.drop_group_id',
    //     `prev_mst_stage_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //     `mst_stage_tips_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //     `sort_order` int unsigned NOT NULL,
    //     `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //     `bgm_asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //     `loop_background_asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
    //     `player_outpost_asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `mst_page_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //     `mst_enemy_outpost_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //     `time_limit` int NOT NULL DEFAULT '0',
    //     `boss_mst_enemy_stage_parameter_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //     `normal_enemy_hp_coef` decimal(10,2) NOT NULL DEFAULT '1.00',
    //     `normal_enemy_attack_coef` decimal(10,2) NOT NULL DEFAULT '1.00',
    //     `normal_enemy_speed_coef` decimal(10,2) NOT NULL DEFAULT '1.00',
    //     `boss_enemy_hp_coef` decimal(10,2) NOT NULL DEFAULT '1.00',
    //     `boss_enemy_attack_coef` decimal(10,2) NOT NULL DEFAULT '1.00',
    //     `boss_enemy_speed_coef` decimal(10,2) NOT NULL DEFAULT '1.00',
    //     `enemy_sequence_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //     `mst_stage_limit_status_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `release_key` bigint unsigned NOT NULL DEFAULT '1',
    //     PRIMARY KEY (`id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    // CREATE TABLE `mst_stages_i18n` (
    //     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `mst_stage_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `language` enum('ja','en','zh-Hant') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ja',
    //     `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `result_tips` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `release_key` int NOT NULL DEFAULT '1',
    //     PRIMARY KEY (`id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    /**
     * 変更内容
     *
     * MstAdventBattle
     * id列の後に追加：mstInGameId:string, assetKey:string
     * adventBattleType列の後にある、timeLimitSeconds列削除
     *
     * MstOutpost
     * assetKeyの後に追加：isDamageInvalidation(bool:unsignedInteger)
     *
     * MstStage
     * mst_in_gamesに追加した列と同盟列を全て削除
     *
     * MstStageI18n
     * 列削除：resultTips
     */

    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::create('mst_in_games', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->bigInteger('release_key')->default(1);
            $table->string('bgm_asset_key')->default('');
            $table->string('loop_background_asset_key')->default('');
            $table->string('player_outpost_asset_key')->default('');
            $table->string('mst_page_id')->default('');
            $table->string('mst_enemy_outpost_id')->default('');
            $table->integer('time_limit');
            $table->string('boss_mst_enemy_stage_parameter_id')->default('');
            $table->decimal('normal_enemy_hp_coef', 10, 2);
            $table->decimal('normal_enemy_attack_coef', 10, 2);
            $table->decimal('normal_enemy_speed_coef', 10, 2);
            $table->decimal('boss_enemy_hp_coef', 10, 2);
            $table->decimal('boss_enemy_attack_coef', 10, 2);
            $table->decimal('boss_enemy_speed_coef', 10, 2);
        });
        Schema::create('mst_in_games_i18n', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->bigInteger('release_key')->default(1);
            $table->string('mst_in_game_id');
            $table->enum('language', ['ja']);
            $table->string('result_tips')->default('');

            $table->unique(['mst_in_game_id', 'language'], 'uk_language');
        });

        Schema::table('mst_advent_battles', function (Blueprint $table) {
            $table->string('asset_key')->default('')->after('id');
            $table->string('mst_in_game_id')->default('')->after('id');
            $table->dropColumn('time_limit_seconds');
            $table->integer('initial_battle_point')->default(0)->after('advent_battle_type');
        });

        Schema::table('mst_enemy_outposts', function (Blueprint $table) {
            $table->boolean('is_damage_invalidation')->unsigned()->default(false)->after('artwork_asset_key');
        });

        Schema::table('mst_stages', function (Blueprint $table) {
            $table->dropColumn('bgm_asset_key');
            $table->dropColumn('loop_background_asset_key');
            $table->dropColumn('player_outpost_asset_key');
            $table->dropColumn('mst_page_id');
            $table->dropColumn('mst_enemy_outpost_id');
            $table->dropColumn('time_limit');
            $table->dropColumn('boss_mst_enemy_stage_parameter_id');
            $table->dropColumn('normal_enemy_hp_coef');
            $table->dropColumn('normal_enemy_attack_coef');
            $table->dropColumn('normal_enemy_speed_coef');
            $table->dropColumn('boss_enemy_hp_coef');
            $table->dropColumn('boss_enemy_attack_coef');
            $table->dropColumn('boss_enemy_speed_coef');
            $table->dropColumn('enemy_sequence_id');
            $table->string('mst_in_game_id')->default('')->after('mst_quest_id');
        });

        Schema::table('mst_stages_i18n', function (Blueprint $table) {
            $table->dropColumn('result_tips');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_in_games');
        Schema::dropIfExists('mst_in_games_i18n');

        Schema::table('mst_advent_battles', function (Blueprint $table) {
            $table->dropColumn('mst_in_game_id');
            $table->dropColumn('asset_key');
            $table->mediumInteger('time_limit_seconds')->default(0);
            $table->dropColumn('initial_battle_point');
        });

        Schema::table('mst_enemy_outposts', function (Blueprint $table) {
            $table->dropColumn('is_damage_invalidation');
        });

        Schema::table('mst_stages', function (Blueprint $table) {
            $table->string('bgm_asset_key')->default('');
            $table->string('loop_background_asset_key')->default('');
            $table->string('player_outpost_asset_key')->default('');
            $table->string('mst_page_id')->default('');
            $table->string('mst_enemy_outpost_id')->default('');
            $table->integer('time_limit');
            $table->string('boss_mst_enemy_stage_parameter_id')->default('');
            $table->decimal('normal_enemy_hp_coef', 10, 2);
            $table->decimal('normal_enemy_attack_coef', 10, 2);
            $table->decimal('normal_enemy_speed_coef', 10, 2);
            $table->decimal('boss_enemy_hp_coef', 10, 2);
            $table->decimal('boss_enemy_attack_coef', 10, 2);
            $table->decimal('boss_enemy_speed_coef', 10, 2);
            $table->string('enemy_sequence_id')->default('');
            $table->dropColumn('mst_in_game_id');
        });

        Schema::table('mst_stages_i18n', function (Blueprint $table) {
            $table->string('result_tips')->default('');
        });
    }
};

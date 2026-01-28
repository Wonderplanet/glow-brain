<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // 変更前のスキーマ

    // CREATE TABLE `mst_enemy_characters` (
    //     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `release_key` bigint NOT NULL DEFAULT '1',
    //     `character_unit_kind` enum('Normal','Formidable','Boss') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `role_type` enum('None','Attack','Balance','Defense','Support','Unique') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `color` enum('None','Colorless','Red','Blue','Yellow','Green') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `mst_series_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '作品ID',
    //     `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `sort_order` int NOT NULL,
    //     `hp` int NOT NULL,
    //     `damage_knock_back_count` int NOT NULL,
    //     `move_speed` int NOT NULL,
    //     `well_distance` double(8,2) NOT NULL,
    //     `attack_power` int NOT NULL,
    //     `attack_combo_cycle` int NOT NULL,
    //     `ability1` enum('None','SlipDamageKomaBlock','AttackPowerDownKomaBlock','GustKomaBlock','AttackPowerUpKomaBoost','WindKomaBoost','AttackPowerUpInNormalKoma','MoveSpeedUpInNormalKoma','DamageCutInNormalKoma') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `ability1_parameter` int NOT NULL,
    //     `bounding_range_front` double(8,2) NOT NULL,
    //     `bounding_range_back` double(8,2) NOT NULL,
    //     `drop_battle_point` int NOT NULL
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // CREATE TABLE `mst_stages` (
    //     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `mst_quest_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `stage_number` int NOT NULL DEFAULT '0',
    //     `cost_stamina` int unsigned NOT NULL,
    //     `exp` int unsigned NOT NULL,
    //     `coin` int unsigned NOT NULL,
    //     `mst_stage_reward_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //     `reward_amount` int unsigned NOT NULL,
    //     `mst_artwork_fragment_drop_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'mst_artwork_fragments.drop_group_id',
    //     `prev_mst_stage_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //     `mst_stage_tips_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //     `sort_order` int unsigned NOT NULL,
    //     `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //     `bgm_asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //     `mst_page_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //     `mst_enemy_outpost_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //     `boss_mst_enemy_character_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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

    // 変更後のスキーマ

    // 既存テーブル変更
    // - name: MstEnemyCharacter
    // params:
    //   - name: id
    //     type: string
    //   - name: mstSeriesId
    //     type: string
    //   - name: assetKey
    //     type: string

    // mst_stages
    // bossMstEnemyCharacterIdをbossMstEnemyStageParameterIdに改名

    // 新規テーブル追加
    // - name: MstEnemyStageParameter
    // params:
    //   - name: id
    //     type: string
    //   - name: mstEnemyCharacterId
    //     type: string
    //   - name: characterUnitKind
    //     type: CharacterUnitKind
    //   - name: roleType
    //     type: CharacterUnitRoleType
    //   - name: color
    //     type: CharacterColor
    //   - name: sortOrder
    //     type: int
    //   - name: hp
    //     type: int
    //   - name: damageKnockBackCount
    //     type: int
    //   - name: moveSpeed
    //     type: int
    //   - name: wellDistance
    //     type: float
    //   - name: attackPower
    //     type: int
    //   - name: attackComboCycle
    //     type: int
    //   - name: ability1
    //     type: UnitAbilityType
    //   - name: ability1Parameter
    //     type: int
    //   - name: boundingRangeFront
    //     type: float
    //   - name: boundingRangeBack
    //     type: float
    //   - name: DropBattlePoint
    //     type: int

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mst_stages', function (Blueprint $table) {
            $table->renameColumn('boss_mst_enemy_character_id', 'boss_mst_enemy_stage_parameter_id');
        });

        Schema::create('mst_enemy_stage_parameters', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('mst_enemy_character_id', 255);
            $table->enum('character_unit_kind', ['Normal', 'Formidable', 'Boss']);
            $table->enum('role_type', ['None', 'Attack', 'Balance', 'Defense', 'Support', 'Unique']);
            $table->enum('color', ['None', 'Colorless', 'Red', 'Blue', 'Yellow', 'Green']);
            $table->integer('sort_order');
            $table->integer('hp');
            $table->integer('damage_knock_back_count');
            $table->integer('move_speed');
            $table->double('well_distance', 8, 2);
            $table->integer('attack_power');
            $table->integer('attack_combo_cycle');
            $table->enum('ability1', ['None', 'SlipDamageKomaBlock', 'AttackPowerDownKomaBlock', 'GustKomaBlock', 'AttackPowerUpKomaBoost', 'WindKomaBoost', 'AttackPowerUpInNormalKoma', 'MoveSpeedUpInNormalKoma', 'DamageCutInNormalKoma']);
            $table->integer('ability1_parameter');
            $table->double('bounding_range_front', 8, 2);
            $table->double('bounding_range_back', 8, 2);
            $table->integer('drop_battle_point');
        });

        Schema::table('mst_enemy_characters', function (Blueprint $table) {
            $table->dropColumn('character_unit_kind');
            $table->dropColumn('role_type');
            $table->dropColumn('color');
            $table->dropColumn('sort_order');
            $table->dropColumn('hp');
            $table->dropColumn('damage_knock_back_count');
            $table->dropColumn('move_speed');
            $table->dropColumn('well_distance');
            $table->dropColumn('attack_power');
            $table->dropColumn('attack_combo_cycle');
            $table->dropColumn('ability1');
            $table->dropColumn('ability1_parameter');
            $table->dropColumn('bounding_range_front');
            $table->dropColumn('bounding_range_back');
            $table->dropColumn('drop_battle_point');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_stages', function (Blueprint $table) {
            $table->renameColumn('boss_mst_enemy_stage_parameter_id', 'boss_mst_enemy_character_id');
        });

        Schema::dropIfExists('mst_enemy_stage_parameters');

        Schema::table('mst_enemy_characters', function (Blueprint $table) {
            $table->enum('character_unit_kind', ['Normal', 'Formidable', 'Boss']);
            $table->enum('role_type', ['None', 'Attack', 'Balance', 'Defense', 'Support', 'Unique']);
            $table->enum('color', ['None', 'Colorless', 'Red', 'Blue', 'Yellow', 'Green']);
            $table->integer('sort_order');
            $table->integer('hp');
            $table->integer('damage_knock_back_count');
            $table->integer('move_speed');
            $table->double('well_distance', 8, 2);
            $table->integer('attack_power');
            $table->integer('attack_combo_cycle');
            $table->enum('ability1', ['None', 'SlipDamageKomaBlock', 'AttackPowerDownKomaBlock', 'GustKomaBlock', 'AttackPowerUpKomaBoost', 'WindKomaBoost', 'AttackPowerUpInNormalKoma', 'MoveSpeedUpInNormalKoma', 'DamageCutInNormalKoma']);
            $table->integer('ability1_parameter');
            $table->double('bounding_range_front', 8, 2);
            $table->double('bounding_range_back', 8, 2);
            $table->integer('drop_battle_point');
        });
    }
};

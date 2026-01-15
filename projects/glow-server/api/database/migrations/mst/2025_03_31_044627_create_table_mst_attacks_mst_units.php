<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // スキーマ変更指示書
    // - テーブル
    //     - MstAttack
    //         - assetKey削除
    //             - 削除前は、attackKindの直後にあった
    //     - MstUnit
    //         - mstUnitAbilityId1列の後に、以下の順番で列追加
    //             - name: abilityUnlockRank1
    //                 type: int
    //             - name: mstUnitAbilityId2
    //                 type: string
    //             - name: abilityUnlockRank2
    //                 type: int
    //             - name: mstUnitAbilityId3
    //                 type: string
    //             - name: abilityUnlockRank3
    //                 type: int
    // - enum
    //     - CharacterUnitKind
    //         - Formidable削除
    //             - 削除前は、Normalの直後にあった
    // - Data
    //     - PartyStatus
    //         - moveSpeedをintからfloat型へ変更

    // CREATE TABLE `mst_attacks` (
    //     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `mst_unit_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `unit_grade` int NOT NULL,
    //     `attack_kind` enum('Normal','Special','Appearance') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `killer_colors` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `killer_percentage` int NOT NULL DEFAULT '0',
    //     `action_frames` int NOT NULL,
    //     `attack_delay` int NOT NULL,
    //     `next_attack_interval` int NOT NULL,
    //     `release_key` int NOT NULL DEFAULT '1',
    //     PRIMARY KEY (`id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // CREATE TABLE `mst_units` (
    //     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `fragment_mst_item_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `color` enum('Colorless','Red','Blue','Yellow','Green') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Colorless' COMMENT '属性',
    //     `role_type` enum('None','Attack','Balance','Defense','Support','Unique','Technical','Special') COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `attack_range_type` enum('Short','Middle','Long') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `unit_label` enum('DropR','DropSR','DropSSR','DropUR','PremiumR','PremiumSR','PremiumSSR','PremiumUR','FestivalUR') COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `has_specific_rank_up` tinyint NOT NULL DEFAULT '0' COMMENT 'キャラ個別のランクアップ設定を使うかどうか',
    //     `mst_series_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '作品ID',
    //     `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
    //     `rarity` enum('N','R','SR','SSR','UR') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `sort_order` int unsigned NOT NULL,
    //     `summon_cost` int unsigned NOT NULL,
    //     `summon_cool_time` int unsigned NOT NULL,
    //     `special_attack_initial_cool_time` int unsigned NOT NULL,
    //     `special_attack_cool_time` int unsigned NOT NULL,
    //     `min_hp` int unsigned NOT NULL,
    //     `max_hp` int unsigned NOT NULL,
    //     `damage_knock_back_count` int unsigned NOT NULL,
    //     `move_speed` int unsigned NOT NULL,
    //     `well_distance` double(8,2) NOT NULL,
    //     `min_attack_power` int unsigned NOT NULL,
    //     `max_attack_power` int unsigned NOT NULL,
    //     `mst_unit_ability_id1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `is_encyclopedia_special_attack_position_right` tinyint unsigned NOT NULL DEFAULT '0',
    //     `release_key` int NOT NULL DEFAULT '1',
    //     PRIMARY KEY (`id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // CREATE TABLE `mst_enemy_stage_parameters` (
    //     `id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //     `mst_enemy_character_id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //     `character_unit_kind` enum('Normal','Formidable','Boss','AdventBattleBoss') COLLATE utf8mb4_bin NOT NULL,
    //     `role_type` enum('None','Attack','Balance','Defense','Support','Unique','Technical','Special') COLLATE utf8mb4_bin NOT NULL,
    //     `color` enum('None','Colorless','Red','Blue','Yellow','Green') COLLATE utf8mb4_bin NOT NULL,
    //     `sort_order` int NOT NULL,
    //     `hp` int NOT NULL,
    //     `damage_knock_back_count` int NOT NULL,
    //     `move_speed` int NOT NULL,
    //     `well_distance` double NOT NULL,
    //     `attack_power` int NOT NULL,
    //     `attack_combo_cycle` int NOT NULL,
    //     `mst_unit_ability_id1` varchar(255) COLLATE utf8mb4_bin DEFAULT '',
    //     `drop_battle_point` int NOT NULL,
    //     `mst_transformation_enemy_stage_parameter_id` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    //     `transformation_condition_type` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT 'None',
    //     `transformation_condition_value` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    //     PRIMARY KEY (`id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify `mst_attacks` table
        Schema::table('mst_attacks', function (Blueprint $table) {
            $table->dropColumn('asset_key');
        });

        // Modify `mst_units` table
        Schema::table('mst_units', function (Blueprint $table) {
            $table->integer('ability_unlock_rank1')->after('mst_unit_ability_id1');
            $table->string('mst_unit_ability_id2')->default('')->after('ability_unlock_rank1');
            $table->integer('ability_unlock_rank2')->after('mst_unit_ability_id2');
            $table->string('mst_unit_ability_id3')->default('')->after('ability_unlock_rank2');
            $table->integer('ability_unlock_rank3')->after('mst_unit_ability_id3');
            $table->decimal('move_speed', 10, 2)->change();
        });

        // Modify `mst_enemy_stage_parameters` table
        Schema::table('mst_enemy_stage_parameters', function (Blueprint $table) {
            $table->string('character_unit_kind', 255)->default('Normal')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert changes to `mst_attacks` table
        Schema::table('mst_attacks', function (Blueprint $table) {
            $table->string('asset_key')->after('attack_kind');
        });

        // Revert changes to `mst_units` table
        Schema::table('mst_units', function (Blueprint $table) {
            $table->dropColumn('ability_unlock_rank1');
            $table->dropColumn('mst_unit_ability_id2');
            $table->dropColumn('ability_unlock_rank2');
            $table->dropColumn('mst_unit_ability_id3');
            $table->dropColumn('ability_unlock_rank3');
            $table->integer('move_speed')->change();
        });

        // Revert changes to `mst_enemy_stage_parameters` table
        Schema::table('mst_enemy_stage_parameters', function (Blueprint $table) {
            DB::statement("ALTER TABLE `mst_enemy_stage_parameters` MODIFY `character_unit_kind` ENUM('Normal','Formidable','Boss','AdventBattleBoss') COLLATE utf8mb4_bin NOT NULL");
        });
    }
};

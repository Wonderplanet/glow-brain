<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // 変更前
    // CREATE TABLE `mst_koma_lines` (
    //     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `mst_page_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `row` int NOT NULL,
    //     `height` double(8,2) NOT NULL,
    //     `koma_line_layout_asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `koma1_asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `koma1_width` double(8,2) DEFAULT NULL,
    //     `koma1_back_ground_offset` double(8,2) NOT NULL,
    //     `koma1_effect_type` enum('None','AttackPowerUp','AttackPowerDown','MoveSpeedUp','SlipDamage','Tailwind','Headwind','Fierce','SpecialAttackSeal','Gust','Poison','Darkness') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //     `koma1_effect_parameter1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `koma1_effect_parameter2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `koma1_effect_target_side` enum('All','Player','Enemy') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `koma1_effect_target_colors` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `koma1_effect_target_roles` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `koma2_asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //     `koma2_width` double(8,2) DEFAULT NULL,
    //     `koma2_back_ground_offset` double(8,2) DEFAULT NULL,
    //     `koma2_effect_type` enum('None','AttackPowerUp','AttackPowerDown','MoveSpeedUp','SlipDamage','Tailwind','Headwind','Fierce','SpecialAttackSeal','Gust','Poison','Darkness') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //     `koma2_effect_parameter1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `koma2_effect_parameter2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `koma2_effect_target_side` enum('All','Player','Enemy') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //     `koma2_effect_target_colors` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //     `koma2_effect_target_roles` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //     `koma3_asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //     `koma3_width` double(8,2) DEFAULT NULL,
    //     `koma3_back_ground_offset` double(8,2) DEFAULT NULL,
    //     `koma3_effect_type` enum('None','AttackPowerUp','AttackPowerDown','MoveSpeedUp','SlipDamage','Tailwind','Headwind','Fierce','SpecialAttackSeal','Gust','Poison','Darkness') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //     `koma3_effect_parameter1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `koma3_effect_parameter2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `koma3_effect_target_side` enum('All','Player','Enemy') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //     `koma3_effect_target_colors` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //     `koma3_effect_target_roles` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //     `koma4_asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //     `koma4_width` double(8,2) DEFAULT NULL,
    //     `koma4_back_ground_offset` double(8,2) DEFAULT NULL,
    //     `koma4_effect_type` enum('None','AttackPowerUp','AttackPowerDown','MoveSpeedUp','SlipDamage','Tailwind','Headwind','Fierce','SpecialAttackSeal','Gust','Poison','Darkness') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //     `koma4_effect_parameter1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `koma4_effect_parameter2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `koma4_effect_target_side` enum('All','Player','Enemy') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //     `koma4_effect_target_colors` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //     `koma4_effect_target_roles` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //     PRIMARY KEY (`id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // 変更内容
    // koma1,2,3,4_effect_typeをvarchar(255) not null default'None'に変更

    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::table('mst_koma_lines', function (Blueprint $table) {
            $table->string('koma1_effect_type', 255)->default('None')->change();
            $table->string('koma2_effect_type', 255)->default('None')->change();
            $table->string('koma3_effect_type', 255)->default('None')->change();
            $table->string('koma4_effect_type', 255)->default('None')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_koma_lines', function (Blueprint $table) {
            $table->enum('koma1_effect_type', ['None', 'AttackPowerUp', 'AttackPowerDown', 'MoveSpeedUp', 'SlipDamage', 'Tailwind', 'Headwind', 'Fierce', 'SpecialAttackSeal', 'Gust', 'Poison', 'Darkness'])->default('None')->change();
            $table->enum('koma2_effect_type', ['None', 'AttackPowerUp', 'AttackPowerDown', 'MoveSpeedUp', 'SlipDamage', 'Tailwind', 'Headwind', 'Fierce', 'SpecialAttackSeal', 'Gust', 'Poison', 'Darkness'])->default('None')->change();
            $table->enum('koma3_effect_type', ['None', 'AttackPowerUp', 'AttackPowerDown', 'MoveSpeedUp', 'SlipDamage', 'Tailwind', 'Headwind', 'Fierce', 'SpecialAttackSeal', 'Gust', 'Poison', 'Darkness'])->default('None')->change();
            $table->enum('koma4_effect_type', ['None', 'AttackPowerUp', 'AttackPowerDown', 'MoveSpeedUp', 'SlipDamage', 'Tailwind', 'Headwind', 'Fierce', 'SpecialAttackSeal', 'Gust', 'Poison', 'Darkness'])->default('None')->change();
        });
    }
};

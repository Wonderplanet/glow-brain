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
    // CREATE TABLE `mst_enemy_stage_parameters` (
    //  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //  `mst_enemy_character_id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //  `character_unit_kind` enum('Normal','Formidable','Boss') COLLATE utf8mb4_bin NOT NULL,
    //  `role_type` enum('None','Attack','Balance','Defense','Support','Unique') COLLATE utf8mb4_bin NOT NULL,
    //  `color` enum('None','Colorless','Red','Blue','Yellow','Green') COLLATE utf8mb4_bin NOT NULL,
    //  `sort_order` int NOT NULL,
    //  `hp` int NOT NULL,
    //  `damage_knock_back_count` int NOT NULL,
    //  `move_speed` int NOT NULL,
    //  `well_distance` double(8,2) NOT NULL,
    //  `attack_power` int NOT NULL,
    //  `attack_combo_cycle` int NOT NULL,
    //  `ability1` enum('None','SlipDamageKomaBlock','AttackPowerDownKomaBlock','GustKomaBlock','AttackPowerUpKomaBoost','WindKomaBoost','AttackPowerUpInNormalKoma','MoveSpeedUpInNormalKoma','DamageCutInNormalKoma') COLLATE utf8mb4_bin NOT NULL,
    //  `ability1_parameter` int NOT NULL,
    //  `bounding_range_front` double(8,2) NOT NULL,
    //  `bounding_range_back` double(8,2) NOT NULL,
    //  `drop_battle_point` int NOT NULL,
    //  PRIMARY KEY (`id`)
    //) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

    // 変更内容
    // - name: mstTransformationEnemyStageParameterId
    //   type: string
    // - name: transformationConditionType
    //   type: UnitTransformationConditionType
    // - name: transformationConditionValue
    //   type: string
    // - name: transformationStartDelay
    //   type: int

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mst_enemy_stage_parameters', function (Blueprint $table) {
            $table->string('mst_transformation_enemy_stage_parameter_id', 255)->default('')->after('drop_battle_point');
            $table->enum('transformation_condition_type', ['None', 'HpPercentage'])->default('None')->after('mst_transformation_enemy_stage_parameter_id');
            $table->string('transformation_condition_value', 255)->default('')->after('transformation_condition_type');
            $table->integer('transformation_start_delay')->default(0)->after('transformation_condition_value');
        });
        DB::statement("ALTER TABLE `mst_manga_animations` MODIFY COLUMN `condition_type` ENUM('None', 'Start', 'Victory', 'EnemySummon', 'EnemyMoveStart', 'TransformationStart', 'TransformationEnd');");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_enemy_stage_parameters', function (Blueprint $table) {
            $table->dropColumn('mst_transformation_enemy_stage_parameter_id');
            $table->dropColumn('transformation_condition_type');
            $table->dropColumn('transformation_condition_value');
            $table->dropColumn('transformation_start_delay');
        });
        DB::statement("ALTER TABLE `mst_manga_animations` MODIFY COLUMN `condition_type` ENUM('None', 'Start', 'Victory', 'EnemySummon', 'EnemyMoveStart');");
    }
};

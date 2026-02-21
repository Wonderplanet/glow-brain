<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /**
         * mst_enemy_stage_parametersのtransformation_condition_typeのenumにStageTimeを追加
         * - name: None
         * - name: HpPercentage
         * - name: StageTime
         */
        DB::statement("ALTER TABLE mst_enemy_stage_parameters MODIFY COLUMN transformation_condition_type ENUM('None','HpPercentage','StageTime') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE mst_enemy_stage_parameters MODIFY COLUMN transformation_condition_type ENUM('None','HpPercentage') NOT NULL");
    }
};

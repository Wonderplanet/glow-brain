<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /**
         * mst_enemy_stage_parametersのrole_typeのenumにTechnicalとSpecialを追加
         *
         * - name: None
         * - name: Attack
         * - name: Balance
         * - name: Defense
         * - name: Support
         * - name: Unique
         * - name: Technical
         * - name: Special
         *
         */
        DB::statement("ALTER TABLE mst_enemy_stage_parameters MODIFY COLUMN role_type ENUM('None','Attack','Balance','Defense','Support','Unique','Technical','Special') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE mst_enemy_stage_parameters MODIFY COLUMN role_type ENUM('None','Attack','Balance','Defense','Support','Unique') NOT NULL");
    }
};

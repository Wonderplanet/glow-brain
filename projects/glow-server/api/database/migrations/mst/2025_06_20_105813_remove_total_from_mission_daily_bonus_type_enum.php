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
        // Remove 'Total' from mission_daily_bonus_type enum in mst_mission_daily_bonuses table
        // Only 'DailyBonus' will remain as a valid enum value
        DB::statement("ALTER TABLE mst_mission_daily_bonuses MODIFY COLUMN mission_daily_bonus_type ENUM('DailyBonus') NOT NULL COMMENT 'デイリーボーナスタイプ'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore 'Total' to mission_daily_bonus_type enum
        DB::statement("ALTER TABLE mst_mission_daily_bonuses MODIFY COLUMN mission_daily_bonus_type ENUM('Total','DailyBonus') NOT NULL COMMENT 'デイリーボーナスタイプ'");
    }
};

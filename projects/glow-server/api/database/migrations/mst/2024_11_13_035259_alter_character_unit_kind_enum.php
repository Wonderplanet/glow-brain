<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE mst_enemy_stage_parameters MODIFY COLUMN character_unit_kind ENUM('Normal', 'Formidable', 'Boss', 'AdventBattleBoss') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE mst_enemy_stage_parameters MODIFY COLUMN character_unit_kind ENUM('Normal', 'Formidable', 'Boss') NOT NULL");
    }
};

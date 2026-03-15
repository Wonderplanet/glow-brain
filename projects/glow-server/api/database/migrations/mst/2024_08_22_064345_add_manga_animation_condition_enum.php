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
        DB::statement("ALTER TABLE `mst_manga_animations` MODIFY COLUMN `condition_type` ENUM('None', 'Start', 'Victory', 'EnemySummon', 'EnemyMoveStart', 'TransformationReady', 'TransformationStart', 'TransformationEnd');");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `mst_manga_animations` MODIFY COLUMN `condition_type` ENUM('None', 'Start', 'Victory', 'EnemySummon', 'EnemyMoveStart', 'TransformationStart', 'TransformationEnd');");
    }
};

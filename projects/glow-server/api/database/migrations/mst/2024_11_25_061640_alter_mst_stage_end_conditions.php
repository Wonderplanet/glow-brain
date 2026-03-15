<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // 変更前
    // CREATE TABLE `mst_stage_end_conditions` (
    //  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //  `release_key` bigint NOT NULL DEFAULT '1',
    //  `mst_stage_id` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    //  `stage_end_type` enum('Victory','Defeat','Finish') COLLATE utf8mb4_bin NOT NULL,
    //  `condition_type` enum('PlayerOutpostBreakDown','EnemyOutpostBreakDown','TimeOver','DefeatedEnemyCount','DefeatUnit') COLLATE utf8mb4_bin NOT NULL,
    //  `condition_value` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    //  PRIMARY KEY (`id`)
    //) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mst_stage_end_conditions', function (Blueprint $table) {
            $table->renameColumn('condition_value', 'condition_value1');
            $table->string('condition_value2', 255)->default('')->after('condition_value1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_stage_end_conditions', function (Blueprint $table) {
            $table->dropColumn('condition_value2');
            $table->renameColumn('condition_value1', 'condition_value');
        });
    }
};

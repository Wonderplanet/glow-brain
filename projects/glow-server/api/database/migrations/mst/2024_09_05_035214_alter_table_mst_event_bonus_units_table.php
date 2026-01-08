<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // 変更前
    // CREATE TABLE `mst_event_bonus_units` (
    //     `id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //     `release_key` bigint NOT NULL DEFAULT '1',
    //     `mst_unit_id` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    //     `bonus_percent` int NOT NULL,
    //     `event_bonus_group_id` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    //     `is_pick_up` tinyint unsigned NOT NULL,
    //     PRIMARY KEY (`id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

    // 変更内容
    // bonus_percentをbonus_percentageに改名

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mst_event_bonus_units', function (Blueprint $table) {
            $table->renameColumn('bonus_percent', 'bonus_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_event_bonus_units', function (Blueprint $table) {
            $table->renameColumn('bonus_percentage', 'bonus_percent');
        });
    }
};

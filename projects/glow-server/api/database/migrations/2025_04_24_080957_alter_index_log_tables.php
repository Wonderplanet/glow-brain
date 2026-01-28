<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public static array $tableList = [
        'log_advent_battle_actions',
        'log_api_requests',
        'log_coins',
        'log_emblems',
        'log_exps',
        'log_gacha_actions',
        'log_items',
        'log_outpost_enhancements',
        'log_stage_actions',
        'log_staminas',
        'log_suspected_users',
        'log_unit_grade_ups',
        'log_unit_level_ups',
        'log_unit_rank_ups',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // create_atにindexが無いlogテーブルにindexを貼る
        foreach (self::$tableList as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->index('created_at', 'created_at_index');
            });
        };
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (self::$tableList as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropIndex('created_at_index');
            });
        };
    }
};

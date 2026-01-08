<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // CREATE TABLE `mst_attack_elements` (
    //     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `release_key` bigint NOT NULL DEFAULT '1',
    //     `mst_attack_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `sort_order` int NOT NULL,
    //     `attack_delay` int NOT NULL,
    //     `attack_type` enum('None','Direct','Homing') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `range_start_type` enum('Distance','Koma','KomaLine','Page') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `range_start_parameter` double(8,2) NOT NULL,
    //     `range_end_type` enum('Distance','Koma','KomaLine','Page') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `range_end_parameter` double(8,2) NOT NULL,
    //     `max_target_count` int NOT NULL,
    //     `target` enum('Friend','Foe','Self') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `target_type` enum('All','Character','Outpost') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `target_colors` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `target_roles` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `damage_type` enum('None','Damage','Heal') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `hit_type` enum('Normal','NoNockBack','KnockBack1','KnockBack2','KnockBack3','ForcedKnockBack5','Drain') COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `hit_parameter1` int unsigned NOT NULL DEFAULT '0',
    //     `hit_parameter2` int unsigned NOT NULL DEFAULT '0',
    //     `probability` int NOT NULL,
    //     `power_parameter_type` enum('Percentage','Fixed','MaxHpPercentage') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `power_parameter` int NOT NULL,
    //     `effect_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'None',
    //     `effective_count` int NOT NULL,
    //     `effective_duration` int NOT NULL,
    //     `effect_parameter` int NOT NULL,
    //     PRIMARY KEY (`id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::table('mst_attack_elements', function (Blueprint $table) {
            $table->string('hit_type', 255)->default('Normal')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_attack_elements', function (Blueprint $table) {
            $table->enum('hit_type', ['Normal', 'NoNockBack', 'KnockBack1', 'KnockBack2', 'KnockBack3', 'ForcedKnockBack5', 'Drain'])->change();
        });
    }
};

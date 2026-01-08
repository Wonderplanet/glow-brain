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
    //     `damage_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'None',
    //     `hit_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Normal',
    //     `hit_parameter1` int unsigned NOT NULL DEFAULT '0',
    //     `hit_parameter2` int unsigned NOT NULL DEFAULT '0',
    //     `is_hit_stop` tinyint NOT NULL DEFAULT '0',
    //     `probability` int NOT NULL,
    //     `power_parameter_type` enum('Percentage','Fixed','MaxHpPercentage') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `power_parameter` int NOT NULL,
    //     `effect_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'None',
    //     `effective_count` int NOT NULL,
    //     `effective_duration` int NOT NULL,
    //     `effect_parameter` int NOT NULL,
    //     PRIMARY KEY (`id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    // 変更内容
    // attack_typeのHomingをDeckに変更

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE mst_attack_elements MODIFY attack_type enum("None","Direct","Deck") CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE mst_attack_elements MODIFY attack_type enum("None","Direct","Homing") CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;');
    }
};

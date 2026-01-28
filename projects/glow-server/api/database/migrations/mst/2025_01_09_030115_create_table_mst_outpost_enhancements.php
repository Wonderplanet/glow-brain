<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // 変更前
    // CREATE TABLE `mst_outpost_enhancements` (
    //     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `mst_outpost_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `outpost_enhancement_type` enum('BeamDamage','BeamInterval','LeaderPointSpeed','LeaderPointLimit','OutpostHp','SummonInterval','LeaderPointUp') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `release_key` bigint NOT NULL DEFAULT '1',
    //     PRIMARY KEY (`id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    // 変更内容
    // outpost_enhancement_typeから、BeamDamage,BeamIntervalを削除して、RushChargeSpeedを追加

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE mst_outpost_enhancements MODIFY outpost_enhancement_type ENUM("LeaderPointSpeed","LeaderPointLimit","OutpostHp","SummonInterval","LeaderPointUp","RushChargeSpeed") CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE mst_outpost_enhancements MODIFY outpost_enhancement_type ENUM("BeamDamage","BeamInterval","LeaderPointSpeed","LeaderPointLimit","OutpostHp","SummonInterval","LeaderPointUp") CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;');
    }
};

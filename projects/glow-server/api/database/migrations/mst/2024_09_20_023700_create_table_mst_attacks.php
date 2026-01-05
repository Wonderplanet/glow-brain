<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // CREATE TABLE `mst_attacks` (
    //     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `mst_unit_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `unit_grade` int NOT NULL,
    //     `attack_kind` enum('Normal','Special','Appearance') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `killer_colors` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `killer_roles` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `action_frames` int NOT NULL,
    //     `attack_delay` int NOT NULL,
    //     `next_attack_interval` int NOT NULL,
    //     `release_key` int NOT NULL DEFAULT '1',
    //     PRIMARY KEY (`id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mst_attacks', function (Blueprint $table) {
            $table->dropColumn('killer_roles');
            $table->integer('killer_percentage')->default(0)->after('killer_colors');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_attacks', function (Blueprint $table) {
            $table->string('killer_roles')->default('')->after('killer_colors');
            $table->dropColumn('killer_percentage');
        });
    }
};

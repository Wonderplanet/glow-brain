<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // 変更前
    // CREATE TABLE `mst_unit_abilities` (
    //     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `mst_ability_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `ability_parameter` int NOT NULL,
    //     `release_key` bigint NOT NULL DEFAULT '1',
    //     PRIMARY KEY (`id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    // 変更内容
    // ability_parameterの後にability_parameter2を追加
    // ability_parameterをability_parameter1へリネーム

    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::table('mst_unit_abilities', function (Blueprint $table) {
            $table->integer('ability_parameter2')->after('ability_parameter');
            $table->renameColumn('ability_parameter', 'ability_parameter1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_unit_abilities', function (Blueprint $table) {
            $table->dropColumn('ability_parameter2');
            $table->renameColumn('ability_parameter1', 'ability_parameter');
        });
    }
};

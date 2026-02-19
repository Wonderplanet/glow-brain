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
    //  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `mst_ability_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `ability_parameter1` int NOT NULL,
    //  `ability_parameter2` int NOT NULL,
    //  `release_key` bigint NOT NULL DEFAULT '1',
    //  PRIMARY KEY (`id`)
    //) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // 変更内容
    // ability_parameter1,2をvarchar(255) nullable default '' に変更
    // ability_parameter3を追加

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mst_unit_abilities', function (Blueprint $table) {
            $table->string('ability_parameter1')->nullable()->default(null)->change();
            $table->string('ability_parameter2')->nullable()->default(null)->change();
            $table->string('ability_parameter3')->nullable()->default(null)->after('ability_parameter2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_unit_abilities', function (Blueprint $table) {
            $table->integer('ability_parameter1')->change();
            $table->integer('ability_parameter2')->change();
            $table->dropColumn('ability_parameter3');
        });
    }
};

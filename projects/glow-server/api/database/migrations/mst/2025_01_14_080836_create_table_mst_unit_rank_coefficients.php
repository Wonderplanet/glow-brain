<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // 変更前
    // CREATE TABLE `mst_unit_rank_coefficients` (
    //     `id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //     `rank` int unsigned NOT NULL COMMENT 'ユニットのランク',
    //     `coefficient` int NOT NULL COMMENT '係数',
    //     `release_key` bigint NOT NULL DEFAULT '1',
    //     PRIMARY KEY (`id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
    // 変更内容
    // special_unit_coefficient列(int)追加

    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::table('mst_unit_rank_coefficients', function (Blueprint $table) {
            $table->integer('special_unit_coefficient')->comment('スペシャルキャラ用のランクステータス上昇率')->after('coefficient');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_unit_rank_coefficients', function (Blueprint $table) {
            $table->dropColumn('special_unit_coefficient');
        });
    }
};

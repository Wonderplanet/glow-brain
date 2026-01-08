<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // 変更前
    //CREATE TABLE `usr_units` (
    //  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `mst_unit_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
    //  `level` int(10) unsigned NOT NULL DEFAULT '1',
    //  `rank` int(10) unsigned NOT NULL DEFAULT '1',
    //  `grade_level` int(10) unsigned NOT NULL DEFAULT '0',
    //  `atk` int(10) unsigned NOT NULL DEFAULT '0',
    //  `created_at` timestamp NULL DEFAULT NULL,
    //  `updated_at` timestamp NULL DEFAULT NULL,
    //  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
    //  UNIQUE KEY `mst_unit_and_usr_id_index` (`usr_user_id`,`mst_unit_id`)
    //) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('usr_units', function (Blueprint $table) {
            $table->dropColumn('atk');
        });
        Schema::table('usr_units', function (Blueprint $table) {
            $table->integer('battle_count')->default(0)->comment('出撃回数')->after('grade_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_units', function (Blueprint $table) {
            $table->dropColumn('battle_count');
        });
        Schema::table('usr_units', function (Blueprint $table) {
            $table->integer('atk')->default(0)->comment('攻撃力')->after('grade_level');
        });
    }
};

<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // 変更前
    // CREATE TABLE `mst_unit_specific_rank_ups` (
    //     `id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //     `release_key` bigint NOT NULL DEFAULT '1',
    //     `mst_unit_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_units.id',
    //     `rank` int NOT NULL COMMENT 'Lv上限開放後のユニットのランク',
    //     `amount` int NOT NULL COMMENT 'リミテッドメモリーの必要数',
    //     `require_level` int NOT NULL COMMENT 'Lv上限開放に必要なユニットのレベル',
    //     `r_memory_fragment_amount` int NOT NULL DEFAULT '0' COMMENT '初級メモリーフラグメントの必要数',
    //     `sr_memory_fragment_amount` int NOT NULL DEFAULT '0' COMMENT '中級メモリーフラグメントの必要数',
    //     `ssr_memory_fragment_amount` int NOT NULL DEFAULT '0' COMMENT '上級メモリーフラグメントの必要数',
    //     PRIMARY KEY (`id`),
    //     UNIQUE KEY `uk_mst_unit_id_rank` (`mst_unit_id`,`rank`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='ユニット個別のランクアップ設定';

    // 変更内容
    // amount列の後にunitMemoryAmount列を追加（int, default 0, comment=キャラ個別メモリーの必要数）

    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::table('mst_unit_specific_rank_ups', function (Blueprint $table) {
            $table->unsignedInteger('unit_memory_amount')->default(0)->comment('キャラ個別メモリーの必要数')->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_unit_specific_rank_ups', function (Blueprint $table) {
            $table->dropColumn('unit_memory_amount');
        });
    }
};

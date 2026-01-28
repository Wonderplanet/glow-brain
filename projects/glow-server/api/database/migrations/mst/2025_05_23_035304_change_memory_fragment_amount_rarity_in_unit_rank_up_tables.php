<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // 変更前
    // CREATE TABLE `mst_unit_rank_ups` (
    //   `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //   `unit_label` enum('DropR','DropSR','DropSSR','DropUR','PremiumR','PremiumSR','PremiumSSR','PremiumUR','FestivalUR') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //   `rank` int NOT NULL,
    //   `amount` int NOT NULL,
    //   `require_level` int NOT NULL,
    //   `r_memory_fragment_amount` int NOT NULL DEFAULT '0' COMMENT '初級メモリーフラグメントの必要数',
    //   `sr_memory_fragment_amount` int NOT NULL DEFAULT '0' COMMENT '中級メモリーフラグメントの必要数',
    //   `ssr_memory_fragment_amount` int NOT NULL DEFAULT '0' COMMENT '上級メモリーフラグメントの必要数',
    //   `release_key` int NOT NULL DEFAULT '1',
    //   PRIMARY KEY (`id`),
    //   UNIQUE KEY `uk_unit_label_rank` (`unit_label`,`rank`)
    // ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // CREATE TABLE `mst_unit_specific_rank_ups` (
    //   `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
    //   `release_key` bigint NOT NULL DEFAULT '1',
    //   `mst_unit_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_units.id',
    //   `rank` int NOT NULL COMMENT 'Lv上限開放後のユニットのランク',
    //   `amount` int NOT NULL COMMENT 'リミテッドメモリーの必要数',
    //   `unit_memory_amount` int unsigned NOT NULL DEFAULT '0' COMMENT 'キャラ個別メモリーの必要数',
    //   `require_level` int NOT NULL COMMENT 'Lv上限開放に必要なユニットのレベル',
    //   `r_memory_fragment_amount` int NOT NULL DEFAULT '0' COMMENT '初級メモリーフラグメントの必要数',
    //   `sr_memory_fragment_amount` int NOT NULL DEFAULT '0' COMMENT '中級メモリーフラグメントの必要数',
    //   `ssr_memory_fragment_amount` int NOT NULL DEFAULT '0' COMMENT '上級メモリーフラグメントの必要数',
    //   PRIMARY KEY (`id`),
    //   UNIQUE KEY `uk_mst_unit_id_rank` (`mst_unit_id`,`rank`)
    // ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='ユニット個別のランクアップ設定';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mst_unit_rank_ups', function (Blueprint $table) {
            $table->dropColumn('r_memory_fragment_amount');

            // コメントの更新
            $table->integer('sr_memory_fragment_amount')->default(0)->comment('初級メモリーフラグメントの必要数')->change();
            $table->integer('ssr_memory_fragment_amount')->default(0)->comment('中級メモリーフラグメントの必要数')->change();

            $table->integer('ur_memory_fragment_amount')->default(0)->after('ssr_memory_fragment_amount')->comment('上級メモリーフラグメントの必要数');
        });
        Schema::table('mst_unit_specific_rank_ups', function (Blueprint $table) {
            $table->dropColumn('r_memory_fragment_amount');

            // コメントの更新
            $table->integer('sr_memory_fragment_amount')->default(0)->comment('初級メモリーフラグメントの必要数')->change();
            $table->integer('ssr_memory_fragment_amount')->default(0)->comment('中級メモリーフラグメントの必要数')->change();

            $table->integer('ur_memory_fragment_amount')->default(0)->after('ssr_memory_fragment_amount')->comment('上級メモリーフラグメントの必要数');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_unit_rank_ups', function (Blueprint $table) {
            $table->integer('r_memory_fragment_amount')->default(0)->comment('初級メモリーフラグメントの必要数');

            // コメントの更新
            $table->integer('sr_memory_fragment_amount')->default(0)->comment('中級メモリーフラグメントの必要数')->change();
            $table->integer('ssr_memory_fragment_amount')->default(0)->comment('上級メモリーフラグメントの必要数')->change();

            $table->dropColumn('ur_memory_fragment_amount');
        });
        Schema::table('mst_unit_specific_rank_ups', function (Blueprint $table) {
            $table->integer('r_memory_fragment_amount')->default(0)->comment('初級メモリーフラグメントの必要数');

            // コメントの更新
            $table->integer('sr_memory_fragment_amount')->default(0)->comment('中級メモリーフラグメントの必要数')->change();
            $table->integer('ssr_memory_fragment_amount')->default(0)->comment('上級メモリーフラグメントの必要数')->change();

            $table->dropColumn('ur_memory_fragment_amount');
        });
    }
};

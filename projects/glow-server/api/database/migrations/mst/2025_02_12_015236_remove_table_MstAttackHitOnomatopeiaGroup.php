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
    // CREATE TABLE `mst_attack_hit_onomatopeia_groups` (
    //     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
    //     `release_key` bigint NOT NULL DEFAULT '1',
    //     `asset_key1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    //     `asset_key2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    //     `asset_key3` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    //     PRIMARY KEY (`id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

    // 変更内容
    // mst_attack_hit_onomatopeia_groups削除する

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('mst_attack_hit_onomatopeia_groups');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('CREATE TABLE `mst_attack_hit_onomatopeia_groups` (
            `id` varchar(255) NOT NULL,
            `release_key` bigint NOT NULL DEFAULT \'1\',
            `asset_key1` varchar(255) NOT NULL DEFAULT \'\',
            `asset_key2` varchar(255) NOT NULL DEFAULT \'\',
            `asset_key3` varchar(255) NOT NULL DEFAULT \'\',
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;');
    }
};

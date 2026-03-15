<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // テーブル削除
    // mst_avatar_frames
    // mst_avatars
    // mst_handout_groups
    // mst_handouts
    // mst_stage_drop_additions
    // mst_stage_drop_bases
    // mst_stage_treasures
    // mst_trade_pieces
    // mst_unit_exchanges
    // opr_coin_products
    // opr_stage_drop_additions

    // 変更前
    # テーブルのダンプ mst_avatar_frames
# ------------------------------------------------------------

// CREATE TABLE `mst_avatar_frames` (
//     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
//     `avatar_frame_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
//     `release_key` int NOT NULL DEFAULT '1',
//     PRIMARY KEY (`id`)
//   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



//   # テーブルのダンプ mst_avatars
//   # ------------------------------------------------------------

//   CREATE TABLE `mst_avatars` (
//     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
//     `avatar_icon_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
//     `release_key` int NOT NULL DEFAULT '1',
//     PRIMARY KEY (`id`)
//   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



//   # テーブルのダンプ mst_handout_groups
//   # ------------------------------------------------------------

//   CREATE TABLE `mst_handout_groups` (
//     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
//     `release_key` int NOT NULL DEFAULT '1',
//     `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
//     `group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
//     `mst_handout_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
//     PRIMARY KEY (`id`)
//   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



//   # テーブルのダンプ mst_handouts
//   # ------------------------------------------------------------

//   CREATE TABLE `mst_handouts` (
//     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
//     `release_key` int NOT NULL DEFAULT '1',
//     `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
//     `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
//     `resource_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
//     `amount` int NOT NULL,
//     PRIMARY KEY (`id`)
//   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



//   # テーブルのダンプ mst_stage_drop_additions
//   # ------------------------------------------------------------

//   CREATE TABLE `mst_stage_drop_additions` (
//     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
//     `release_key` int NOT NULL DEFAULT '1',
//     `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
//     `wave_interval` int unsigned NOT NULL DEFAULT '0',
//     `mst_handout_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
//     PRIMARY KEY (`id`)
//   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



//   # テーブルのダンプ mst_stage_drop_bases
//   # ------------------------------------------------------------

//   CREATE TABLE `mst_stage_drop_bases` (
//     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
//     `release_key` int NOT NULL DEFAULT '1',
//     `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
//     `mst_stage_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
//     `exp` int unsigned NOT NULL DEFAULT '0',
//     `coin` int unsigned NOT NULL DEFAULT '0',
//     `mst_item_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
//     `mst_item_amount` int unsigned NOT NULL DEFAULT '0',
//     PRIMARY KEY (`id`),
//     UNIQUE KEY `mst_stage_drop_bases_mst_stage_id_unique` (`mst_stage_id`)
//   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



//   # テーブルのダンプ mst_stage_treasures
//   # ------------------------------------------------------------

//   CREATE TABLE `mst_stage_treasures` (
//     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
//     `release_key` int NOT NULL DEFAULT '1',
//     `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
//     `mst_stage_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
//     `wave` int NOT NULL,
//     `mst_handout_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
//     PRIMARY KEY (`id`),
//     UNIQUE KEY `uk_mst_stage_id_and_wave` (`mst_stage_id`,`wave`)
//   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



//   # テーブルのダンプ mst_trade_pieces
//   # ------------------------------------------------------------

//   CREATE TABLE `mst_trade_pieces` (
//     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
//     `mst_unit_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
//     `trade_required_amount` int NOT NULL,
//     `trade_amount` int NOT NULL,
//     `release_key` int NOT NULL DEFAULT '1',
//     PRIMARY KEY (`id`),
//     KEY `mst_trade_pieces_mst_unit_id_index` (`mst_unit_id`)
//   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



//   # テーブルのダンプ mst_unit_exchanges
//   # ------------------------------------------------------------

//   CREATE TABLE `mst_unit_exchanges` (
//     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
//     `mst_unit_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
//     `required_mst_item_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
//     `required_amount` int NOT NULL,
//     `required_diamond_amount` int NOT NULL,
//     `release_key` int NOT NULL DEFAULT '1',
//     PRIMARY KEY (`id`)
//   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



//   # テーブルのダンプ opr_coin_products
//   # ------------------------------------------------------------

//   CREATE TABLE `opr_coin_products` (
//     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
//     `release_key` int NOT NULL DEFAULT '1',
//     `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
//     `coin_amount` int NOT NULL,
//     `required_diamond_amount` int NOT NULL,
//     `is_free` tinyint NOT NULL DEFAULT '0',
//     `start_at` timestamp NULL DEFAULT NULL,
//     `end_at` timestamp NULL DEFAULT NULL,
//     PRIMARY KEY (`id`)
//   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



//   # テーブルのダンプ opr_stage_drop_additions
//   # ------------------------------------------------------------

//   CREATE TABLE `opr_stage_drop_additions` (
//     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
//     `release_key` int NOT NULL DEFAULT '1',
//     `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
//     `wave_interval` int unsigned NOT NULL DEFAULT '0',
//     `mst_handout_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
//     `start_at` timestamp NOT NULL,
//     `end_at` timestamp NOT NULL,
//     PRIMARY KEY (`id`)
//   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('mst_avatar_frames');
        Schema::dropIfExists('mst_avatars');
        Schema::dropIfExists('mst_handout_groups');
        Schema::dropIfExists('mst_handouts');
        Schema::dropIfExists('mst_stage_drop_additions');
        Schema::dropIfExists('mst_stage_drop_bases');
        Schema::dropIfExists('mst_stage_treasures');
        Schema::dropIfExists('mst_trade_pieces');
        Schema::dropIfExists('mst_unit_exchanges');
        Schema::dropIfExists('opr_coin_products');
        Schema::dropIfExists('opr_stage_drop_additions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement(
            'CREATE TABLE `mst_avatar_frames` (
                `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `avatar_frame_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `release_key` int NOT NULL DEFAULT \'1\',
                PRIMARY KEY (`id`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
        );

        DB::statement(
            'CREATE TABLE `mst_avatars` (
                `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `avatar_icon_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `release_key` int NOT NULL DEFAULT \'1\',
                PRIMARY KEY (`id`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
        );

        DB::statement(
            'CREATE TABLE `mst_handout_groups` (
                `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `release_key` int NOT NULL DEFAULT \'1\',
                `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `mst_handout_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                PRIMARY KEY (`id`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
        );

        DB::statement(
            'CREATE TABLE `mst_handouts` (
                `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `release_key` int NOT NULL DEFAULT \'1\',
                `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `resource_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `amount` int NOT NULL,
                PRIMARY KEY (`id`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
        );

        DB::statement(
            'CREATE TABLE `mst_stage_drop_additions` (
                `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `release_key` int NOT NULL DEFAULT \'1\',
                `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `wave_interval` int unsigned NOT NULL DEFAULT \'0\',
                `mst_handout_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                PRIMARY KEY (`id`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
        );

        DB::statement(
            'CREATE TABLE `mst_stage_drop_bases` (
                `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `release_key` int NOT NULL DEFAULT \'1\',
                `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `mst_stage_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `exp` int unsigned NOT NULL DEFAULT \'0\',
                `coin` int unsigned NOT NULL DEFAULT \'0\',
                `mst_item_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `mst_item_amount` int unsigned NOT NULL DEFAULT \'0\',
                PRIMARY KEY (`id`),
                UNIQUE KEY `mst_stage_drop_bases_mst_stage_id_unique` (`mst_stage_id`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
        );

        DB::statement(
            'CREATE TABLE `mst_stage_treasures` (
                `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `release_key` int NOT NULL DEFAULT \'1\',
                `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `mst_stage_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `wave` int NOT NULL,
                `mst_handout_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_mst_stage_id_and_wave` (`mst_stage_id`,`wave`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
        );

        DB::statement(
            'CREATE TABLE `mst_trade_pieces` (
                `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `mst_unit_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `trade_required_amount` int NOT NULL,
                `trade_amount` int NOT NULL,
                `release_key` int NOT NULL DEFAULT \'1\',
                PRIMARY KEY (`id`),
                KEY `mst_trade_pieces_mst_unit_id_index` (`mst_unit_id`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
        );

        DB::statement(
            'CREATE TABLE `mst_unit_exchanges` (
                `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `mst_unit_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `required_mst_item_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `required_amount` int NOT NULL,
                `required_diamond_amount` int NOT NULL,
                `release_key` int NOT NULL DEFAULT \'1\',
                PRIMARY KEY (`id`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
        );

        DB::statement(
            'CREATE TABLE `opr_coin_products` (
                `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `release_key` int NOT NULL DEFAULT \'1\',
                `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `coin_amount` int NOT NULL,
                `required_diamond_amount` int NOT NULL,
                `is_free` tinyint NOT NULL DEFAULT \'0\',
                `start_at` timestamp NULL DEFAULT NULL,
                `end_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
        );

        DB::statement(
            'CREATE TABLE `opr_stage_drop_additions` (
                `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `release_key` int NOT NULL DEFAULT \'1\',
                `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `wave_interval` int unsigned NOT NULL DEFAULT \'0\',
                `mst_handout_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `start_at` timestamp NOT NULL,
                `end_at` timestamp NOT NULL,
                PRIMARY KEY (`id`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
        );


    }
};

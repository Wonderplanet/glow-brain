<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // 変更前
    // CREATE TABLE `mst_units` (
    //     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `fragment_mst_item_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `color` enum('Colorless','Red','Blue','Yellow','Green') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Colorless' COMMENT '属性',
    //     `role_type` enum('None','Attack','Balance','Defense','Support','Unique','Technical','Special') COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `attack_range_type` enum('Short','Middle','Long') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `unit_label` enum('DropN','DropR','DropSR','DropSSR','DropUR','PremiumN','PremiumR','PremiumSR','PremiumSSR','PremiumUR') COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `mst_series_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '作品ID',
    //     `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
    //     `rarity` enum('N','R','SR','SSR','UR') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `sort_order` int unsigned NOT NULL,
    //     `summon_cost` int unsigned NOT NULL,
    //     `summon_cool_time` int unsigned NOT NULL,
    //     `special_attack_initial_cool_time` int unsigned NOT NULL,
    //     `special_attack_cool_time` int unsigned NOT NULL,
    //     `min_hp` int unsigned NOT NULL,
    //     `max_hp` int unsigned NOT NULL,
    //     `damage_knock_back_count` int unsigned NOT NULL,
    //     `move_speed` int unsigned NOT NULL,
    //     `well_distance` double(8,2) NOT NULL,
    //     `min_attack_power` int unsigned NOT NULL,
    //     `max_attack_power` int unsigned NOT NULL,
    //     `mst_unit_ability_id1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `bounding_range_front` double(8,2) NOT NULL,
    //     `bounding_range_back` double(8,2) NOT NULL,
    //     `is_encyclopedia_special_attack_position_right` tinyint unsigned NOT NULL DEFAULT '0',
    //     `release_key` int NOT NULL DEFAULT '1',
    //     PRIMARY KEY (`id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // CREATE TABLE `mst_unit_rank_ups` (
    //     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `unit_label` enum('DropN','DropR','DropSR','DropSSR','DropUR','PremiumN','PremiumR','PremiumSR','PremiumSSR','PremiumUR') COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `rank` int NOT NULL,
    //     `amount` int NOT NULL,
    //     `require_level` int NOT NULL,
    //     `release_key` int NOT NULL DEFAULT '1',
    //     PRIMARY KEY (`id`),
    //     UNIQUE KEY `uk_unit_label_rank` (`unit_label`,`rank`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // CREATE TABLE `mst_items` (
    //     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `type` enum('CharacterFragment','RankUpMaterial','StageMedal','IdleCoinBox','IdleRankUpMaterialBox','RandomFragmentBox','SelectionFragmentBox','GachaTicket','Etc') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //     `group_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `rarity` enum('N','R','SR','SSR','UR') COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `effect_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '特定item_typeのときの効果値',
    //     `mst_series_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'mst_series.id',
    //     `sort_order` int NOT NULL DEFAULT '0',
    //     `start_date` timestamp NOT NULL,
    //     `end_date` timestamp NOT NULL,
    //     `release_key` bigint NOT NULL DEFAULT '1',
    //     `destination_opr_product_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //     PRIMARY KEY (`id`),
    //     KEY `mst_items_item_type_index` (`type`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // 変更内容

    // require_level列の後に以下追加
    // mst_unit_rank_ups	r_memory_fragment_amount	int	FALSE	0	初級メモリーフラグメントの必要数
    // mst_unit_rank_ups	sr_memory_fragment_amount	int	FALSE	0	中級メモリーフラグメントの必要数
    // mst_unit_rank_ups	ssr_memory_fragment_amount	int	FALSE	0	上級メモリーフラグメントの必要数

    // unit_label列の後に以下追加
    // mst_units	has_specific_rank_up	tinyint	FALSE	0	キャラ個別のランクアップ設定を使うかどうか	"ランクアップ時に参照するマスタテーブルをフラグで切り替える 0: mst_unit_rank_upsを参照 1: mst_unit_specific_rank_upsを参照"

    // mst_items.typeにRankUpMemoryFragmentを追加

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mst_unit_specific_rank_ups', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->bigInteger('release_key')->default(1);
            $table->string('mst_unit_id')->comment('mst_units.id');
            $table->integer('rank')->comment('Lv上限開放後のユニットのランク');
            $table->integer('amount')->comment('リミテッドメモリーの必要数');
            $table->integer('require_level')->comment('Lv上限開放に必要なユニットのレベル');
            $table->integer('r_memory_fragment_amount')->default(0)->comment('初級メモリーフラグメントの必要数');
            $table->integer('sr_memory_fragment_amount')->default(0)->comment('中級メモリーフラグメントの必要数');
            $table->integer('ssr_memory_fragment_amount')->default(0)->comment('上級メモリーフラグメントの必要数');

            $table->unique(['mst_unit_id', 'rank'], 'uk_mst_unit_id_rank');

            $table->comment('ユニット個別のランクアップ設定');
        });

        Schema::table('mst_units', function (Blueprint $table) {
            $table->tinyInteger('has_specific_rank_up')->default(0)->comment('キャラ個別のランクアップ設定を使うかどうか')->after('unit_label');
        });

        Schema::table('mst_items', function (Blueprint $table) {
            DB::statement('ALTER TABLE mst_items MODIFY COLUMN type ENUM("CharacterFragment","RankUpMaterial","StageMedal","IdleCoinBox","IdleRankUpMaterialBox","RandomFragmentBox","SelectionFragmentBox","GachaTicket","Etc","RankUpMemoryFragment")');
        });

        Schema::table('mst_unit_rank_ups', function (Blueprint $table) {
            $table->integer('ssr_memory_fragment_amount')->default(0)->comment('上級メモリーフラグメントの必要数')->after('require_level');
            $table->integer('sr_memory_fragment_amount')->default(0)->comment('中級メモリーフラグメントの必要数')->after('require_level');
            $table->integer('r_memory_fragment_amount')->default(0)->comment('初級メモリーフラグメントの必要数')->after('require_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_unit_specific_rank_ups');

        Schema::table('mst_units', function (Blueprint $table) {
            $table->dropColumn('has_specific_rank_up');
        });

        Schema::table('mst_items', function (Blueprint $table) {
            DB::statement('ALTER TABLE mst_items MODIFY COLUMN type ENUM("CharacterFragment","RankUpMaterial","StageMedal","IdleCoinBox","IdleRankUpMaterialBox","RandomFragmentBox","SelectionFragmentBox","GachaTicket","Etc")');
        });

        Schema::table('mst_unit_rank_ups', function (Blueprint $table) {
            $table->dropColumn('r_memory_fragment_amount');
            $table->dropColumn('sr_memory_fragment_amount');
            $table->dropColumn('ssr_memory_fragment_amount');
        });
    }
};

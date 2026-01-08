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
    //     `unit_label` enum('DropR','DropSR','DropSSR','DropUR','PremiumR','PremiumSR','PremiumSSR','PremiumUR','FestivalUR') COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `has_specific_rank_up` tinyint NOT NULL DEFAULT '0' COMMENT 'キャラ個別のランクアップ設定を使うかどうか',
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

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // is_encyclopedia_special_attack_position_right列の後に、mst_attack_hit_onomatopeia_group_id列を追加
        Schema::table('mst_units', function (Blueprint $table) {
            $table->string('mst_attack_hit_onomatopeia_group_id')->default('')->after('is_encyclopedia_special_attack_position_right');
        });
        Schema::create('mst_attack_hit_onomatopeia_groups', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->bigInteger('release_key')->default(1);
            $table->string('asset_key1')->default('');
            $table->string('asset_key2')->default('');
            $table->string('asset_key3')->default('');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_units', function (Blueprint $table) {
            $table->dropColumn('mst_attack_hit_onomatopeia_group_id');
        });

        Schema::dropIfExists('mst_attack_hit_onomatopeia_groups');
    }
};

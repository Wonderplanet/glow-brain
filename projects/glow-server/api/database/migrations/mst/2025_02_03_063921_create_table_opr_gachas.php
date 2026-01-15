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
    // CREATE TABLE `opr_gachas` (
    //     `id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //     `gacha_type` enum('Normal','Premium','Pickup','Free','Ticket','Festival','PaidOnly','Medal') COLLATE utf8mb4_bin DEFAULT NULL,
    //     `upper_group` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT 'None' COMMENT '天井設定区分',
    //     `enable_ad_play` tinyint(1) NOT NULL DEFAULT '0' COMMENT '広告で回せるか',
    //     `enable_add_ad_play_upper` tinyint(1) NOT NULL DEFAULT '0' COMMENT '広告で天井を動かすか',
    //     `ad_play_interval_time` int unsigned DEFAULT NULL COMMENT '広告で回すことができるインターバル時間(設定単位は分)',
    //     `multi_draw_count` int unsigned NOT NULL DEFAULT '1' COMMENT 'N連の指定',
    //     `multi_fixed_prize_count` smallint unsigned DEFAULT '0' COMMENT 'N連の確定枠数',
    //     `daily_play_limit_count` int unsigned DEFAULT NULL COMMENT '１日に回すことができる上限数',
    //     `total_play_limit_count` int unsigned DEFAULT NULL COMMENT '回すことができる上限数',
    //     `daily_ad_limit_count` int unsigned DEFAULT NULL COMMENT '1日に広告で回すことができる上限数',
    //     `total_ad_limit_count` int unsigned DEFAULT NULL COMMENT '広告で回すことができる上限数',
    //     `prize_group_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'opr_gacha_prizes.group_id',
    //     `fixed_prize_group_id` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT '確定枠(opr_gacha_prizes.group_id)',
    //     `appearance_condition` enum('Always','HasTicket') COLLATE utf8mb4_bin NOT NULL DEFAULT 'Always' COMMENT '登場条件',
    //     `start_at` timestamp NOT NULL COMMENT '開始日時',
    //     `end_at` timestamp NOT NULL COMMENT '終了日時',
    //     `display_mst_unit_id` text COLLATE utf8mb4_bin COMMENT '表示に使用するピックアップユニットIDを指定する',
    //     `display_information_id` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'ガチャ詳細用お知らせID',
    //     `gacha_priority` int NOT NULL DEFAULT '1' COMMENT 'バナー表示順',
    //     `release_key` bigint NOT NULL DEFAULT '1',
    //     `created_at` timestamp NULL DEFAULT NULL,
    //     `updated_at` timestamp NULL DEFAULT NULL,
    //     PRIMARY KEY (`id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

    /**
     * Run the migrations.
     */
    public function up(): void
    {

        // opr_gachas.gacha_typeにTutorial追加
        DB::statement('ALTER TABLE opr_gachas MODIFY COLUMN gacha_type enum("Normal","Premium","Pickup","Free","Ticket","Festival","PaidOnly","Medal","Tutorial") COLLATE utf8mb4_bin DEFAULT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // opr_gachas.gacha_typeからTutorial削除
        DB::statement('ALTER TABLE opr_gachas MODIFY COLUMN gacha_type enum("Normal","Premium","Pickup","Free","Ticket","Festival","PaidOnly","Medal") COLLATE utf8mb4_bin DEFAULT NULL');
    }
};

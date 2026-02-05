<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // TiKVストレージの都合上、不要なid_uniqueインデックスを削除
        // PRIMARY KEYを持つテーブル以外の47テーブルが対象
        $tables = [
            'usr_advent_battle_sessions',
            'usr_advent_battles',
            'usr_artwork_fragments',
            'usr_artworks',
            'usr_cheat_sessions',
            'usr_comeback_bonus_progresses',
            'usr_condition_packs',
            'usr_currency_frees',
            'usr_currency_paids',
            'usr_currency_summaries',
            'usr_device_link_passwords',
            'usr_device_link_socials',
            'usr_emblems',
            'usr_enemy_discoveries',
            'usr_gacha_uppers',
            'usr_gachas',
            'usr_idle_incentives',
            'usr_item_trades',
            'usr_items',
            'usr_jump_plus_rewards',
            'usr_mission_daily_bonuses',
            'usr_mission_event_daily_bonus_progresses',
            'usr_mission_event_daily_bonuses',
            'usr_mission_events',
            'usr_mission_limited_terms',
            'usr_mission_normals',
            'usr_mission_statuses',
            'usr_outpost_enhancements',
            'usr_outposts',
            'usr_parties',
            'usr_pvp_sessions',
            'usr_pvps',
            'usr_received_unit_encyclopedia_rewards',
            'usr_shop_items',
            'usr_shop_passes',
            'usr_stage_enhances',
            'usr_stage_events',
            'usr_stage_sessions',
            'usr_stages',
            'usr_store_allowances',
            'usr_store_infos',
            'usr_store_products',
            'usr_temporary_individual_messages',
            'usr_trade_packs',
            'usr_tutorial_gachas',
            'usr_tutorials',
            'usr_unit_summaries',
            'usr_user_buy_counts',
            'usr_user_logins',
            'usr_user_parameters',
            'usr_user_profiles',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $indexName = $tableName . '_id_unique';
                    $table->dropUnique($indexName);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ロールバック時はインデックスを再作成
        $tables = [
            'usr_advent_battle_sessions',
            'usr_advent_battles',
            'usr_artwork_fragments',
            'usr_artworks',
            'usr_cheat_sessions',
            'usr_comeback_bonus_progresses',
            'usr_condition_packs',
            'usr_currency_frees',
            'usr_currency_paids',
            'usr_currency_summaries',
            'usr_device_link_passwords',
            'usr_device_link_socials',
            'usr_emblems',
            'usr_enemy_discoveries',
            'usr_gacha_uppers',
            'usr_gachas',
            'usr_idle_incentives',
            'usr_item_trades',
            'usr_items',
            'usr_jump_plus_rewards',
            'usr_mission_daily_bonuses',
            'usr_mission_event_daily_bonus_progresses',
            'usr_mission_event_daily_bonuses',
            'usr_mission_events',
            'usr_mission_limited_terms',
            'usr_mission_normals',
            'usr_mission_statuses',
            'usr_outpost_enhancements',
            'usr_outposts',
            'usr_parties',
            'usr_pvp_sessions',
            'usr_pvps',
            'usr_received_unit_encyclopedia_rewards',
            'usr_shop_items',
            'usr_shop_passes',
            'usr_stage_enhances',
            'usr_stage_events',
            'usr_stage_sessions',
            'usr_stages',
            'usr_store_allowances',
            'usr_store_infos',
            'usr_store_products',
            'usr_temporary_individual_messages',
            'usr_trade_packs',
            'usr_tutorial_gachas',
            'usr_tutorials',
            'usr_unit_summaries',
            'usr_user_buy_counts',
            'usr_user_logins',
            'usr_user_parameters',
            'usr_user_profiles',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unique('id');
                });
            }
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * TTLを設定するテーブルの配列
     * 各テーブルに対して30日のTTLを設定
     */
    private array $tables = [
        'log_coins',
        'log_staminas',
        'log_exps',
        'log_items',
        'log_emblems',
        'log_stage_actions',
        'log_unit_grade_ups',
        'log_unit_level_ups',
        'log_unit_rank_ups',
        'log_outpost_enhancements',
        'log_advent_battle_actions',
        'log_bnid_links',
        'log_system_message_additions',
        'log_trade_shop_items',
        'log_logins',
        'log_artwork_fragments',
        'log_units',
        'log_ad_free_plays',
        'log_idle_incentive_rewards',
        'log_tutorial_actions',
        'log_user_levels',
        'log_user_profiles',
        'log_receive_message_rewards',
        'log_pvp_actions',
        'log_mission_rewards',
        'log_encyclopedia_rewards',
        'log_advent_battle_rewards',
    ];

    public function up(): void
    {
        if (!$this->shouldRun()) {
            return;
        }

        foreach ($this->tables as $table) {
            // created_atから30日でTTL設定
            DB::statement("ALTER TABLE `{$table}` TTL = `created_at` + INTERVAL 31 DAY");
            // 1日1回にする
            DB::statement("ALTER TABLE `{$table}` TTL_JOB_INTERVAL = '24h'");
            // TTLを有効化 (デフォルトONだが念のため)
            DB::statement("ALTER TABLE `{$table}` TTL_ENABLE = 'ON'");
        }
    }

    public function down(): void
    {
        if (!$this->shouldRun()) {
            return;
        }

        foreach ($this->tables as $table) {
            // TTL属性を削除
            DB::statement("ALTER TABLE `{$table}` REMOVE TTL");
        }
    }

    private function shouldRun(): bool
    {
        $result = DB::select("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = 'information_schema' AND table_name = 'cluster_info'");
        return $result[0]->count > 0;
    }
};

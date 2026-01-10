<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'mst_enemy_stage_parameters',
        'mst_koma_lines',
        'mst_quest_bonus_units',
        'opr_asset_release_controls',
        'opr_campaigns',
        'opr_campaigns_i18n',
        'opr_content_closes',
        'opr_gacha_prizes',
        'opr_in_game_notices',
        'opr_in_game_notices_i18n',
        'opr_jump_plus_reward_schedules',
        'opr_jump_plus_rewards',
        'opr_message_rewards',
        'opr_messages',
        'opr_messages_i18n',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 'mst_enemy_stage_parameters', 'mst_koma_lines', 'mst_quest_bonus_units', 'opr_asset_release_controls', 'opr_campaigns', 'opr_campaigns_i18n', 'opr_content_closes', 'opr_gacha_prizes', 'opr_in_game_notices', 'opr_in_game_notices_i18n', 'opr_jump_plus_reward_schedules', 'opr_jump_plus_rewards', 'opr_message_rewards', 'opr_messages', 'opr_messages_i18n', 'opr_quest_bonus_units'
        // 上記テーブルに対して、release_keyカラムを追加する
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->bigInteger('release_key')->default(1)->after('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('release_key');
            });
        }
    }
};

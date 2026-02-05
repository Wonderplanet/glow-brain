<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('log_stage_actions', function (Blueprint $table) {
            $table->dropColumn(['party_units', 'in_game_battle_log']);
            $table->json('discovered_enemies')->after('mst_artwork_id')->comment('発見した敵情報');
            $table->integer('clear_time_ms')->nullable()->after('mst_artwork_id')->comment('クリアタイム(ミリ秒)');
            $table->integer('score')->default(0)->after('mst_artwork_id')->comment('スコア');
            $table->integer('defeat_boss_enemy_count')->default(0)->after('mst_artwork_id')->comment('ボス敵撃破数');
            $table->integer('defeat_enemy_count')->default(0)->after('mst_artwork_id')->comment('敵撃破数');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_stage_actions', function (Blueprint $table) {
            $table->json('in_game_battle_log')->nullable()->comment('インゲームのバトルログ')->after('mst_artwork_id')->comment('インゲームのバトルログ');
            $table->json('party_units')->nullable()->comment('ユニットのステータス情報を含めたパーティ情報')->after('mst_artwork_id')->comment('ユニットのステータス情報を含めたパーティ情報');

            $table->dropColumn(['defeat_enemy_count', 'defeat_boss_enemy_count', 'score', 'clear_time_ms', 'discovered_enemies']);
        });
    }
};

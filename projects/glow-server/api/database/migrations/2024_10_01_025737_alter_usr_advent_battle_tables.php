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
        Schema::table('usr_advent_battles', function (Blueprint $table) {
            $table->tinyInteger('is_excluded_ranking')->unsigned()->default(0)->comment('ランキングから除外されているか')->after('is_ranking_reward_received');
        });
        Schema::table('usr_advent_battle_sessions', function (Blueprint $table) {
            $table->timestampTz('battle_start_at')->comment('バトル開始日時')->after('party_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_advent_battle_sessions', function (Blueprint $table) {
            $table->dropColumn('battle_start_at');
        });
        Schema::table('usr_advent_battles', function (Blueprint $table) {
            $table->dropColumn('is_excluded_ranking');
        });
    }
};

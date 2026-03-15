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
        Schema::table('usr_stage_sessions', function (Blueprint $table) {
            $table->unsignedInteger('random_seed')->default(0)->comment('クライアント抽選用ランダムシード')->after('auto_lap_count');
        });

        Schema::table('usr_advent_battle_sessions', function (Blueprint $table) {
            $table->unsignedInteger('random_seed')->default(0)->comment('クライアント抽選用ランダムシード')->after('is_challenge_ad');
        });

        Schema::table('usr_pvp_sessions', function (Blueprint $table) {
            $table->unsignedInteger('random_seed')->default(0)->comment('クライアント抽選用ランダムシード')->after('battle_start_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_stage_sessions', function (Blueprint $table) {
            $table->dropColumn('random_seed');
        });

        Schema::table('usr_advent_battle_sessions', function (Blueprint $table) {
            $table->dropColumn('random_seed');
        });

        Schema::table('usr_pvp_sessions', function (Blueprint $table) {
            $table->dropColumn('random_seed');
        });
    }
};

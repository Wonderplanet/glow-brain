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
            $table->tinyInteger('is_challenge_ad')->default(0)->after('daily_continue_ad_count')->comment('広告視聴による挑戦か');
        });

        Schema::table('usr_advent_battle_sessions', function (Blueprint $table) {
            $table->tinyInteger('is_challenge_ad')->default(0)->after('is_valid')->comment('広告視聴による挑戦か');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_stage_sessions', function (Blueprint $table) {
            $table->dropColumn('is_challenge_ad');
        });

        Schema::table('usr_advent_battle_sessions', function (Blueprint $table) {
            $table->dropColumn('is_challenge_ad');
        });
    }
};

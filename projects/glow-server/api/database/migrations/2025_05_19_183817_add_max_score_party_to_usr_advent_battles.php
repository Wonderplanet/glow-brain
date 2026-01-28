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
            $table->json('max_score_party')->nullable()->after('is_ranking_reward_received')->comment('最高スコア時のパーティ情報');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_advent_battles', function (Blueprint $table) {
            $table->dropColumn('max_score_party');
        });
    }
};
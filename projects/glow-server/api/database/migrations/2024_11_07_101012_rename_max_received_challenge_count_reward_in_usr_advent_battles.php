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
            $table->renameColumn('max_received_challenge_count_reward', 'max_received_max_score_reward');
            $table->unsignedBigInteger('max_received_max_score_reward')->default(0)->comment('受取済みの最高スコア報酬の最高スコア数値')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_advent_battles', function (Blueprint $table) {
            $table->renameColumn('max_received_max_score_reward', 'max_received_challenge_count_reward');
            $table->unsignedSmallInteger('max_received_challenge_count_reward')->default(0)->comment('受取済みの挑戦回数報酬の最大挑戦回数値')->change();
        });
    }
};

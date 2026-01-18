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
        Schema::create('usr_advent_battles', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('mst_advent_battle_id', 255)->comment('mst_advent_battles.id');
            $table->bigInteger('max_score',)->unsigned()->default(0)->comment('最高スコア');
            $table->bigInteger('total_score')->unsigned()->default(0)->comment('累計スコア');
            $table->bigInteger('challenge_count')->unsigned()->default(0)->comment('挑戦回数(リセットなし)');
            $table->smallInteger('reset_challenge_count')->unsigned()->default(0)->comment('挑戦回数(デイリーリセット対象)');
            $table->smallInteger('reset_ad_challenge_count')->unsigned()->default(0)->comment('広告視聴での挑戦回数(デイリーリセット対象)');
            $table->smallInteger('max_received_challenge_count_reward')->unsigned()->default(0)->comment('受取済みの挑戦回数報酬の最大挑戦回数値');
            $table->tinyInteger('is_ranking_reward_received')->unsigned()->default(0)->comment('順位報酬、またはランク報酬受け取り済みか');
            $table->timestampTz('latest_reset_at')->nullable()->comment('リセット日時');
            $table->timestampsTz();
            $table->primary(['usr_user_id', 'mst_advent_battle_id'], 'pk_usr_user_id_mst_advent_battle_id');
        });

        Schema::create('usr_advent_battle_sessions', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('mst_advent_battle_id', 255)->comment('mst_advent_battles.id');
            $table->tinyInteger('is_valid')->unsigned()->default(0)->comment('0:挑戦していない, 1:挑戦中');
            $table->integer('party_no')->unsigned()->default(0)->comment('挑戦パーティ番号');
            $table->timestampsTz();
            $table->primary(['usr_user_id'], 'pk_usr_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usr_advent_battle_sessions');
        Schema::dropIfExists('usr_advent_battles');
    }
};

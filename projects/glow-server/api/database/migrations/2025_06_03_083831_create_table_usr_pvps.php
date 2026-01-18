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
        $rankClassTypes = [
            'Bronze',
            'Silver',
            'Gold',
            'Platinum',
        ];

        Schema::create('usr_pvps', function (Blueprint $table) use ($rankClassTypes) {
            $table->string('id')->primary()->comment('ID');
            $table->string('usr_user_id')->comment('usr_users.id');
            $table->string('sys_pvp_season_id', 16)->comment('sys_pvp_seasons.id');
            $table->integer('score')->default(0)->comment('パーティ番号');
            $table->enum('pvp_rank_class_type', $rankClassTypes)->comment('PVPランク区分');
            $table->unsignedTinyInteger('pvp_rank_class_level')->default(0)->comment('PVPランク区分レベル');
            $table->integer('ranking')->nullable()->comment('ランキング');
            $table->unsignedInteger('daily_challenge_count')->comment('1日のアイテム消費なし挑戦可能回数');
            $table->unsignedInteger('daily_item_challenge_count')->comment('1日のアイテム消費あり挑戦可能回数');
            $table->dateTime('last_played_at')->nullable()->comment('最終プレイ日時');
            $table->json('selected_opponent_candidates')->nullable()->comment('選択した対戦相手の情報リスト');
            $table->timestampsTz();

            $table->unique(['sys_pvp_season_id', 'usr_user_id']);
            $table->comment('開催毎の個人PVP情報');
        });

        Schema::create('usr_pvp_sessions', function (Blueprint $table) {
            $table->string('id')->primary()->comment('ID');
            $table->string('usr_user_id')->comment('usr_users.id');
            $table->string('sys_pvp_season_id', 16)->comment('sys_pvp_seasons.id');
            $table->unsignedTinyInteger('party_no')->default(0)->comment('パーティ番号');
            $table->string('opponent_user_id')->nullable()->comment('対戦相手のusr_users.id');
            $table->json('opponent_pvp_status')->comment('対戦相手のPVPステータス');
            $table->integer('opponent_score')->default(0)->comment('対戦相手のスコア');
            $table->tinyInteger('is_valid')->unsigned()->default(0)->comment('0:挑戦していない, 1:挑戦中');
            $table->dateTime('start_at')->comment('セッション開始日時');
            $table->dateTime('end_at')->nullable()->comment('セッション終了日時');
            $table->timestampsTz();

            $table->unique('usr_user_id');
            $table->comment('PVPセッション情報');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usr_pvps');

        Schema::dropIfExists('usr_pvp_sessions');
    }
};

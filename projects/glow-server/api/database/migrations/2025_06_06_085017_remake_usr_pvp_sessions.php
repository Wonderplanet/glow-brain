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
        // primaryを変更するために一度テーブルを削除
        Schema::dropIfExists('usr_pvp_sessions');
        Schema::create('usr_pvp_sessions', function (Blueprint $table) {
            $table->string('usr_user_id')->primary()->comment('usr_users.id');
            $table->string('id')->comment('ID');
            $table->string('sys_pvp_season_id', 16)->comment('sys_pvp_seasons.id');
            $table->unsignedTinyInteger('party_no')->default(0)->comment('パーティ番号');
            $table->string('opponent_my_id')->nullable()->comment('対戦相手のmyId');
            $table->json('opponent_pvp_status')->comment('対戦相手のPVPステータス');
            $table->unsignedBigInteger('opponent_score')->default(0)->comment('対戦相手のスコア');
            $table->tinyInteger('is_valid')->unsigned()->default(0)->comment('0:挑戦していない, 1:挑戦中');
            $table->timestampTz('start_at')->comment('セッション開始日時');
            $table->timestampTz('end_at')->nullable()->comment('セッション終了日時');
            $table->timestampsTz();

            $table->unique('id');
            $table->comment('PVPセッション情報');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usr_pvp_sessions');

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
};

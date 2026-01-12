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
        Schema::create('log_pvp_actions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('usr_user_id')->comment('usr_users.id');
            $table->string('nginx_request_id', 255)->comment('APIリクエスト単位でNginxにて生成されるユニークID');
            $table->string('request_id', 255)->comment('APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID');
            $table->unsignedInteger('logging_no')->comment('APIリクエスト中でのログの順番');

            $table->string('sys_pvp_season_id', 16)->comment('sys_pvp_seasons.id');
            $table->string('api_path', 255)->comment('リクエストされたPVP関連のAPI');
            $table->unsignedTinyInteger('result')->default(0)->comment('PVP結果。0: 結果未確定, 1: 敗北, 2: 勝利 3: リタイア 4: 中断復帰キャンセル');

            $table->json('my_pvp_status')->comment('PVPステータス情報');
            $table->string('opponent_my_id')->comment('対戦相手id');
            $table->json('opponent_pvp_status')->comment('PVPステータス情報');

            $table->json('in_game_battle_log')->nullable()->comment('インゲームのバトルログ');

            $table->timestampsTz();
            $table->index('created_at', 'created_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_pvp_actions');
    }
};

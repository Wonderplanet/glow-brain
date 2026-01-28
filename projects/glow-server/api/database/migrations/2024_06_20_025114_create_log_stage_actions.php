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
        Schema::create('log_stage_actions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('nginx_request_id', 255)->comment('APIリクエスト単位でNginxにて生成されるユニークID');
            $table->string('request_id', 255)->comment('APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID');
            $table->unsignedInteger('logging_no')->comment('APIリクエスト中でのログの順番');

            $table->string('mst_stage_id', 255)->comment('mst_stages.id');
            $table->string('api_path', 255)->comment('リクエストされたステージ関連のAPI');
            $table->unsignedTinyInteger('result')->default(0)->comment('ステージ結果。0: 結果未確定, 1: 敗北, 2: 勝利');

            $table->text('party_units')->nullable()->comment('ユニットのステータス情報を含めたパーティ情報（シリアライズデータ）');
            $table->text('used_outpost')->nullable()->comment('使用したゲート情報（シリアライズデータ）');
            $table->text('in_game_battle_log')->nullable()->comment('インゲームのバトルログ（シリアライズデータ）');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_stage_actions');
    }
};

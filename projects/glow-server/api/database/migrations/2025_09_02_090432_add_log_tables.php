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
        Schema::create('log_mission_rewards', function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('ID');
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('nginx_request_id', 255)->comment('APIリクエスト単位でNginxにて生成されるユニークID');
            $table->string('request_id', 255)->comment('APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID');
            $table->integer('logging_no')->comment('APIリクエスト中でのログの順番');
            $table->string('mission_type', 255)->comment('ミッションタイプ');
            $table->json('received_reward')->comment('配布報酬情報(変換前情報あり)');
            $table->timestampsTz();
            $table->comment('ミッション報酬ログ');
        });
        Schema::create('log_encyclopedia_rewards', function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('ID');
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('nginx_request_id', 255)->comment('APIリクエスト単位でNginxにて生成されるユニークID');
            $table->string('request_id', 255)->comment('APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID');
            $table->integer('logging_no')->comment('APIリクエスト中でのログの順番');
            $table->json('received_reward')->comment('配布報酬情報(変換前情報あり)');
            $table->timestampsTz();
            $table->comment('図鑑報酬ログ');
        });
        Schema::create('log_advent_battle_rewards', function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('ID');
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('nginx_request_id', 255)->comment('APIリクエスト単位でNginxにて生成されるユニークID');
            $table->string('request_id', 255)->comment('APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID');
            $table->integer('logging_no')->comment('APIリクエスト中でのログの順番');
            $table->json('received_reward')->comment('配布報酬情報(変換前情報あり)');
            $table->timestampsTz();
            $table->comment('降臨バトル報酬ログ');
        });
        Schema::table('log_stage_actions', function (Blueprint $table) {
            $table->json('party_status')->after('discovered_enemies')->comment('パーティステータス情報');
        });
        Schema::dropIfExists('log_party_units');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_mission_rewards');
        Schema::dropIfExists('log_encyclopedia_rewards');
        Schema::dropIfExists('log_advent_battle_rewards');
        Schema::table('log_stage_actions', function (Blueprint $table) {
            $table->dropColumn('party_status');
        });
        Schema::create('log_party_units', function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('ID');
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('nginx_request_id', 255)->comment('APIリクエスト単位でNginxにて生成されるユニークID');
            $table->string('request_id', 255)->comment('APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID');
            $table->integer('logging_no')->comment('APIリクエスト中でのログの順番');
            $table->string('content_type', 255)->comment('インゲームコンテンツタイプ');
            $table->string('target_id', 255)->comment('インゲームコンテンツの識別子');
            $table->integer('position')->comment('パーティ内のユニットの順番');
            $table->string('mst_unit_id', 255)->comment('mst_units.id');
            $table->integer('level')->comment('レベル');
            $table->integer('rank')->comment('ランク');
            $table->integer('grade_level')->comment('グレードレベル');
            $table->timestampsTz();
        });
    }
};

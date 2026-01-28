<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('log_idle_incentive_rewards', function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('ID');
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('nginx_request_id', 255)->comment('APIリクエスト単位でNginxにて生成されるユニークID');
            $table->string('request_id', 255)->comment('APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID');
            $table->integer('logging_no')->comment('APIリクエスト中でのログの順番');
            $table->string('exec_method', 255)->comment('探索報酬の受け取り方法');
            $table->timestamp('idle_started_at')->comment('放置開始日時');
            $table->integer('elapsed_minutes')->comment('報酬計算に使用した放置時間(分)');
            $table->json('received_reward')->comment('配布報酬情報(変換前情報あり)');
            $table->timestamp('received_reward_at')->comment('探索報酬を受け取った日時');
            $table->timestampsTz();
        });
    }

    public function down()
    {
        Schema::dropIfExists('log_idle_incentive_rewards');
    }
};

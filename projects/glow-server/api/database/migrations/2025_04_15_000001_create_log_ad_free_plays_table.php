<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('log_ad_free_plays', function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('ID');
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('nginx_request_id', 255)->comment('APIリクエスト単位でNginxにて生成されるユニークID');
            $table->string('request_id', 255)->comment('APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID');
            $table->integer('logging_no')->comment('APIリクエスト中でのログの順番');
            $table->string('content_type', 255)->comment('広告視聴をしたコンテンツ');
            $table->string('target_id', 255)->comment('対象コンテンツの識別子');
            $table->timestamp('play_at')->comment('広告視聴によって無料プレイした日時');
            $table->timestampsTz();
        });
    }

    public function down()
    {
        Schema::dropIfExists('log_ad_free_plays');
    }
};

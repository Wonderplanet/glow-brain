<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('log_artwork_fragments', function (Blueprint $table) {
            $table->string('id')->primary()->comment('ID');
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('nginx_request_id', 255)->comment('APIリクエスト単位でNginxにて生成されるユニークID');
            $table->string('request_id', 255)->comment('APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID');
            $table->integer('logging_no')->comment('APIリクエスト中でのログの順番');
            $table->string('mst_artwork_fragment_id', 255)->comment('mst_artwork_fragments.id');
            $table->string('content_type', 255)->comment('原画のかけらを入手したコンテンツのタイプ');
            $table->string('target_id', 255)->comment('原画のかけらを入手したコンテンツ');
            $table->unsignedSmallInteger('is_complete_artwork')->comment('原画が完成したかどうか: 1: 原画が完成した, 0: 原画未完成');
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_artwork_fragments');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
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

    public function down()
    {
        Schema::dropIfExists('log_party_units');
    }
};

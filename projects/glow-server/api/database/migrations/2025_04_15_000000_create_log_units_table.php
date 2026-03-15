<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('log_units', function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('ID');
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('nginx_request_id', 255)->comment('APIリクエスト単位でNginxにて生成されるユニークID');
            $table->string('request_id', 255)->comment('APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID');
            $table->integer('logging_no')->comment('APIリクエスト中でのログの順番');
            $table->string('mst_unit_id', 255)->comment('mst_units.id');
            $table->integer('level')->comment('レベル');
            $table->integer('rank')->comment('ランク');
            $table->integer('grade_level')->comment('グレードレベル');
            $table->string('trigger_source', 255)->comment('経緯情報ソース');
            $table->string('trigger_value', 255)->comment('経緯情報値1');
            $table->string('trigger_value_2', 255)->comment('経緯情報値2');
            $table->string('trigger_value_3', 255)->comment('経緯情報値3');
            $table->timestampsTz();
        });
    }

    public function down()
    {
        Schema::dropIfExists('log_units');
    }
};

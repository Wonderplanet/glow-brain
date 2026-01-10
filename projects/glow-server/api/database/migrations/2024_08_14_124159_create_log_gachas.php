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
        Schema::create('log_gachas', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('nginx_request_id', 255)->comment('APIリクエスト単位でNginxにて生成されるユニークID');
            $table->string('request_id', 255)->comment('APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID');
            $table->unsignedInteger('logging_no')->comment('APIリクエスト中でのログの順番');
            $table->string('opr_gacha_id', 255)->comment('opr_gachas.id');
            $table->text('result')->comment('ガシャの排出物');
            $table->string('cost_type', 255)->comment('使用したゲート情報（シリアライズデータ）');
            $table->unsignedSmallInteger('draw_count')->comment('ガシャを引いた回数');
            $table->boolean('is_upper')->comment('0:天井に達していない 1:天井に達した');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_gachas');
    }
};

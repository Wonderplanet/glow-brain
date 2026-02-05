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
        Schema::dropIfExists('log_api_requests');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('log_api_requests', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('api_path', 255)->comment('リクエストされたAPI');
            $table->string('api_version', 255)->comment('APIバージョン。例：1.0.0');
            $table->string('client_version', 255)->comment('クライアントバージョン。例：1.0.0');
            $table->string('nginx_request_id', 255)->comment('APIリクエスト単位でNginxにて生成されるユニークID');
            $table->string('request_id', 255)->comment('APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID');
            $table->integer('requested_release_key')->nullable(false)->comment('apiリクエスト時に使用したマスタデータのリリースキー');
            $table->text('user_agent')->comment('ユーザーエージェント');
            $table->integer('os_platform')->comment('OSプラットフォーム。UserConstantのPLATFORM_XXXの値。');
            $table->string('os_version', 255)->comment('OSバージョン');
            $table->string('country_code', 255)->comment('国コード');
            $table->string('ad_id', 64)->nullable()->comment('広告ID');
            $table->json('request_body')->nullable()->comment('リクエストボディ');
            $table->timestampTz('request_at')->comment('APIリクエスト日時');
            $table->json('bank_data')->comment('BANK用JSONデータ');
            $table->timestamps();
            $table->comment('APIリクエストログ');
        });
    }
};

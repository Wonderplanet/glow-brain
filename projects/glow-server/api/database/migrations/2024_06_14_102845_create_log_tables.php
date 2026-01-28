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
            $table->string('ad_id', 10)->nullable()->comment('広告ID');
            $table->text('request_body')->comment('リクエストボディ');
            $table->timestampTz('request_at')->comment('APIリクエスト日時');
            $table->timestamps();
        });

        Schema::create('log_coins', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('nginx_request_id', 255)->comment('APIリクエスト単位でNginxにて生成されるユニークID');
            $table->string('request_id', 255)->comment('APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID');
            $table->unsignedInteger('logging_no')->comment('APIリクエスト中でのログの順番');
            $table->string('action_type', 255)->comment('Get: 獲得 Use: 消費');
            $table->unsignedInteger('amount')->default(0)->comment('変動数');
            $table->text('action_detail')->nullable()->comment('アクションの理由(シリアライズデータ)');
            $table->timestamps();
        });

        Schema::create('log_staminas', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('nginx_request_id', 255)->comment('APIリクエスト単位でNginxにて生成されるユニークID');
            $table->string('request_id', 255)->comment('APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID');
            $table->unsignedInteger('logging_no')->comment('APIリクエスト中でのログの順番');
            $table->string('action_type', 255)->comment('Get: 獲得 Use: 消費');
            $table->unsignedInteger('amount')->default(0)->comment('変動数');
            $table->text('action_detail')->nullable()->comment('アクションの理由(シリアライズデータ)');
            $table->timestamps();
        });

        Schema::create('log_exps', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('nginx_request_id', 255)->comment('APIリクエスト単位でNginxにて生成されるユニークID');
            $table->string('request_id', 255)->comment('APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID');
            $table->unsignedInteger('logging_no')->comment('APIリクエスト中でのログの順番');
            $table->string('action_type', 255)->comment('Get: 獲得 Use: 消費');
            $table->unsignedInteger('amount')->default(0)->comment('変動数');
            $table->text('action_detail')->nullable()->comment('アクションの理由(シリアライズデータ)');
            $table->timestamps();
        });

        Schema::create('log_items', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('nginx_request_id', 255)->comment('APIリクエスト単位でNginxにて生成されるユニークID');
            $table->string('request_id', 255)->comment('APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID');
            $table->unsignedInteger('logging_no')->comment('APIリクエスト中でのログの順番');
            $table->string('action_type', 255)->comment('Get: 獲得 Use: 消費');
            $table->string('mst_item_id', 255)->comment('mst_items.id');
            $table->unsignedInteger('amount')->default(0)->comment('変動数');
            $table->text('action_detail')->nullable()->comment('アクションの理由(シリアライズデータ)');
            $table->timestamps();
        });

        Schema::create('log_emblems', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('nginx_request_id', 255)->comment('APIリクエスト単位でNginxにて生成されるユニークID');
            $table->string('request_id', 255)->comment('APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID');
            $table->unsignedInteger('logging_no')->comment('APIリクエスト中でのログの順番');
            $table->string('mst_emblem_id', 255)->comment('mst_emblems.id');
            $table->unsignedInteger('amount')->default(0)->comment('変動数');
            $table->text('action_detail')->nullable()->comment('アクションの理由(シリアライズデータ)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_api_requests');
        Schema::dropIfExists('log_coins');
        Schema::dropIfExists('log_staminas');
        Schema::dropIfExists('log_exps');
        Schema::dropIfExists('log_items');
        Schema::dropIfExists('log_emblems');
    }
};

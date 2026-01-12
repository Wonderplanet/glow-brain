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
        $eventIds = [
            100, 200, 300
        ];
        Schema::create('log_banks', function (Blueprint $table) use (
            $eventIds
        ) {
            $table->string('id')->primary();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('nginx_request_id', 255)->comment('APIリクエスト単位でNginxにて生成されるユニークID');
            $table->string('request_id', 255)->comment('APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID');
            $table->unsignedInteger('logging_no')->comment('APIリクエスト中でのログの順番');
            $table->enum('event_id', $eventIds)->comment('イベントID');
            $table->string('platform_user_id', 50)->comment('プラットフォーム別識別番号');
            $table->timestampTz('user_first_created_at')->comment('ユーザー初回登録日時');
            $table->text('user_agent')->comment('ユーザーエージェント');
            $table->integer('os_platform')->comment('OSプラットフォーム。UserConstantのPLATFORM_XXXの値。');
            $table->string('os_version', 255)->comment('OSバージョン');
            $table->string('country_code', 255)->comment('国コード');
            $table->string('ad_id', 10)->nullable()->comment('広告ID');
            $table->timestampTz('request_at')->comment('APIリクエスト日時');
            $table->timestamps();
            $table->comment('BanK KPI用ログ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_banks');
    }
};

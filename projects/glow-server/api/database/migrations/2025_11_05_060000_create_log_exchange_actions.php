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
        Schema::create('log_exchange_actions', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('nginx_request_id', 255)->comment('APIリクエスト単位でNginxにて生成されるユニークID');
            $table->string('request_id', 255)->comment('APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID');
            $table->unsignedInteger('logging_no')->comment('APIリクエスト中でのログの順番');
            $table->string('mst_exchange_lineup_id', 255)->comment('mst_exchange_lineups.id');
            $table->json('costs')->comment('支払ったコスト情報');
            $table->json('rewards')->comment('獲得した報酬情報');
            $table->unsignedInteger('trade_count')->default(1)->comment('交換個数');
            $table->timestamps();
            $table->index('usr_user_id', 'idx_usr_user_id');
            $table->index('created_at', 'idx_created_at');
            $table->comment('交換所アクションログテーブル');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_exchange_actions');
    }
};

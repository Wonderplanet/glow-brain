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
        Schema::create('log_logins', function (Blueprint $table) {
            $table->string('id')->primary()->comment('主キー');
            $table->string('usr_user_id')->comment('usr_users.id');
            $table->string('nginx_request_id')->comment('APIリクエスト単位でNginxにて生成されるユニークID');
            $table->string('request_id')->comment('APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID');
            $table->integer('logging_no')->comment('APIリクエスト中でのログの順番');
            $table->integer('login_count')->comment('ログイン回数');
            $table->smallInteger('is_day_first_login')->comment('1日の最初のログインかどうかのフラグ (1: 初ログイン, 0: ログイン2回目以降)');
            $table->integer('login_day_count')->comment('ログイン日数');
            $table->integer('login_continue_day_count')->comment('連続ログイン日数');
            $table->integer('comeback_day_count')->comment('最終ログインから復帰にかかった日数');
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_logins');
    }
};

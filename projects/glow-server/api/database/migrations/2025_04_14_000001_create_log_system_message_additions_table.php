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
        Schema::create('log_system_message_additions', function (Blueprint $table) {
            $table->string('id')->primary()->comment('ID');
            $table->string('usr_user_id')->comment('usr_users.id');
            $table->string('nginx_request_id')->comment('APIリクエスト単位でNginxにて生成されるユニークID');
            $table->string('request_id')->comment('APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID');
            $table->integer('logging_no')->comment('APIリクエスト中でのログの順番');
            $table->string('trigger_source')->comment('登録経緯情報ソース');
            $table->string('trigger_value')->comment('登録経緯情報値');
            $table->json('pre_grant_reward_json')->comment('配布予定の報酬情報');
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_system_message_additions');
    }
};

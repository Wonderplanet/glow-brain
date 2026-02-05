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
        Schema::create('adm_user_deletion_operate_histories', function (Blueprint $table) {
            $table->string('id')->unique();
            $table->string('usr_user_id')->comment('usr_users.id');
            $table->unsignedSmallInteger('status')->comment('操作時のユーザーステータス 0:通常プレイ可 1:時限BAN 2:永久BAN');
            $table->string('adm_user_id')->comment('操作を行った対応者のuser_id');
            $table->json('profile_data')->nullable()->comment('削除前のプロフィールデータ');
            $table->timestampTz('operated_at')->comment('操作日時');
            $table->timestampTz('expires_at')->comment('有効期限');
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adm_user_deletion_operate_histories');
    }
};


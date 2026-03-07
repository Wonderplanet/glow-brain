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
        Schema::create('adm_user_ban_operate_histories', function (Blueprint $table) {
            $table->string('id')->unique();
            $table->string('usr_user_id')->index('usr_user_id_index')->comment('usr_users.id');
            $table->unsignedSmallInteger('ban_status')->comment('操作後のユーザーステータス 0:通常プレイ可 1:時限BAN');
            $table->string('adm_user_id')->comment('操作を行った対応者のuser_id');
            $table->text('operation_reason')->comment('操作経緯');
            $table->timestampTz('operated_at')->comment('操作日時');
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adm_user_ban_operate_histories');
    }
};


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
        Schema::create('usr_gacha_uppers', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->index('usr_user_id_index')->comment('ユーザーID');
            $table->string('upper_type', 255)->comment('天井設定区分');
            $table->integer('step_number')->unsigned()->default(1)->comment('何段階目の天井回数かの設定');
            $table->integer('count')->unsigned()->default(0)->comment('天井を保証する回数 リセット条件に合致した場合カウントは0に戻る');
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');
            $table->unique(['usr_user_id', 'upper_type', 'step_number'], 'usr_user_id_upper_type_step_number_unique');
        });

        Schema::create('usr_gachas', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->index('usr_user_id_index');
            $table->string('opr_gacha_id', 255)->comment('opr_gachas.id');
            $table->timestampTz('ad_played_at')->nullable()->default(null)->comment('広告で回した時間');
            $table->timestampTz('free_played_at')->nullable()->default(null)->comment('無料で回した時間');
            $table->integer('count')->unsigned()->default(0)->comment('ガチャを回した回数');
            $table->integer('daily_count')->unsigned()->default(0)->comment('日次でガチャを回した回数');
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');
            $table->unique(['usr_user_id', 'opr_gacha_id'], 'usr_user_id_opr_gacha_id_unique');
        });

        // 古いテーブルの削除
        Schema::dropIfExists('usr_gacha_normals');
        Schema::dropIfExists('usr_gacha_supers');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usr_gacha_uppers');
        Schema::dropIfExists('usr_gachas');

        Schema::create('usr_gacha_normals', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('usr_user_id');
            $table->string('opr_gacha_normal_id')->nullable(false);
            $table->integer('diamond_draw_count')->default(0);
            $table->integer('ticket_draw_count')->default(0);
            $table->integer('ad_draw_count')->default(0);
            $table->timestamp('ad_draw_reset_at');
            $table->timestamps();
            $table->unique(['usr_user_id', 'opr_gacha_normal_id']);
        });

        Schema::create('usr_gacha_supers', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('usr_user_id');
            $table->string('opr_gacha_super_id');
            $table->unsignedInteger('draw_count')->default(0);
            $table->timestamps();
            $table->unique(['usr_user_id', 'opr_gacha_super_id']);
        });
    }
};

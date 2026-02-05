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
        // ユーザーBOXガチャテーブル
        Schema::create('usr_box_gachas', function (Blueprint $table) {
            $table->string('id', 255);
            $table->string('usr_user_id', 255)->index('usr_user_id_index')->comment('ユーザーID');
            $table->string('mst_box_gacha_id', 255)->comment('mst_box_gachas.id');
            $table->integer('current_box_level')->unsigned()->default(1)->comment('現在の引ける箱');
            $table->integer('reset_count')->unsigned()->default(0)->comment('リセットした回数');
            $table->integer('total_draw_count')->unsigned()->default(0)->comment('全ての箱から引いた総数');
            $table->integer('draw_count')->unsigned()->default(0)->comment('現在の箱から引いた数');
            $table->json('draw_prizes')->comment('現在引いた賞品の情報json');
            $table->timestamps();
            $table->primary(['usr_user_id', 'mst_box_gacha_id'], 'usr_user_id_mst_box_gacha_id_primary');
        });

        // BOXガチャアクションログテーブル
        Schema::create('log_box_gacha_actions', function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('ULID');
            $table->string('usr_user_id', 255)->index('usr_user_id_index')->comment('usr_users.id ユーザー情報');
            $table->string('nginx_request_id', 255)->comment('APIリクエスト単位でNginxにて生成されるユニークID');
            $table->string('request_id', 255)->comment('APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID');
            $table->integer('logging_no')->unsigned()->comment('APIリクエスト中でのログの順番');
            $table->string('log_type', 255)->comment('ログタイプ Draw/Reset');
            $table->string('mst_box_gacha_id', 255)->comment('mst_box_gachas.id');
            $table->json('draw_prizes')->comment('引いた結果 mstBoxGachaPrizeId + drawCountの配列');
            $table->integer('total_draw_count')->unsigned()->nullable()->comment('引いた数 resetの場合はnull');
            $table->timestampsTz();
            $table->index('created_at', 'created_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usr_box_gachas');
        Schema::dropIfExists('log_box_gacha_actions');
    }
};

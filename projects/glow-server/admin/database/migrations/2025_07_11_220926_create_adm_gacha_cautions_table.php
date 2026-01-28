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
        Schema::create('adm_gacha_cautions', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('adm_promotion_tag_id', 255)->nullable()->comment('昇格タグID(adm_promotion_tags.id)');
            $table->json('html_json')->comment('本文のhtmlのjsonデータ');
            $table->text('memo')->comment('管理用メモ');
            $table->bigInteger('author_adm_user_id')->comment('作成者ユーザーID');
            $table->timestampsTz();

            $table->index('adm_promotion_tag_id', 'idx_tag');

            $table->comment('ガシャ注意事項');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adm_gacha_cautions');
    }
};

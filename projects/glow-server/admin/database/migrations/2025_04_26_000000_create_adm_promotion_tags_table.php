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
        Schema::create('adm_promotion_tags', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->text('description')->nullable()->comment('メモ');
            $table->timestampsTz();

            $table->comment('昇格タグの管理テーブル');
        });

        Schema::table('adm_informations', function (Blueprint $table) {
            $table->timestampTz('content_change_at')
                ->nullable(false)
                ->comment('お知らせの内容が変更された日時')
                ->after('post_notice_end_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adm_promotion_tags');

        Schema::table('adm_informations', function (Blueprint $table) {
            $table->dropColumn('content_change_at');
        });
    }
};

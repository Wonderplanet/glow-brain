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
        // opr_content_closesテーブルを削除
        Schema::dropIfExists('opr_content_closes');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ロールバック時にテーブルを再作成
        Schema::create('opr_content_closes', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->enum('content_type', ['AdventBattle'])->nullable(false);
            $table->timestampTz('start_at')->comment('クローズ開始時間');
            $table->timestampTz('end_at')->comment('クローズ終了時間');
        });
    }
};

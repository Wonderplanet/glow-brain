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
        Schema::create('adm_gacha_log_aggregation_progresses', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->enum('status', ['InProgress', 'Complete'])->comment('ステータス');
            $table->unsignedBigInteger('progress')->comment('処理済み件数');
            $table->date('target_date')->unique()->comment('ログ集計対象日');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adm_gacha_log_aggregation_progresses');
    }
};

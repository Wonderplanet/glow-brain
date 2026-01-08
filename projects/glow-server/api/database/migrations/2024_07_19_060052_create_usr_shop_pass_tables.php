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
        Schema::create('usr_shop_passes', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255);
            $table->string('mst_shop_pass_id', 255);
            $table->unsignedBigInteger('daily_reward_received_count')->default(0)->comment('毎日報酬を受け取った回数');
            $table->timestampTz('daily_latest_received_at')->nullable()->comment('毎日報酬を受け取った日時');
            $table->timestampTz('start_at')->comment('パスの開始日時');
            $table->timestampTz('end_at')->comment('パスの終了日時');
            $table->timestampsTz();
            $table->unique(['usr_user_id', 'mst_shop_pass_id'], 'usr_user_id_mst_shop_pass_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usr_shop_passes');
    }
};

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
        Schema::create('usr_stage_events', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('usr_user_id');
            $table->string('mst_stage_id');
            $table->unsignedBigInteger('clear_count')->default(0)->comment('クリア回数');
            $table->unsignedBigInteger('reset_clear_count')->default(0)->comment('リセットからのクリア回数');
            $table->unsignedBigInteger('reset_ad_challenge_count')->default(0)->comment('リセットからの広告視聴での挑戦回数');
            $table->timestampTz('latest_reset_at')->nullable()->comment('リセット日時');
            $table->timestampsTz();
            $table->unique(['usr_user_id', 'mst_stage_id'], 'uk_usr_user_id_mst_stage_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usr_stage_events');
    }
};

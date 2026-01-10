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
        Schema::create('usr_trade_packs', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('mst_pack_id', 255)->comment('mst_packs.id');
            $table->unsignedInteger('daily_trade_count')->comment('デイリー交換回数');
            $table->timestampTz('last_reset_at')->comment('最終リセット日時');
            $table->timestampsTz();
            $table->unique(['usr_user_id', 'mst_pack_id'], 'usr_user_id_mst_pack_id_unique');
            $table->comment('パックの交換管理テーブル');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usr_trade_packs');
    }
};

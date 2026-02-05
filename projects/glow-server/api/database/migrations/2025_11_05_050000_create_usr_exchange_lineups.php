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
        Schema::create('usr_exchange_lineups', function (Blueprint $table) {
            $table->string('id', 255);
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('mst_exchange_lineup_id', 255)->comment('mst_exchange_lineups.id');
            $table->string('mst_exchange_id', 255)->comment('mst_exchanges.id');
            $table->unsignedInteger('trade_count')->default(0)->comment('累計交換回数');
            $table->timestampsTz();
            $table->primary(['usr_user_id', 'mst_exchange_lineup_id', 'mst_exchange_id'], 'pk_usr_exchange_lineups');
            $table->comment('ユーザー交換履歴テーブル');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usr_exchange_lineups');
    }
};

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
        Schema::create('usr_item_trades', function (Blueprint $table) {
            $table->string('id')->unique();
            $table->string('usr_user_id')->comment('usr_users.id');
            $table->string('mst_item_id')->comment('mst_items.id');
            $table->bigInteger('trade_amount')->default(0)->comment('通算交換量（リセットなし）');
            $table->bigInteger('reset_trade_amount')->default(0)->comment('交換量（リセットあり）');
            $table->timestampTz('trade_amount_reset_at')->nullable(false)->comment('交換量をリセットした日時');
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'mst_item_id']);

            $table->comment("アイテムの交換情報管理");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usr_item_trades');
    }
};

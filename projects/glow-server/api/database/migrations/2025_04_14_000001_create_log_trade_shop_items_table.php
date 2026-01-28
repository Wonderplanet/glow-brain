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
        Schema::create('log_trade_shop_items', function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('ID');
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('nginx_request_id', 255)->comment('APIリクエスト単位でNginxにて生成されるユニークID');
            $table->string('request_id', 255)->comment('APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID');
            $table->integer('logging_no')->comment('APIリクエスト中でのログの順番');
            $table->string('mst_shop_item_id', 255)->comment('mst_shop_items.id');
            $table->integer('trade_count')->comment('交換回数');
            $table->string('cost_type', 255)->comment('消費したリソースのタイプ');
            $table->integer('cost_amount')->comment('消費したリソースの数');
            $table->json('received_reward')->comment('変換後の配布報酬情報(実際にユーザーが受け取った報酬情報)');
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_trade_shop_items');
    }
};

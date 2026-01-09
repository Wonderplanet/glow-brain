<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('opr_products_i18n', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('opr_product_id')->comment('対象のプロダクト(opr_products.id)');
            $table->enum('language', ['ja'])->comment('言語情報');
            $table->string('asset_key')->default('')->comment('アセットキー');

            $table->unique(['opr_product_id', 'language'], 'uk_opr_product_id_language');

            $table->comment('ユーザーに販売する実際の商品の多言語テーブル');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opr_products_i18n');
    }
};

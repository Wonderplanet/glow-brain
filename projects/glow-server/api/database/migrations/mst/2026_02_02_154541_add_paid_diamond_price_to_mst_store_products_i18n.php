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
     *
     * パック商品に含まれる有償通貨（ダイヤモンド/プリズム）部分の価格を
     * プラットフォーム別に格納するカラムを追加。
     *
     * Bank KPI f003の集計で使用：
     * - dataフィールド: 有償通貨部分の価格（paid_diamond_price_*）
     * - direct_dataフィールド: 有償通貨以外の部分の価格（price_* - paid_diamond_price_*）
     */
    public function up(): void
    {
        Schema::table('mst_store_products_i18n', function (Blueprint $table) {
            $table->decimal('paid_diamond_price_ios', 10, 3)->default(0)
                ->comment('商品に含まれる有償通貨部分の価格（iOS）')
                ->after('price_webstore');
            $table->decimal('paid_diamond_price_android', 10, 3)->default(0)
                ->comment('商品に含まれる有償通貨部分の価格（Android）')
                ->after('paid_diamond_price_ios');
            $table->decimal('paid_diamond_price_webstore', 10, 3)->default(0)
                ->comment('商品に含まれる有償通貨部分の価格（WebStore）')
                ->after('paid_diamond_price_android');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_store_products_i18n', function (Blueprint $table) {
            $table->dropColumn([
                'paid_diamond_price_ios',
                'paid_diamond_price_android',
                'paid_diamond_price_webstore',
            ]);
        });
    }
};

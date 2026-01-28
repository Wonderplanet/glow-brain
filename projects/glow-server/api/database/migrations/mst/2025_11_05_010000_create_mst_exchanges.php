<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Domain\Constants\Database;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mst_exchanges', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->timestampTz('start_at')->comment('開催開始日時');
            $table->timestampTz('end_at')->nullable()->comment('開催終了日時');
            $table->string('lineup_group_id', 255)->comment('ラインナップグループID');
            $table->unsignedInteger('display_order')->default(0)->comment('表示順序');
            $table->comment('交換所マスタ');
        });

        Schema::create('mst_exchanges_i18n', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('mst_exchange_id', 255)->comment('mst_exchanges.id');
            $table->string('language', 50)->default('ja')->comment('言語');
            $table->string('name', 255)->comment('交換所名');
            $table->string('banner_url', 1000)->comment('バナー画像URL');
            $table->unique(['mst_exchange_id', 'language'], 'uk_mst_exchange_id_language');
            $table->comment('交換所マスタ多言語');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_exchanges_i18n');
        Schema::dropIfExists('mst_exchanges');
    }
};

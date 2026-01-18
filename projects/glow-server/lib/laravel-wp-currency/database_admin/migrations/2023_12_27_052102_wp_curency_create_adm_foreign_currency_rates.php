<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WonderPlanet\Domain\Currency\Utils\DBUtility as CurrencyDBUtility;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(CurrencyDBUtility::getTableName('adm_foreign_currency_rates'), function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->integer('year')->unsigned()->comment('取得した年');
            $table->integer('month')->unsigned()->comment('取得した月');
            $table->string('currency_code', 16)->comment('通貨コード');
            $table->string('currency', 255)->comment('通貨');
            $table->string('currency_name', 255)->comment('通貨名');
            $table->decimal('tts', 20, 6)->comment('月末TTS');
            $table->decimal('ttb', 20, 6)->comment('月末TTB');
            $table->decimal('ttm', 20, 6)->comment('公表仲値');
            $table->timestamps();

            // unique生成
            $table->unique(['year', 'month', 'currency_code'], 'year_month_currency_code_unique');

            // index生成
            $table->index(['year', 'month'], 'year_month_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(CurrencyDBUtility::getTableName('adm_foreign_currency_rates'));
    }
};

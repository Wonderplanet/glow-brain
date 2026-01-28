<?php

declare(strict_types=1);

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
        Schema::create('adm_foreign_currency_daily_rates', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->integer('year')->unsigned()->comment('取得した年');
            $table->integer('month')->unsigned()->comment('取得した月');
            $table->integer('day')->unsigned()->comment('取得した日');
            $table->string('currency_code', 16)->comment('通貨コード');
            $table->string('currency', 255)->comment('通貨');
            $table->string('currency_name', 255)->comment('通貨名');
            $table->decimal('tts', 20, 6)->comment('TTS');
            $table->decimal('ttb', 20, 6)->comment('TTB');
            $table->decimal('ttm', 20, 6)->comment('公表仲値');
            $table->timestamps();

            // unique生成
            $table->unique(['year', 'month', 'day', 'currency_code'], 'year_month_day_currency_code_unique');

            // index生成
            $table->index(['year', 'month', 'day'], 'year_month_day_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(CurrencyDBUtility::getTableName('adm_foreign_currency_daily_rates'));
    }
};

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

        Schema::create('opr_gacha_display_units_i18n', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->string('opr_gacha_id')->default('')->comment('表示対象のガシャ(opr_gachas.id)');
            $table->string('mst_unit_id')->default('')->comment('表示するキャラ(mst_units.id)');
            $table->enum('language', ['ja'])->comment('言語情報');
            $table->integer('sort_order')->default(0)->comment('キャラの表示順(昇順)');
            $table->string('description')->default('')->comment('表示キャラごとの文言');

            // unique
            $table->unique(['opr_gacha_id', 'mst_unit_id', 'language'], 'uk_gacha_unit_language');

            //table comment
            $table->comment('ガチャ画面に表示するユニット情報の多言語情報');
        });

        Schema::dropIfExists('opr_gacha_display_units');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::dropIfExists('opr_gacha_display_units_i18n');

        Schema::create('opr_gacha_display_units', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->bigInteger('release_key')->default(1);
            $table->string('opr_gacha_id')->default('');
            $table->string('mst_unit_id')->default('');
            $table->integer('sort_order');
        });
    }
};

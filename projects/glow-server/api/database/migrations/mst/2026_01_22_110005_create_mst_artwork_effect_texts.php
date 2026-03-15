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
        Schema::create('mst_artwork_effects_i18n', function (Blueprint $table) {
            $table->string('id')->primary()->comment('ID');
            $table->string('mst_artwork_id')->comment('mst_artworks.id');
            $table->enum('language', ['ja'])->default('ja')->comment('言語');
            $table->string('grade_level1_effect_text')->comment('グレードレベル１効果テキスト');
            $table->string('grade_level2_effect_text')->comment('グレードレベル２効果テキスト');
            $table->string('grade_level3_effect_text')->comment('グレードレベル３効果テキスト');
            $table->string('grade_level4_effect_text')->comment('グレードレベル４効果テキスト');
            $table->string('grade_level5_effect_text')->comment('グレードレベル５効果テキスト');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');

            $table->unique(['mst_artwork_id', 'language'], 'mst_artwork_effect_texts_unique');
            $table->comment('原画効果テキストマスタ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_artwork_effects_i18n');
    }
};

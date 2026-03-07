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
        Schema::create('mst_artwork_effects', function (Blueprint $table) {
            $table->string('id')->primary()->comment('ID');
            $table->string('mst_artwork_id')->comment('mst_artworks.id');
            $table->string('effect_type')->comment('効果タイプ');
            $table->float('grade_level1_value')->comment('グレードレベル1の効果値');
            $table->float('grade_level2_value')->comment('グレードレベル2の効果値');
            $table->float('grade_level3_value')->comment('グレードレベル3の効果値');
            $table->float('grade_level4_value')->comment('グレードレベル4の効果値');
            $table->float('grade_level5_value')->comment('グレードレベル5の効果値');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');

            $table->index('mst_artwork_id');
            $table->comment('原画効果マスタ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_artwork_effects');
    }
};

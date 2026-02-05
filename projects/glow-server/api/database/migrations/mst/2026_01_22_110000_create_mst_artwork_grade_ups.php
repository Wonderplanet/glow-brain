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
        $rarities = ['N', 'R', 'SR', 'SSR', 'UR'];

        Schema::create('mst_artwork_grade_ups', function (Blueprint $table) use ($rarities) {
            $table->string('id')->primary()->comment('ID');
            $table->enum('rarity', $rarities)->comment('レアリティ');
            $table->integer('grade_level')->comment('原画グレード');
            $table->string('mst_series_id')->comment('mst_series.id');
            $table->string('mst_artwork_id')->nullable()->comment('mst_artworks.id');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');

            $table->index(['mst_series_id', 'rarity', 'grade_level'], 'mst_artwork_grade_ups_series_rarity_grade');
            $table->index(['mst_artwork_id', 'grade_level'], 'mst_artwork_grade_ups_artwork_grade');
            $table->comment('原画グレードアップマスタ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_artwork_grade_ups');
    }
};

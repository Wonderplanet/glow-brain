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
        Schema::create('mst_artwork_acquisition_routes', function (Blueprint $table) {
            $table->string('id')->primary()->comment('ID');
            $table->string('mst_artwork_id')->comment('mst_artworks.id');
            $table->string('content_type')->comment('コンテンツタイプ');
            $table->string('content_id')->comment('コンテンツID');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');

            $table->comment('原画入手経路マスタ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_artwork_acquisition_routes');
    }
};

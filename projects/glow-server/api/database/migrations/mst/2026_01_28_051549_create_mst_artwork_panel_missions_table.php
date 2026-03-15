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
        Schema::create('mst_artwork_panel_missions', function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('ID');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->string('mst_artwork_id', 255)->comment('mst_artworks.id');
            $table->string('mst_event_id', 255)->comment('mst_events.id');
            $table->string('initial_open_mst_artwork_fragment_id', 255)->nullable()->comment('初期開放する原画のかけら mst_artwork_fragments.id');
            $table->timestampTz('start_at')->comment('開始日時');
            $table->timestampTz('end_at')->comment('終了日時');

            $table->comment('原画パネルミッションのマスタ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_artwork_panel_missions');
    }
};

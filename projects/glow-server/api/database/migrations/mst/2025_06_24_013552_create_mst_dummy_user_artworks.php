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
        Schema::create('mst_dummy_user_artworks', function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('id');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->string('mst_dummy_user_id', 255)->comment('mst_dummy_users.id');
            $table->string('mst_artwork_id', 255)->comment('mst_artwork.id');
            $table->unique(['mst_dummy_user_id', 'mst_artwork_id'], 'mst_dummy_user_artworks_unique');
            $table->comment('ダミーユーザーの原画所持マスターテーブル');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_dummy_user_artworks');
    }
};

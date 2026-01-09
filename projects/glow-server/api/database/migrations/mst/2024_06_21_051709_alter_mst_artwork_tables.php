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
        Schema::table('mst_artworks', function (Blueprint $table) {
            $table->dropUnique('mst_artworks_mst_series_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_artworks', function (Blueprint $table) {
            $table->unique(['mst_series_id'], 'mst_artworks_mst_series_id_unique');
        });
    }
};

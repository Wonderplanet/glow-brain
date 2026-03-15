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

        Schema::table('mst_artworks', function (Blueprint $table) use ($rarities) {
            $table->enum('rarity', $rarities)->comment('レアリティ')->after('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_artworks', function (Blueprint $table) {
            $table->dropColumn('rarity');
        });
    }
};

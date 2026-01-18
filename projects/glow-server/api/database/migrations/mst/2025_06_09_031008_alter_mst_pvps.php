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
        Schema::table('mst_pvps', function (Blueprint $table) {
            $table->string('mst_in_game_id')->default('')->comment('mst_in_games.id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_pvps', function (Blueprint $table) {
            $table->dropColumn('mst_in_game_id');
        });
    }
};

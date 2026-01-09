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
        Schema::table('mst_in_games', function (Blueprint $table) {
            $table->string('mst_auto_player_sequence_set_id')->after('mst_auto_player_sequence_id')->comment('mst_auto_player_sequences.sequence_set_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_in_games', function (Blueprint $table) {
            $table->dropColumn('mst_auto_player_sequence_set_id');
        });
    }
};

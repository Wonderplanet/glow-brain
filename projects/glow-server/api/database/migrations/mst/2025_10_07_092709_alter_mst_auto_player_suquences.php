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
        Schema::table('mst_auto_player_sequences', function (Blueprint $table) {
            $table->string('sequence_set_id')->after('id')->comment('シーケンスセット用のID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_auto_player_sequences', function (Blueprint $table) {
            $table->dropColumn('sequence_set_id');
        });
    }
};

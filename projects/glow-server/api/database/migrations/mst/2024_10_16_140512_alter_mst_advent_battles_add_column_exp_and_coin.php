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
        Schema::table('mst_advent_battles', function (Blueprint $table) {
            $table->integer('exp')->unsigned()->default(0)->comment('獲得リーダーEXP')->after('display_mst_unit_id3');
        });
        Schema::table('mst_advent_battles', function (Blueprint $table) {
            $table->integer('coin')->unsigned()->default(0)->comment('獲得コイン')->after('exp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_advent_battles', function (Blueprint $table) {
            $table->dropColumn('exp');
        });
        Schema::table('mst_advent_battles', function (Blueprint $table) {
            $table->dropColumn('coin');
        });
    }
};

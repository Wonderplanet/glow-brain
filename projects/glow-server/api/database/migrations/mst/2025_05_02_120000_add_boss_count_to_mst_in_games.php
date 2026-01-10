<?php
use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = Database::MST_CONNECTION;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mst_in_games', function (Blueprint $table) {
            if (!Schema::hasColumn('mst_in_games', 'boss_count')) {
            $table->integer('boss_count')->nullable()->after('boss_mst_enemy_stage_parameter_id')->comment('ボスの出現数');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_in_games', function (Blueprint $table) {
            $table->dropColumn('boss_count');
        });
    }
};

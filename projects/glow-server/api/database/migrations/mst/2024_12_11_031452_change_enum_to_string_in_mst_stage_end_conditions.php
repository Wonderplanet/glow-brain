<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mst_stage_end_conditions', function (Blueprint $table) {
            $table->string('stage_end_type')->default('Victory')->change();
            $table->string('condition_type')->default('PlayerOutpostBreakDown')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE mst_stage_end_conditions MODIFY COLUMN stage_end_type ENUM('Victory', 'Defeat', 'Finish')");
        DB::statement("ALTER TABLE mst_stage_end_conditions MODIFY COLUMN condition_type ENUM('PlayerOutpostBreakDown', 'EnemyOutpostBreakDown', 'TimeOver', 'DefeatedEnemyCount', 'DefeatUnit')");
    }
};

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
        
        Schema::create('mst_stage_end_conditions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->bigInteger('release_key')->default(1);
            $table->string('mst_stage_id')->default('');
            $table->enum('stage_end_type', ['Victory', 'Defeat', 'Finish']);
            $table->enum('condition_type', ['PlayerOutpostBreakDown', 'EnemyOutpostBreakDown', 'TimeOver', 'DefeatedEnemyCount', 'DefeatUnit']);
            $table->string('condition_value')->default('');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
        Schema::dropIfExists('mst_stage_end_conditions');
    }
};

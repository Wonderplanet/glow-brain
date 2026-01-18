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
        Schema::create('mst_manga_animations', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('mst_stage_id');
            $table->enum('condition_type', [
                'None',
                'Start',
                'Victory',
                'EnemySummon',
                'EnemyMoveStart',
            ]);
            $table->string('condition_value');
            $table->integer('animation_start_delay');
            $table->boolean('is_pause');
            $table->boolean('can_skip');
            $table->string('asset_key');
            $table->bigInteger('release_key')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_manga_animations');
    }
};

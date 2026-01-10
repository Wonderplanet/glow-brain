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
        $resourceTypes = [
            'AllEnemiesAndOutPost',
            'AllEnemies',
            'TargetEnemy',
        ];

        Schema::table('mst_advent_battles', function (Blueprint $table) use ($resourceTypes) {
            $table->enum('score_addition_type', $resourceTypes)->default('AllEnemiesAndOutPost')->comment('スコア加算タイプ');
            $table->string('score_addition_target_mst_enemy_stage_parameter_id')->comment('TargetEnemy時の対象MstId');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_advent_battles', function (Blueprint $table) {
            $table->dropColumn('score_addition_type');
            $table->dropColumn('score_addition_target_mst_enemy_stage_parameter_id');
        });
    }
};

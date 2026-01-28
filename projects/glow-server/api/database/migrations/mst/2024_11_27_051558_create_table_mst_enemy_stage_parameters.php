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

        Schema::table('mst_enemy_stage_parameters', function (Blueprint $table) {
            $table->string('death_effect_type', 255)->default('Normal')->after('transformation_condition_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_enemy_stage_parameters', function (Blueprint $table) {
            $table->dropColumn('death_effect_type');
        });
    }
};

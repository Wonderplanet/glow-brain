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
            $table->dropColumn('transformation_start_delay');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_enemy_stage_parameters', function (Blueprint $table) {
            $table->integer('transformation_start_delay')->default(0)->after('transformation_condition_value');
        });
    }
};

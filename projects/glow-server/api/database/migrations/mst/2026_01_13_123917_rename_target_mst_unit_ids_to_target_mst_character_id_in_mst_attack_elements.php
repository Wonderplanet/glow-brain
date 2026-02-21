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
        Schema::table('mst_attack_elements', function (Blueprint $table) {
            $table->renameColumn('target_mst_series_id', 'target_mst_series_ids');
            $table->renameColumn('target_mst_unit_ids', 'target_mst_character_ids');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_attack_elements', function (Blueprint $table) {
            $table->renameColumn('target_mst_series_ids', 'target_mst_series_id');
            $table->renameColumn('target_mst_character_ids', 'target_mst_unit_ids');
        });
    }
};

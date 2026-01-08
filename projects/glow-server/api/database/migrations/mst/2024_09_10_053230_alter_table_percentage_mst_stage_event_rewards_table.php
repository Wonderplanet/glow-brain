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
        Schema::table('mst_stage_event_rewards', function (Blueprint $table) {
            // drop_percentageをpercentageへ改名
            $table->renameColumn('drop_percentage', 'percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_stage_event_rewards', function (Blueprint $table) {
            $table->renameColumn('percentage', 'drop_percentage');
        });
    }
};

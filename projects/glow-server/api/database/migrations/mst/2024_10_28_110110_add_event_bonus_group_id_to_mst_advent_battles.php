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
            $table
                ->string('event_bonus_group_id', 255)
                ->default('')
                ->after('mst_stage_rule_group_id')
                ->comment('mst_event_bonus_units.event_bonus_group_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_advent_battles', function (Blueprint $table) {
            $table->dropColumn('event_bonus_group_id');
        });
    }
};

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
        Schema::create('mst_quest_bonus_units', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('mst_quest_id')->comment('mst_quests.id');
            $table->string('mst_unit_id')->comment('mst_units.id');
            $table->float('coin_bonus_rate')->comment('コイン報酬量の上昇倍率');
            $table->timestampTz('start_at')->comment('開始日時');
            $table->timestampTz('end_at')->comment('終了日時');
            $table->unique([
                'mst_quest_id',
                'mst_unit_id'
            ], 'mst_quest_id_mst_unit_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_quest_bonus_units');
    }
};

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
        
        Schema::create('mst_event_bonus_units', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->bigInteger('release_key')->default(1);
            $table->string('mst_unit_id')->default('');
            $table->integer('bonus_percent');
            $table->string('event_bonus_group_id')->default('');
            $table->unsignedTinyInteger('is_pick_up');
        });
        Schema::create('mst_quest_event_bonus_schedules', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->bigInteger('release_key')->default(1);
            $table->string('mst_quest_id')->default('');
            $table->string('event_bonus_group_id')->default('');
            $table->timestampTz('start_at');
            $table->timestampTz('end_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
        Schema::dropIfExists('mst_event_bonus_units');
        Schema::dropIfExists('mst_quest_event_bonus_schedules');
    }
};

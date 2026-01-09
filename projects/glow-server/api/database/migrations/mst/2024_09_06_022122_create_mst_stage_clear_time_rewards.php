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
        Schema::create('mst_stage_clear_time_rewards', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('mst_stage_id', 255)->comment('mst_stages.id');
            $table->integer('upper_clear_time_ms')->unsigned()->comment('目標タイム(ミリ秒)');
            $table->enum('resource_type', ['Coin', 'FreeDiamond', 'Item', 'Emblem', 'Unit'])->comment('報酬タイプ');
            $table->string('resource_id', 255)->nullable()->comment('報酬ID');
            $table->integer('resource_amount')->unsigned()->comment('報酬数');
            $table->bigInteger('release_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_stage_clear_time_rewards');
    }
};

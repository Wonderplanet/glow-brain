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
        Schema::create('mst_advent_battle_clear_rewards', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('mst_advent_battle_id')->index()->comment('mst_advent_battle.id');
            $table->enum('reward_category', ['Always', 'FirstClear', 'Random'])->comment('報酬カテゴリー');
            $table->enum('resource_type', ['Exp', 'Coin', 'FreeDiamond', 'Item', 'Emblem', 'Unit'])->comment('報酬タイプ');
            $table->string('resource_id', 255)->nullable();
            $table->integer('resource_amount')->unsigned()->nullable();
            $table->integer('percentage')->unsigned()->default(1)->comment('出現比重');
            $table->unsignedInteger('sort_order')->comment('ソート順序');
            $table->bigInteger('release_key')->default(1);
            $table->index(['mst_advent_battle_id'], 'mst_advent_battle_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_advent_battle_clear_rewards');
    }
};

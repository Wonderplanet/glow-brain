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
        // APIでは使用しないテーブル
        Schema::create('mst_unit_encyclopedia_effects', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('mst_unit_encyclopedia_reward_id')->comment('mst_unit_encyclopedia_rewards.id');
            $table->string('effect_type')->comment('効果種別');
            $table->float('value')->comment('効果値');
            $table->bigInteger('release_key')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_unit_encyclopedia_effects');
    }
};

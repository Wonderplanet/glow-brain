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
        Schema::table('mst_stage_enhance_reward_params', function (Blueprint $table) {
            $table->dropColumn('reward_amount_multiplier');
            $table->bigInteger('coin_reward_amount')->unsigned()->comment('報酬量')->after('min_threshold_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_stage_enhance_reward_params', function (Blueprint $table) {
            $table->dropColumn('coin_reward_amount');
            $table->decimal('reward_amount_multiplier', 5, 2)->unsigned()->comment('報酬量の乗数')->after('min_threshold_score');
        });
    }
};

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
            $table->string('asset_key')->default('')->after('coin_reward_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_stage_enhance_reward_params', function (Blueprint $table) {
            $table->dropColumn('asset_key');
        });
    }
};

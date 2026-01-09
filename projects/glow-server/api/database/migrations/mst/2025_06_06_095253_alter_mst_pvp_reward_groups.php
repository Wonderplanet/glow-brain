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
        Schema::table('mst_pvp_reward_groups', function (Blueprint $table) {
            $table->dropUnique('mst_pvp_reward_groups_unique');
            $table->renameColumn('pvp_reward_category', 'reward_category');
            $table->unique(['mst_pvp_id', 'reward_category', 'condition_value'], 'mst_pvp_reward_groups_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_pvp_reward_groups', function (Blueprint $table) {
            $table->dropUnique('mst_pvp_reward_groups_unique');
            $table->renameColumn('reward_category', 'pvp_reward_category');
            $table->unique(['mst_pvp_id', 'pvp_reward_category', 'condition_value'], 'mst_pvp_reward_groups_unique');
        });
    }
};

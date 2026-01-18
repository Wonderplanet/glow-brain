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
        Schema::table('mst_pvp_bonus_points', function (Blueprint $table) {
            $table->string('threshold', 255)->nullable()->comment('しきい値')->change();
        });

        Schema::table('mst_pvp_ranks', function (Blueprint $table) {
            $table->string('asset_key')->comment('ランクアイコンアセットId');
        });

        Schema::table('mst_pvps', function (Blueprint $table) {
            $table->dropColumn('reward_group_id');
        });

        Schema::table('mst_pvp_reward_groups', function (Blueprint $table) {
            $table->string('mst_pvp_id', 16)->comment('mst_pvps.id');
            $table->unique(['mst_pvp_id', 'pvp_reward_category', 'condition_value'], 'mst_pvp_reward_groups_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_pvp_bonus_points', function (Blueprint $table) {
            $table->string('threshold', 255)->comment('しきい値')->change();
        });

        Schema::table('mst_pvp_ranks', function (Blueprint $table) {
            $table->dropColumn('asset_key');
        });

        Schema::table('mst_pvps', function (Blueprint $table) {
            $table->string('reward_group_id', 255)->comment('mst_pvp_reward_groups.id');
        });

        Schema::table('mst_pvp_reward_groups', function (Blueprint $table) {
            $table->dropColumn('mst_pvp_id');
            $table->dropUnique('mst_pvp_reward_groups_unique');
        });
    }
};

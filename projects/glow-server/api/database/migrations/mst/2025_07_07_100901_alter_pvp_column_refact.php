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
        $pvpBonusTypes = [
            'ClearTime',
            'WinUpperBonus',
            'WinSameBonus',
            'WinLowerBonus',
        ];
        Schema::table('mst_pvp_bonus_points', function (Blueprint $table) use ($pvpBonusTypes) {
            $table->enum('bonus_type', $pvpBonusTypes)->comment('PVPボーナスタイプ')->change();
        });

        Schema::table('mst_pvp_matching_score_ranges', function (Blueprint $table) {
            $table->renameColumn('higher_rank_score_upper_range', 'upper_rank_max_score');
            $table->renameColumn('higher_rank_score_lower_range', 'upper_rank_min_score');
            $table->renameColumn('same_rank_score_upper_range', 'same_rank_max_score');
            $table->renameColumn('same_rank_score_lower_range', 'same_rank_min_score');
            $table->renameColumn('lower_rank_score_upper_range', 'lower_rank_max_score');
            $table->renameColumn('lower_rank_score_lower_range', 'lower_rank_min_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $pvpBonusTypes = [
            'ClearTime',
            'WinOverBonus',
            'WinNormalBonus',
            'WinUnderBonus',
        ];
        Schema::table('mst_pvp_bonus_points', function (Blueprint $table) use ($pvpBonusTypes) {
            $table->enum('bonus_type', $pvpBonusTypes)->comment('PVPボーナスタイプ')->change();
        });
        Schema::table('mst_pvp_matching_score_ranges', function (Blueprint $table) {
            $table->renameColumn('upper_rank_max_score', 'higher_rank_score_upper_range');
            $table->renameColumn('upper_rank_min_score', 'higher_rank_score_lower_range');
            $table->renameColumn('same_rank_max_score', 'same_rank_score_upper_range');
            $table->renameColumn('same_rank_min_score', 'same_rank_score_lower_range');
            $table->renameColumn('lower_rank_max_score', 'lower_rank_score_upper_range');
            $table->renameColumn('lower_rank_min_score', 'lower_rank_score_lower_range');
        });
    }
};

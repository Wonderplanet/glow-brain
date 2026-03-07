<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE mst_items MODIFY type enum('CharacterFragment','RankUpMaterial','StageMedal','IdleCoinBox','IdleRankUpMaterialBox','RandomFragmentBox','SelectionFragmentBox','GachaTicket','Etc','RankUpMemoryFragment','GachaMedal','StaminaRecoveryPercent','StaminaRecoveryFixed','ArtworkGradeUpMaterial','ArtworkGradeUpSeriesMaterial')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE mst_items MODIFY type enum('CharacterFragment','RankUpMaterial','StageMedal','IdleCoinBox','IdleRankUpMaterialBox','RandomFragmentBox','SelectionFragmentBox','GachaTicket','Etc','RankUpMemoryFragment','GachaMedal','StaminaRecoveryPercent','StaminaRecoveryFixed')");
    }
};

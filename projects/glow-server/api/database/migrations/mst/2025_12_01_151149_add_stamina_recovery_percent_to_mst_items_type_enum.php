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
        DB::statement("ALTER TABLE mst_items MODIFY type enum('CharacterFragment','RankUpMaterial','StageMedal','IdleCoinBox','IdleRankUpMaterialBox','RandomFragmentBox','SelectionFragmentBox','GachaTicket','Etc','RankUpMemoryFragment','GachaMedal','StaminaRecoveryPercent','StaminaRecoveryFixed')");
    }

    /**
     * Reverse the migrations.
     *
     * 注意: StaminaRecoveryPercent/StaminaRecoveryFixedを持つレコードが存在する場合はエラーになる。
     * その場合は手動でデータ対応を行うこと。
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE mst_items MODIFY type enum('CharacterFragment','RankUpMaterial','StageMedal','IdleCoinBox','IdleRankUpMaterialBox','RandomFragmentBox','SelectionFragmentBox','GachaTicket','Etc','RankUpMemoryFragment','GachaMedal')");
    }
};

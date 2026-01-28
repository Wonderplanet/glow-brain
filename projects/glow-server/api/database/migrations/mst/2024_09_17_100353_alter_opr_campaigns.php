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
        $targetTypes = [
            'NormalQuest',
            'EnhanceQuest',
            'EventQuest',
            'PvP',
            'AdventBattle',
        ];
        $targetTypesString = "'" . implode("', '", $targetTypes) . "'";

        DB::statement("ALTER TABLE opr_campaigns MODIFY COLUMN target_type ENUM({$targetTypesString}) NOT NULL COMMENT 'キャンペーン対象タイプ'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $targetTypes = [
            'NormalQuest',
            'EnhanceQuest',
            'EventQuest',
            'PvP',
            'DescentBattle',
        ];
        $targetTypesString = "'" . implode("', '", $targetTypes) . "'";

        DB::statement("ALTER TABLE opr_campaigns MODIFY COLUMN target_type ENUM({$targetTypesString}) NOT NULL COMMENT 'キャンペーン対象タイプ'");
    }
};

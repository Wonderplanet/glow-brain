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
        $resourceTypes = [
            'FreeDiamond',
            'Coin',
            'Exp',
            'Stamina',
            'Unit',
            'Item',
        ];
        $resourceTypesString = "'" . implode("', '", $resourceTypes) . "'";

        DB::statement("ALTER TABLE opr_gacha_limited_prizes MODIFY COLUMN resource_type ENUM({$resourceTypesString}) NOT NULL");
        DB::statement("ALTER TABLE opr_gacha_permanent_prizes MODIFY COLUMN resource_type ENUM({$resourceTypesString}) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $resourceTypes = [
            'FreeDiamond',
            'Coin',
            'Player',
            'Stamina',
            'Unit',
            'Item',
        ];
        $resourceTypesString = "'" . implode("', '", $resourceTypes) . "'";

        DB::statement("ALTER TABLE opr_gacha_limited_prizes MODIFY COLUMN resource_type ENUM({$resourceTypesString}) NOT NULL");
        DB::statement("ALTER TABLE opr_gacha_permanent_prizes MODIFY COLUMN resource_type ENUM({$resourceTypesString}) NOT NULL");
    }
};

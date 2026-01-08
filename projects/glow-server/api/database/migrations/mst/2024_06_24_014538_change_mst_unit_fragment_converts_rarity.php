<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE mst_unit_fragment_converts CHANGE COLUMN rarity unit_label ENUM(
            'DropN',
            'DropR',
            'DropSR',
            'DropSSR',
            'PremiumN',
            'PremiumR',
            'PremiumSR',
            'PremiumSSR',
            'PremiumUR'
        ) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE mst_unit_fragment_converts CHANGE COLUMN unit_label rarity ENUM(
            'N',
            'R',
            'SR',
            'SSR',
            'UR'
        ) NOT NULL");
    }
};

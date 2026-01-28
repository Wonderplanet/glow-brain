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
        DB::statement("ALTER TABLE opr_gacha_use_resources MODIFY COLUMN cost_type ENUM('Diamond','PaidDiamond','Free','Item','Ad') COLLATE utf8mb4_bin NOT NULL DEFAULT 'Diamond';");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE opr_gacha_use_resources MODIFY COLUMN cost_type ENUM('Diamond','PaidDiamond','Free','Item','Ad','Coin') COLLATE utf8mb4_bin NOT NULL DEFAULT 'Diamond';");
    }
};

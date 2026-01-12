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
        DB::statement("ALTER TABLE mst_home_banners MODIFY COLUMN destination ENUM('None', 'Gacha', 'CreditShop', 'BasicShop', 'Event', 'Web', 'Pack', 'Pass', 'BeginnerMission', 'AdventBattle', 'Pvp') NOT NULL DEFAULT 'None' COMMENT 'タップ時の遷移先タイプ'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE mst_home_banners MODIFY COLUMN destination ENUM('None', 'Gacha', 'CreditShop', 'BasicShop', 'Event', 'Web') NOT NULL DEFAULT 'None' COMMENT 'タップ時の遷移先タイプ'");
    }
};

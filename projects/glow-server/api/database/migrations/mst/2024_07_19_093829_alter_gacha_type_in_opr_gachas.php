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
        DB::statement("ALTER TABLE opr_gachas MODIFY COLUMN gacha_type ENUM('Normal', 'Premium', 'Pickup', 'Free', 'Ticket', 'Festival', 'PaidOnly') COLLATE utf8mb4_bin NOT NULL DEFAULT 'Normal' COMMENT 'ガシャタイプ';");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE opr_gachas MODIFY COLUMN gacha_type ENUM('Normal','Premium','Pickup','Stepup','Free','Ticket','Festival') COLLATE utf8mb4_bin NOT NULL DEFAULT 'Normal' COMMENT 'ノーマルかプレミアムか';");
    }
};

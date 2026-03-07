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
        DB::statement('ALTER TABLE mst_mission_limited_terms MODIFY mission_category enum("AdventBattle","ArtworkPanel") CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT "ミッションカテゴリー";');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE mst_mission_limited_terms MODIFY mission_category enum("AdventBattle") CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT "ミッションカテゴリー";');
    }
};

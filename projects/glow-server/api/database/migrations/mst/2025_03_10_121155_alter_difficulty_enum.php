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
        DB::statement("ALTER TABLE mst_quests MODIFY COLUMN difficulty ENUM('Normal', 'Hard', 'Extra') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Normal' COMMENT '難易度'");
        DB::statement("ALTER TABLE opr_campaigns MODIFY COLUMN difficulty ENUM('Normal', 'Hard', 'Extra') COMMENT '難易度'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE mst_quests MODIFY COLUMN difficulty ENUM('Normal', 'Hard', 'VeryHard') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Normal' COMMENT '難易度'");
        DB::statement("ALTER TABLE opr_campaigns MODIFY COLUMN difficulty ENUM('Normal', 'Hard', 'VeryHard') COMMENT '難易度'");
    }
};

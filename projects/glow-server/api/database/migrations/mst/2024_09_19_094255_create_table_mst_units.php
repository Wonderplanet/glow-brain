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

        Schema::table('mst_units', function (Blueprint $table) {
            DB::statement("ALTER TABLE mst_units MODIFY COLUMN role_type ENUM('None', 'Attack', 'Balance', 'Defense', 'Support', 'Unique', 'Technical', 'Special') NOT NULL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 変更前
        // `role_type` enum('Attack','Balance','Defense','Support','Unique') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
        Schema::table('mst_units', function (Blueprint $table) {
            DB::statement("ALTER TABLE mst_units MODIFY COLUMN role_type ENUM('Attack','Balance','Defense','Support','Unique') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
        });
    }
};

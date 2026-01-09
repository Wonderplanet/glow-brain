<?php

declare(strict_types=1);

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mst_attack_elements', function (Blueprint $table) {
            $table->string('attack_type', 255)->change();
            $table->string('target', 255)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE mst_attack_elements MODIFY COLUMN attack_type ENUM('None','Direct','Deck') NOT NULL");
        DB::statement("ALTER TABLE mst_attack_elements MODIFY COLUMN target ENUM('Friend','Foe','Self') NOT NULL");
    }
};

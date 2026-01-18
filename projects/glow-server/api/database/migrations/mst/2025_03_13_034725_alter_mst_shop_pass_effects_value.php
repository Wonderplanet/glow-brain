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
        //
        DB::statement("ALTER TABLE mst_shop_pass_effects MODIFY COLUMN effect_value bigint unsigned NOT NULL default 0");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        DB::statement("ALTER TABLE mst_shop_pass_effects MODIFY COLUMN effect_value bigint unsigned default NULL");
    }
};

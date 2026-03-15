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
        //rarity
        // mst_unitはすでに更新されていたので、ここでは何もしない
        DB::statement("ALTER TABLE mst_items MODIFY COLUMN rarity ENUM('N','R','SR','SSR','UR') NOT NULL");
        DB::statement("ALTER TABLE mst_artwork_fragments MODIFY COLUMN rarity ENUM('N','R','SR','SSR','UR') NOT NULL DEFAULT 'R' AFTER drop_percentage");
        DB::statement("ALTER TABLE mst_stage_limit_statuses MODIFY COLUMN only_rarity ENUM('N','R','SR','SSR','UR') DEFAULT NULL");
        DB::statement("ALTER TABLE mst_stage_limit_statuses MODIFY COLUMN over_rarity ENUM('N','R','SR','SSR','UR') DEFAULT NULL");
        
        //unit_label
        DB::statement("ALTER TABLE mst_units MODIFY COLUMN unit_label ENUM('DropN','DropR','DropSR','DropSSR','DropUR','PremiumN','PremiumR','PremiumSR','PremiumSSR','PremiumUR') NOT NULL");
        DB::statement("ALTER TABLE mst_unit_grade_ups MODIFY COLUMN unit_label ENUM('DropN','DropR','DropSR','DropSSR','DropUR','PremiumN','PremiumR','PremiumSR','PremiumSSR','PremiumUR') NOT NULL DEFAULT 'DropR'");
        DB::statement("ALTER TABLE mst_unit_grade_coefficients MODIFY COLUMN unit_label ENUM('DropN','DropR','DropSR','DropSSR','DropUR','PremiumN','PremiumR','PremiumSR','PremiumSSR','PremiumUR') NOT NULL DEFAULT 'DropR' COMMENT 'ユニットラベル' AFTER id");
        DB::statement("ALTER TABLE mst_unit_level_ups MODIFY COLUMN unit_label ENUM('DropN','DropR','DropSR','DropSSR','DropUR','PremiumN','PremiumR','PremiumSR','PremiumSSR','PremiumUR') NOT NULL DEFAULT 'DropR'");
        DB::statement("ALTER TABLE mst_unit_rank_ups MODIFY COLUMN unit_label ENUM('DropN','DropR','DropSR','DropSSR','DropUR','PremiumN','PremiumR','PremiumSR','PremiumSSR','PremiumUR') NOT NULL");
        DB::statement("ALTER TABLE mst_unit_fragment_converts MODIFY COLUMN unit_label ENUM('DropN','DropR','DropSR','DropSSR','DropUR','PremiumN','PremiumR','PremiumSR','PremiumSSR','PremiumUR') NOT NULL");
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        //rarity
        DB::statement("ALTER TABLE mst_items MODIFY COLUMN rarity ENUM('N','R','SR','SSR') NOT NULL");
        DB::statement("ALTER TABLE mst_artwork_fragments MODIFY COLUMN rarity ENUM('N','R','SR','SSR') NOT NULL DEFAULT 'N' AFTER drop_percentage");
        DB::statement("ALTER TABLE mst_stage_limit_statuses MODIFY COLUMN only_rarity ENUM('SSR','SR','R','N') DEFAULT NULL");
        DB::statement("ALTER TABLE mst_stage_limit_statuses MODIFY COLUMN over_rarity ENUM('SSR','SR','R','N') DEFAULT NULL");

        //unit_label
        DB::statement("ALTER TABLE mst_units MODIFY COLUMN unit_label ENUM('DropN','DropR','DropSR','DropSSR','PremiumN','PremiumR','PremiumSR','PremiumSSR','PremiumUR') NOT NULL");
        DB::statement("ALTER TABLE mst_unit_grade_ups MODIFY COLUMN unit_label ENUM('DropN','DropR','DropSR','DropSSR','PremiumN','PremiumR','PremiumSR','PremiumSSR','PremiumUR') NOT NULL DEFAULT 'DropN'");
        DB::statement("ALTER TABLE mst_unit_grade_coefficients MODIFY COLUMN unit_label ENUM('DropN','DropR','DropSR','DropSSR','PremiumN','PremiumR','PremiumSR','PremiumSSR','PremiumUR') NOT NULL DEFAULT 'DropN' COMMENT 'ユニットラベル' AFTER id");
        DB::statement("ALTER TABLE mst_unit_level_ups MODIFY COLUMN unit_label ENUM('DropN','DropR','DropSR','DropSSR','PremiumN','PremiumR','PremiumSR','PremiumSSR','PremiumUR') NOT NULL DEFAULT 'DropN'");
        DB::statement("ALTER TABLE mst_unit_rank_ups MODIFY COLUMN unit_label ENUM('DropN','DropR','DropSR','DropSSR','PremiumN','PremiumR','PremiumSR','PremiumSSR','PremiumUR') NOT NULL");
        DB::statement("ALTER TABLE mst_unit_fragment_converts MODIFY COLUMN unit_label ENUM('DropN','DropR','DropSR','DropSSR','PremiumN','PremiumR','PremiumSR','PremiumSSR','PremiumUR') NOT NULL");
    }
};

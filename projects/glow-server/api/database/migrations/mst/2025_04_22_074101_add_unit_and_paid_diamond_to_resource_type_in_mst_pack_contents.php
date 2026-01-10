<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `mst_pack_contents` MODIFY COLUMN `resource_type` ENUM('FreeDiamond','Coin','Item','Unit') NOT NULL COMMENT '報酬タイプ'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `mst_pack_contents` MODIFY COLUMN `resource_type` ENUM('FreeDiamond','Coin','Item') DEFAULT NULL");
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // adm_bank_f002テーブルのplatform_idにasbを追加
        DB::statement("ALTER TABLE adm_bank_f002 MODIFY COLUMN platform_id ENUM('ios','android','pc','dmm','steam','ps4','ps5','xsx','nsw','win','asb') NOT NULL COMMENT 'プラットフォームID'");

        // adm_bank_f003テーブルのplatform_idにasbを追加
        DB::statement("ALTER TABLE adm_bank_f003 MODIFY COLUMN platform_id ENUM('ios','android','pc','dmm','steam','ps4','ps5','xsx','nsw','win','asb') NOT NULL COMMENT 'プラットフォームID'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // asbを削除して元の状態に戻す
        DB::statement("ALTER TABLE adm_bank_f002 MODIFY COLUMN platform_id ENUM('ios','android','pc','dmm','steam','ps4','ps5','xsx','nsw','win') NOT NULL COMMENT 'プラットフォームID'");

        DB::statement("ALTER TABLE adm_bank_f003 MODIFY COLUMN platform_id ENUM('ios','android','pc','dmm','steam','ps4','ps5','xsx','nsw','win') NOT NULL COMMENT 'プラットフォームID'");
    }
};

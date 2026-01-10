<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('opr_gacha_uppers', function (Blueprint $table) {
            $table->renameColumn('upper_type', 'upper_group');
        });

        DB::statement("ALTER TABLE opr_gacha_uppers CHANGE COLUMN step_number upper_type ENUM('MaxRarity', 'Pickup') COLLATE utf8mb4_bin NOT NULL DEFAULT 'MaxRarity' COMMENT '天井タイプ';");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE opr_gacha_uppers CHANGE COLUMN upper_type step_number int unsigned NOT NULL COMMENT '天井回数';");

        Schema::table('opr_gacha_uppers', function (Blueprint $table) {
            $table->renameColumn('upper_group', 'upper_type');
        });
    }
};

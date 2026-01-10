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
        Schema::table('opr_gacha_use_resources', function (Blueprint $table) {
            $table->dropUnique('opr_gacha_id_cost_type_unique');
            $table->unique(['opr_gacha_id', 'cost_type', 'draw_count'], 'opr_gacha_id_cost_type_draw_count_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opr_gacha_use_resources', function (Blueprint $table) {
            $table->dropUnique('opr_gacha_id_cost_type_draw_count_unique');
            $table->unique(['opr_gacha_id', 'cost_type'], 'opr_gacha_id_cost_type_unique');
        });
    }
};

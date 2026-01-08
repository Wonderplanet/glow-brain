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
        //
        Schema::table('mst_special_role_level_up_attack_elements', function (Blueprint $table) {
            $table->decimal('min_power_parameter', 10, 2)->after('mst_attack_element_id')->comment('スペシャルロールユニットにおけるレベル最小時の攻撃パラメータ');
            $table->decimal('max_power_parameter', 10, 2)->after('min_power_parameter')->comment('スペシャルロールユニットにおけるレベル最大時の攻撃パラメータ');
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('mst_special_role_level_up_attack_elements', function (Blueprint $table) {
            $table->dropColumn('min_power_parameter');
            $table->dropColumn('max_power_parameter');
        });
    }
};

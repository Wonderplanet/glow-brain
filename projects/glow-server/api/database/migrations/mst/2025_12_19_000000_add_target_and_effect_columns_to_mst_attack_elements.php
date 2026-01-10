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
        Schema::table('mst_attack_elements', function (Blueprint $table) {
            $table->string('target_mst_series_id')->default('')->after('target_roles')->comment('対象シリーズID');
            $table->string('target_mst_unit_ids')->default('')->after('target_mst_series_id')->comment('対象ユニットID群');
            $table->string('effect_value')->default('')->after('effect_parameter')->comment('攻撃効果値(文字列)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_attack_elements', function (Blueprint $table) {
            $table->dropColumn(['target_mst_series_id', 'target_mst_unit_ids', 'effect_value']);
        });
    }
};

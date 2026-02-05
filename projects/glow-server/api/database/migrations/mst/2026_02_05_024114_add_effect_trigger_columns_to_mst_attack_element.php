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
            $table->string('effect_trigger_roles')->default('')->after('effect_value')->comment('エフェクトトリガーロール');
            $table->string('effect_trigger_colors')->default('')->after('effect_trigger_roles')->comment('エフェクトトリガーカラー');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_attack_elements', function (Blueprint $table) {
            $table->dropColumn(['effect_trigger_roles', 'effect_trigger_colors']);
        });
    }
};

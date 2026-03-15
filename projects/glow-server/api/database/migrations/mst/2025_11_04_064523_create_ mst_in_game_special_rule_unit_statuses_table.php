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
        Schema::create('mst_in_game_special_rule_unit_statuses', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->string('group_id')->comment('グループID');
            $table->string('target_type')->comment('InGameSpecialRuleUnitStatusTargetTypeで指定する');
            $table->string('target_value')->comment('mst_units.idやロール、属性などを指定する');
            $table->string('status_parameter_type')->comment('InGameSpecialRuleUnitStatusParameterTypeで指定する');
            $table->integer('effect_value')->comment('効果値');
            
            $table->index(['group_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_in_game_special_rule_unit_statuses');
    }
};

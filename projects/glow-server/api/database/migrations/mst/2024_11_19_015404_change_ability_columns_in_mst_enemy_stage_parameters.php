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
        Schema::table('mst_enemy_stage_parameters', function (Blueprint $table) {
            $table->string('mst_unit_ability_id1')->default('')->nullable()->after('attack_combo_cycle');
            $table->dropColumn('ability1');
            $table->dropColumn('ability1_parameter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_enemy_stage_parameters', function (Blueprint $table) {
            $table->enum('ability1', ['None','SlipDamageKomaBlock','AttackPowerDownKomaBlock','GustKomaBlock','AttackPowerUpKomaBoost','WindKomaBoost','AttackPowerUpInNormalKoma','MoveSpeedUpInNormalKoma','DamageCutInNormalKoma'])->after('attack_combo_cycle');
            $table->integer('ability1_parameter')->after('ability1');
            $table->dropColumn('mst_unit_ability_id1');
        });
    }
};

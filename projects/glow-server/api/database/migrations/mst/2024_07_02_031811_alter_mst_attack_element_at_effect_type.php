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
        DB::statement("ALTER TABLE mst_attack_elements MODIFY COLUMN effect_type ENUM('None','AttackPowerUp','AttackPowerDown','DamageCut','AttackSpeedDown','MoveSpeedUp','MoveSpeedDown','SlipDamage','Fierce','SpecialAttackSeal','SlipDamageKomaBlock','AttackPowerDownKomaBlock','GustKomaBlock','AttackPowerUpKomaBoost','AttackPowerUpInNormalKoma','MoveSpeedUpInNormalKoma','DamageCutInNormalKoma','Poison','PoisonBlock')");
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE mst_attack_elements MODIFY COLUMN effect_type ENUM('None','AttackPowerUp','AttackPowerDown','DamageCut','AttackSpeedDown','MoveSpeedUp','MoveSpeedDown','SlipDamage','Fierce','SpecialAttackSeal','SlipDamageKomaBlock','AttackPowerDownKomaBlock','GustKomaBlock','AttackPowerUpKomaBoost','AttackPowerUpInNormalKoma','MoveSpeedUpInNormalKoma','DamageCutInNormalKoma')");
    }
};

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
        //MstKomaLines
        DB::statement("ALTER TABLE mst_koma_lines MODIFY COLUMN koma1_effect_type ENUM('None','AttackPowerUp','AttackPowerDown','MoveSpeedUp','SlipDamage','Tailwind','Headwind','Fierce','SpecialAttackSeal','Gust','Poison')");
        DB::statement("ALTER TABLE mst_koma_lines MODIFY COLUMN koma2_effect_type ENUM('None','AttackPowerUp','AttackPowerDown','MoveSpeedUp','SlipDamage','Tailwind','Headwind','Fierce','SpecialAttackSeal','Gust','Poison')");
        DB::statement("ALTER TABLE mst_koma_lines MODIFY COLUMN koma3_effect_type ENUM('None','AttackPowerUp','AttackPowerDown','MoveSpeedUp','SlipDamage','Tailwind','Headwind','Fierce','SpecialAttackSeal','Gust','Poison')");
        DB::statement("ALTER TABLE mst_koma_lines MODIFY COLUMN koma4_effect_type ENUM('None','AttackPowerUp','AttackPowerDown','MoveSpeedUp','SlipDamage','Tailwind','Headwind','Fierce','SpecialAttackSeal','Gust','Poison')");
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //MstKomaLines
        DB::statement("ALTER TABLE mst_koma_lines MODIFY COLUMN koma1_effect_type ENUM('None','AttackPowerUp','AttackPowerDown','MoveSpeedUp','SlipDamage','Tailwind','Headwind','Fierce','SpecialAttackSeal','Gust')");
        DB::statement("ALTER TABLE mst_koma_lines MODIFY COLUMN koma2_effect_type ENUM('None','AttackPowerUp','AttackPowerDown','MoveSpeedUp','SlipDamage','Tailwind','Headwind','Fierce','SpecialAttackSeal','Gust')");
        DB::statement("ALTER TABLE mst_koma_lines MODIFY COLUMN koma3_effect_type ENUM('None','AttackPowerUp','AttackPowerDown','MoveSpeedUp','SlipDamage','Tailwind','Headwind','Fierce','SpecialAttackSeal','Gust')");
        DB::statement("ALTER TABLE mst_koma_lines MODIFY COLUMN koma4_effect_type ENUM('None','AttackPowerUp','AttackPowerDown','MoveSpeedUp','SlipDamage','Tailwind','Headwind','Fierce','SpecialAttackSeal','Gust')");
    }
};

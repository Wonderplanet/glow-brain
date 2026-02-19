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
        
        Schema::create('mst_character_role_bonuses', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->bigInteger('release_key')->default(1);
            $table->enum('role_type', ['None', 'Attack', 'Balance', 'Defense', 'Support', 'Unique', 'Technical', 'Special']);
            $table->decimal('color_advantage_attack_bonus', 10, 2);
            $table->decimal('color_advantage_defense_bonus', 10, 2);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
        Schema::dropIfExists('mst_character_role_bonuses');
    }
};

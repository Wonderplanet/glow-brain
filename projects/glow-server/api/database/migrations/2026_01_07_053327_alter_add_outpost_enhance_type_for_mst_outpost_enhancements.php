<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mst_outpost_enhancements', function (Blueprint $table) {
            $table->enum('outpost_enhancement_type', [
                'LeaderPointSpeed',
                'LeaderPointLimit',
                'OutpostHp',
                'SummonInterval',
                'LeaderPointUp',
                'RushChargeSpeed',
                'PartyArtworkCostCapacity',
            ])->change()->comment('強化タイプ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_outpost_enhancements', function (Blueprint $table) {
            $table->enum('outpost_enhancement_type', [
                'LeaderPointSpeed',
                'LeaderPointLimit',
                'OutpostHp',
                'SummonInterval',
                'LeaderPointUp',
                'RushChargeSpeed',
            ])->change()->comment('強化タイプ');
        });
    }
};

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
        Schema::table('opr_gacha_rarities_weights', function (Blueprint $table) {
            $table->renameColumn('normal_rarity', 'n_rarity');
            $table->renameColumn('good_rarity', 'r_rarity');
            $table->renameColumn('better_rarity', 'sr_rarity');
            $table->renameColumn('excellent_rarity', 'ssr_rarity');
            $table->renameColumn('epic_rarity', 'ur_rarity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opr_gacha_rarities_weights', function (Blueprint $table) {
            $table->renameColumn('n_rarity', 'normal_rarity');
            $table->renameColumn('r_rarity', 'good_rarity');
            $table->renameColumn('sr_rarity', 'better_rarity');
            $table->renameColumn('ssr_rarity', 'excellent_rarity');
            $table->renameColumn('ur_rarity', 'epic_rarity');
        });
    }
};

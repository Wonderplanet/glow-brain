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
        Schema::table('opr_stepup_gacha_steps', function (Blueprint $table) {
            $table->dropColumn('fixed_prize_rarity_threshold_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opr_stepup_gacha_steps', function (Blueprint $table) {
            $table->enum('fixed_prize_rarity_threshold_type', ['N','R','SR','SSR','UR'])
                ->nullable()
                ->comment('確定枠レアリティ条件')
                ->after('fixed_prize_count');
        });
    }
};

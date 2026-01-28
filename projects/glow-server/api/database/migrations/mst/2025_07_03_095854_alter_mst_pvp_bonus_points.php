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
        Schema::table('mst_pvp_bonus_points', function (Blueprint $table) {
            $table->renameColumn('threshold', 'condition_value');
        });

        Schema::table('mst_pvp_dummies', function (Blueprint $table) {
            $table->dropColumn('score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_pvp_bonus_points', function (Blueprint $table) {
            $table->renameColumn('condition_value', 'threshold');
        });

        Schema::table('mst_pvp_dummies', function (Blueprint $table) {
            $table->unsignedInteger('score')->default(0)->comment('PVPスコア');
            $table->index('score');
        });
    }
};

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
        Schema::table('mst_pvp_ranks', function (Blueprint $table) {
            $table->unsignedInteger('lose_sub_point')->default(0)->comment('敗北時のスコア減算値')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_pvp_ranks', function (Blueprint $table) {
            $table->integer('lose_sub_point')->default(0)->comment('敗北時のスコア減算値')->change();
        });
    }
};

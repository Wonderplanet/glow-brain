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
        //
        Schema::table('mst_enemy_characters', function (Blueprint $table) {
            $table->primary(['id'],'PRIMARY');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('mst_enemy_characters', function (Blueprint $table) {
            $table->dropPrimary('PRIMARY');
        });
    }
};

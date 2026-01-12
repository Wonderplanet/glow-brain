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
        Schema::rename('opr_gacha_permanent_prizes', 'opr_gacha_normal_prizes');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('opr_gacha_normal_prizes', 'opr_gacha_permanent_prizes');
    }
};

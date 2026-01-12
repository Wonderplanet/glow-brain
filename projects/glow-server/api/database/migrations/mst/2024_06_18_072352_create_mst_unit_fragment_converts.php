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
        Schema::create('mst_unit_fragment_converts', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->enum('rarity', [
                'N',
                'R',
                'SR',
                'SSR',
                'UR',
            ])->unique();
            $table->unsignedInteger('convert_amount');
            $table->bigInteger('release_key')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_unit_fragment_converts');
    }
};

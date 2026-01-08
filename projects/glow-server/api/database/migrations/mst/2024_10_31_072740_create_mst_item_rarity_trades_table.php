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
        Schema::create('mst_item_rarity_trades', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->bigInteger('release_key')->default(1);
            $table->enum('rarity', ['N', 'R', 'SR', 'SSR', 'UR'])->unique()->comment('レアリティ');
            $table->unsignedInteger('cost_amount')->default(1)->comment('交換元アイテムの必要消費数');
            $table->unsignedInteger('max_tradable_amount')->nullable()->comment('交換上限個数。null: 交換上限なし');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_item_rarity_trades');
    }
};

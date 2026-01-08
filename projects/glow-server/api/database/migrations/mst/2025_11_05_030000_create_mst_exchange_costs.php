<?php

declare(strict_types=1);

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
        Schema::create('mst_exchange_costs', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('mst_exchange_lineup_id', 255)->comment('mst_exchange_lineups.id');
            $table->enum('cost_type', ['Coin', 'Item'])->comment('コストタイプ');
            $table->string('cost_id', 255)->nullable()->comment('コストID');
            $table->unsignedInteger('cost_amount')->comment('必要数量');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->index('mst_exchange_lineup_id', 'idx_mst_exchange_lineup_id');
            $table->comment('交換コストマスタ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_exchange_costs');
    }
};

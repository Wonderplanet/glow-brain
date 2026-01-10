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
        Schema::create('mst_exchange_rewards', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('mst_exchange_lineup_id', 255)->comment('mst_exchange_lineups.id');
            $table->enum(
                'resource_type',
                ['Coin', 'FreeDiamond', 'Item', 'Emblem', 'Unit', 'Artwork']
            )->comment('報酬タイプ');
            $table->string('resource_id', 255)->nullable()->comment('報酬ID');
            $table->unsignedInteger('resource_amount')->comment('報酬数量');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->index('mst_exchange_lineup_id', 'idx_mst_exchange_lineup_id');
            $table->comment('交換報酬マスタ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_exchange_rewards');
    }
};

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

        Schema::table('mst_item_rarity_trades', function (Blueprint $table) {
            $table->enum('reset_type', ['None', 'Daily', 'Weekly', 'Monthly'])->default('None')->comment('リセット期間')->after('cost_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_item_rarity_trades', function (Blueprint $table) {
            $table->dropColumn('reset_type');
        });
    }
};

<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    protected $connection = Database::MST_CONNECTION;

    // クライアント側でのパースエラー回避のためにbase_rank_up_material_amount列を復活

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mst_idle_incentive_rewards', function (Blueprint $table) {
            $table->decimal('base_rank_up_material_amount', 10, 2)
                ->default(1.00)
                ->comment('リミテッドメモリーのベース獲得量')
                ->after('base_exp_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_idle_incentive_rewards', function (Blueprint $table) {
            $table->dropColumn('base_rank_up_material_amount');
        });
    }
};

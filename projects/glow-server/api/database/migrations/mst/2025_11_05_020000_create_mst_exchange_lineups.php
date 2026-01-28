<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Domain\Constants\Database;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mst_exchange_lineups', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->string('group_id', 255)->comment('グループID');
            $table->unsignedInteger('tradable_count')->nullable()->comment('交換上限数（null=無制限）');
            $table->unsignedInteger('display_order')->default(0)->comment('表示順序');
            $table->index('group_id', 'idx_group_id');
            $table->comment('交換ラインナップマスタ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_exchange_lineups');
    }
};

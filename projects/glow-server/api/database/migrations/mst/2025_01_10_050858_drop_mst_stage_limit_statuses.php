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
        Schema::dropIfExists('mst_stage_limit_statuses');
        Schema::table('mst_stages', function (Blueprint $table) {
            $table->dropColumn('mst_stage_limit_status_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('mst_stage_limit_statuses', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->bigInteger('release_key')->default(1);
            $table->enum('only_rarity', ['N', 'R', 'SR', 'SSR', 'UR'])->nullable();
            $table->enum('over_rarity', ['N', 'R', 'SR', 'SSR', 'UR'])->nullable();
            $table->integer('over_summon_cost')->nullable();
            $table->integer('under_summon_cost')->nullable();
            $table->string('mst_series_ids');
        });
        Schema::table('mst_stages', function (Blueprint $table) {
            $table->string('mst_stage_limit_status_id')->default('');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('usr_idle_incentives', function (Blueprint $table) {
            $table->string('reward_mst_stage_id', 255)->nullable()->after('ad_quick_receive_count')->comment('探索報酬を決めるステージID(mst_stages.id)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_idle_incentives', function (Blueprint $table) {
            $table->dropColumn('reward_mst_stage_id');
        });
    }
};

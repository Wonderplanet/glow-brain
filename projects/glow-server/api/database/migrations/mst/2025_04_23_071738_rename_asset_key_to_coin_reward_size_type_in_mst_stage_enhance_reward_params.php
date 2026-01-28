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
        Schema::table('mst_stage_enhance_reward_params', function (Blueprint $table) {
            $table->renameColumn('asset_key', 'coin_reward_size_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_stage_enhance_reward_params', function (Blueprint $table) {
            $table->renameColumn('coin_reward_size_type', 'asset_key');
        });
    }
};

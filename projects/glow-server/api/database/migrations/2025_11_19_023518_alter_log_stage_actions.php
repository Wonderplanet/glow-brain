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
        Schema::table('log_stage_actions', function (Blueprint $table) {
            $table->unsignedInteger('auto_lap_count')->comment('スタミナブースト周回指定')->after('party_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_stage_actions', function (Blueprint $table) {
            $table->dropColumn('auto_lap_count');
        });
    }
};

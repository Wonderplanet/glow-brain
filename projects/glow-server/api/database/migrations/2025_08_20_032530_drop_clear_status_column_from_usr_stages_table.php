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
        Schema::table('usr_stages', function (Blueprint $table) {
            $table->dropColumn('clear_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_stages', function (Blueprint $table) {
            $table->tinyInteger('clear_status')->default(0)->after('mst_stage_id');
        });
    }
};

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
        Schema::table('log_exchange_actions', function (Blueprint $table) {
            $table->string('mst_exchange_id', 255)->after('mst_exchange_lineup_id')->comment('mst_exchanges.id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_exchange_actions', function (Blueprint $table) {
            $table->dropColumn('mst_exchange_id');
        });
    }
};

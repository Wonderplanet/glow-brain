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
        Schema::table('usr_exchange_lineups', function (Blueprint $table) {
            $table->timestampTz('reset_at')->after('trade_count')->comment('最終リセット日時');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_exchange_lineups', function (Blueprint $table) {
            $table->dropColumn('reset_at');
        });
    }
};

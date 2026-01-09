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
        Schema::table('log_app_store_refunds', function (Blueprint $table) {
            $table->index('transaction_id', 'transaction_id_index');
            $table->index('refunded_at', 'refunded_at_index');
        });
        Schema::table('log_google_play_refunds', function (Blueprint $table) {
            $table->index('transaction_id', 'transaction_id_index');
            $table->index('refunded_at', 'refunded_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_app_store_refunds', function (Blueprint $table) {
            $table->dropIndex('transaction_id_index');
            $table->dropIndex('refunded_at_index');
        });
        Schema::table('log_google_play_refunds', function (Blueprint $table) {
            $table->dropIndex('transaction_id_index');
            $table->dropIndex('refunded_at_index');
        });
    }
};

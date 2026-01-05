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
        Schema::create('log_app_store_refunds', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('transaction_id', 255)->comment('課金のトランザクションID');
            $table->string('price', 255)->nullable()->comment('返金金額');
            $table->timestampTz('refunded_at')->comment('返金日時');
            $table->text('signed_payload')->comment('署名付きの返金通知');
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_app_store_refunds');
    }
};

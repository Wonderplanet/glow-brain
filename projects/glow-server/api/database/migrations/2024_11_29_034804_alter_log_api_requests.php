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
        Schema::table('log_api_requests', function (Blueprint $table) {
            $table->json('bank_data')->comment('BANK用JSONデータ')->after('request_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_api_requests', function (Blueprint $table) {
            $table->dropColumn('bank_data');
        });
    }
};

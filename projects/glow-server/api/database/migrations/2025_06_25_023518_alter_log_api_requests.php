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
            $table->string('ad_id', 64)->nullable()->comment('広告ID')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_api_requests', function (Blueprint $table) {
            $table->string('ad_id', 10)->nullable()->comment('広告ID')->change();
        });
    }
};

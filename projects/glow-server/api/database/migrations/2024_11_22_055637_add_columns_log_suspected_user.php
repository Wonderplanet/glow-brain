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
        Schema::table('log_suspected_users', function (Blueprint $table) {
            $table->timestampTz('suspected_at')->after('detail')->comment('チート疑いされた日時');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_suspected_users', function (Blueprint $table) {
            $table->dropColumn('suspected_at');
        });
    }
};

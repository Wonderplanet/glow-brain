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
        Schema::table('mng_asset_releases', function (Blueprint $table) {
            $table->timestampTz('start_at')->nullable()->default(null)->comment('開始日時')->after('description');
        });

        Schema::table('mng_master_releases', function (Blueprint $table) {
            $table->timestampTz('start_at')->nullable()->default(null)->comment('開始日時')->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mng_asset_releases', function (Blueprint $table) {
            $table->dropColumn('start_at');
        });

        Schema::table('mng_master_releases', function (Blueprint $table) {
            $table->dropColumn('start_at');
        });
    }
};

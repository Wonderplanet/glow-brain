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
        Schema::table('opr_master_release_versions', function (Blueprint $table) {
            $table->rename('mng_master_release_versions');
        });
        Schema::table('opr_master_releases', function (Blueprint $table) {
            $table->rename('mng_master_releases');
        });
        Schema::table('opr_asset_release_versions', function (Blueprint $table) {
            $table->rename('mng_asset_release_versions');
        });
        Schema::table('opr_asset_releases', function (Blueprint $table) {
            $table->rename('mng_asset_releases');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mng_master_release_versions', function (Blueprint $table) {
            $table->rename('opr_master_release_versions');
        });
        Schema::table('mng_master_releases', function (Blueprint $table) {
            $table->rename('opr_master_releases');
        });
        Schema::table('mng_asset_release_versions', function (Blueprint $table) {
            $table->rename('opr_asset_release_versions');
        });
        Schema::table('mng_asset_releases', function (Blueprint $table) {
            $table->rename('opr_asset_releases');
        });
    }
};

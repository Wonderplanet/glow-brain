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
        Schema::table('adm_asset_release_version_statuses', function (Blueprint $table) {
            $table->renameColumn('opr_asset_release_version_id', 'mng_asset_release_version_id');
        });
        Schema::table('adm_asset_import_histories', function (Blueprint $table) {
            $table->renameColumn('opr_asset_release_version_id', 'mng_asset_release_version_id');
        });
        Schema::table('adm_master_release_version_statuses', function (Blueprint $table) {
            $table->renameColumn('opr_master_release_version_id', 'mng_master_release_version_id');
        });
        Schema::table('adm_master_import_history_versions', function (Blueprint $table) {
            $table->renameColumn('opr_master_release_version_id', 'mng_master_release_version_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adm_asset_release_version_statuses', function (Blueprint $table) {
            $table->renameColumn('mng_asset_release_version_id', 'opr_asset_release_version_id');
        });
        Schema::table('adm_asset_import_histories', function (Blueprint $table) {
            $table->renameColumn('mng_asset_release_version_id', 'opr_asset_release_version_id');
        });
        Schema::table('adm_master_release_version_statuses', function (Blueprint $table) {
            $table->renameColumn('mng_master_release_version_id', 'opr_master_release_version_id');
        });
        Schema::table('adm_master_import_history_versions', function (Blueprint $table) {
            $table->renameColumn('mng_master_release_version_id', 'opr_master_release_version_id');
        });
    }
};

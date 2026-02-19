<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // mng_master_releasesにcreated_at、updated_atを追加
        Schema::table('mng_master_releases', function (Blueprint $table) {
            $table->timestampsTz();
        });
        // mng_master_release_versionsにcreated_at、updated_atを追加
        Schema::table('mng_master_release_versions', function (Blueprint $table) {
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // mng_master_releasesからcreated_at、updated_atを削除
        Schema::table('mng_master_releases', function (Blueprint $table) {
            $table->dropTimestampsTz();
        });
        // mng_master_release_versionsからcreated_at、updated_atを削除
        Schema::table('mng_master_release_versions', function (Blueprint $table) {
            $table->dropTimestampsTz();
        });
    }
};

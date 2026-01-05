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
        Schema::table('mng_master_release_versions', function (Blueprint $table) {
            $table->renameColumn('master_scheme_version', 'master_schema_version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mng_master_release_versions', function (Blueprint $table) {
            $table->renameColumn('master_schema_version', 'master_scheme_version');
        });
    }
};

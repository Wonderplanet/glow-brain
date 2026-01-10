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
        Schema::table('mng_master_releases', function (Blueprint $table) {
            $table->string('client_compatibility_version', 255)
                ->comment('クライアント互換性バージョン')
                ->after('target_release_version_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mng_master_releases', function (Blueprint $table) {
            $table->dropColumn('client_compatibility_version');
        });
    }
};

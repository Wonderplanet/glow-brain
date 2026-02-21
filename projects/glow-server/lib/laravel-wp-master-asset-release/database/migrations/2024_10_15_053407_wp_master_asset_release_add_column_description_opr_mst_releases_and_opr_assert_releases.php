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
        Schema::table('opr_master_releases', function (Blueprint $table) {
            $table->text('description')
                ->comment('メモ欄')
                ->nullable()
                ->after('target_release_version_id');
        });
        Schema::table('opr_asset_releases', function (Blueprint $table) {
            $table->text('description')
                ->comment('メモ欄')
                ->nullable()
                ->after('target_release_version_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opr_master_releases', function (Blueprint $table) {
            $table->dropColumn('description');
        });
        Schema::table('opr_asset_releases', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};

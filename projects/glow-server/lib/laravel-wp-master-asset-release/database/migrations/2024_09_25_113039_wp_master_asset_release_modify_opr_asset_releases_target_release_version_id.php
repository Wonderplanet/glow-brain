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
        Schema::table('opr_asset_releases', function (Blueprint $table) {
            $table->boolean('enabled')->default(false)->comment('リリース状態')->change();
            $table->string('target_release_version_id', 255)->nullable()->comment('opr_asset_release_versions.id')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opr_asset_releases', function (Blueprint $table) {
            $table->boolean('enabled')->comment('リリース状態')->change();
            $table->string('target_release_version_id', 255)->change();
        });
    }
};

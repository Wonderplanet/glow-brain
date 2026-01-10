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
            $table->dropIndex('enabled_index');
            $table->index(['platform', 'enabled'], 'platform_enabled_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opr_asset_releases', function (Blueprint $table) {
            $table->dropIndex('platform_enabled_index');
            $table->index('enabled', 'enabled_index');
        });
    }
};

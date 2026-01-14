<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // banner_pathをasset_keyに改名

        Schema::table('mst_home_banners', function (Blueprint $table) {
            $table->renameColumn('banner_path', 'asset_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_home_banners', function (Blueprint $table) {
            $table->renameColumn('asset_key', 'banner_path');
        });
    }
};

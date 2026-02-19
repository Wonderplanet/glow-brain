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
        Schema::table('mst_stages', function (Blueprint $table) {
            $table->string('loop_background_asset_key')->default('')->after('bgm_asset_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_stages', function (Blueprint $table) {
            $table->dropColumn('loop_background_asset_key');
        });
    }
};

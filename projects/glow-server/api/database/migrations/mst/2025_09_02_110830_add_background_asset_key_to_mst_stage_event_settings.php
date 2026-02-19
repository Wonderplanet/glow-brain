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
        Schema::table('mst_stage_event_settings', function (Blueprint $table) {
            $table->string('background_asset_key')->nullable()->after('ad_challenge_count')->comment('背景');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_stage_event_settings', function (Blueprint $table) {
            $table->dropColumn('background_asset_key');
        });
    }
};

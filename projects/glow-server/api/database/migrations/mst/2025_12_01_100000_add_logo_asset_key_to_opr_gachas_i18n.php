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
        Schema::table('opr_gachas_i18n', function (Blueprint $table) {
            $table->string('logo_asset_key')->nullable()->after('banner_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opr_gachas_i18n', function (Blueprint $table) {
            $table->dropColumn('logo_asset_key');
        });
    }
};

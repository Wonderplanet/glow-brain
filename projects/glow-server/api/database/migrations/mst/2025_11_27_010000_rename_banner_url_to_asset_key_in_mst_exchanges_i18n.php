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
        Schema::table('mst_exchanges_i18n', function (Blueprint $table) {
            $table->renameColumn('banner_url', 'asset_key');
        });

        Schema::table('mst_exchanges_i18n', function (Blueprint $table) {
            $table->string('asset_key', 255)->comment('アセットキー')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_exchanges_i18n', function (Blueprint $table) {
            $table->renameColumn('asset_key', 'banner_url');
        });

        Schema::table('mst_exchanges_i18n', function (Blueprint $table) {
            $table->string('banner_url', 1000)->comment('バナー画像URL')->change();
        });
    }
};

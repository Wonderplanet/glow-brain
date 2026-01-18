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
            $table->string('gacha_background_color')->comment('ガチャ背景色')->after('background_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opr_gachas_i18n', function (Blueprint $table) {
            $table->dropColumn('gacha_background_color');
        });
    }
};

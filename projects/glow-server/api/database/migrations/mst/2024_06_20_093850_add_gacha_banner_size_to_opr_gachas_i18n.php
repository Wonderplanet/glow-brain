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
            $table->bigInteger('release_key')->default(1)->after('background_url');
            $table->enum('gacha_banner_size', ['SizeM', 'SizeL'])->default('SizeM')->comment('ガチャバナーサイズ')->after('background_url');
        });
        Schema::table('opr_gachas', function (Blueprint $table) {
            $table->dropColumn('gacha_banner_size');
            $table->bigInteger('release_key')->default(1)->after('gacha_priority');
        });
        Schema::table('opr_gacha_limited_prizes', function (Blueprint $table) {
            $table->bigInteger('release_key')->default(1)->after('pickup');
        });
        Schema::table('opr_gacha_permanent_prizes', function (Blueprint $table) {
            $table->bigInteger('release_key')->default(1)->after('pickup');
        });
        Schema::table('opr_gacha_rarities_weights', function (Blueprint $table) {
            $table->bigInteger('release_key')->default(1)->after('epic_rarity');
        });
        Schema::table('opr_gacha_uppers', function (Blueprint $table) {
            $table->bigInteger('release_key')->default(1)->after('count');
        });
        Schema::table('opr_gacha_use_resources', function (Blueprint $table) {
            $table->bigInteger('release_key')->default(1)->after('cost_priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opr_gachas_i18n', function (Blueprint $table) {
            $table->dropColumn('gacha_banner_size');
            $table->dropColumn('release_key');
        });
        Schema::table('opr_gachas', function (Blueprint $table) {
            $table->enum('gacha_banner_size', ['SizeM', 'SizeL'])->default('SizeM')->comment('ガチャバナーサイズ')->after('gacha_priority');
            $table->dropColumn('release_key');
        });
        Schema::table('opr_gacha_limited_prizes', function (Blueprint $table) {
            $table->dropColumn('release_key');
        });
        Schema::table('opr_gacha_permanent_prizes', function (Blueprint $table) {
            $table->dropColumn('release_key');
        });
        Schema::table('opr_gacha_rarities_weights', function (Blueprint $table) {
            $table->dropColumn('release_key');
        });
        Schema::table('opr_gacha_uppers', function (Blueprint $table) {
            $table->dropColumn('release_key');
        });
        Schema::table('opr_gacha_use_resources', function (Blueprint $table) {
            $table->dropColumn('release_key');
        });
    }
};

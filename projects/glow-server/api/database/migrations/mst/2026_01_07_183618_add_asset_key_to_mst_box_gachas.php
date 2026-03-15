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
        Schema::table('mst_box_gachas', function (Blueprint $table) {
            $table->string('display_mst_unit_id2', 255)->after('loop_type')->comment('TOP表示用ユニットID2');
            $table->string('display_mst_unit_id1', 255)->after('loop_type')->comment('TOP表示用ユニットID1');
            $table->string('asset_key', 255)->after('loop_type')->comment('アセットキー');

            $table->dropTimestamps();
            $table->bigInteger('release_key')->default(1)->after('id')->comment('リリースキー');
        });
        Schema::table('mst_box_gacha_groups', function (Blueprint $table) {
            $table->dropTimestamps();
            $table->bigInteger('release_key')->default(1)->after('id')->comment('リリースキー');
        });
        Schema::table('mst_box_gacha_prizes', function (Blueprint $table) {
            $table->dropTimestamps();
            $table->bigInteger('release_key')->default(1)->after('id')->comment('リリースキー');
        });

        Schema::create('mst_box_gachas_i18n', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->string('mst_box_gacha_id', 255)->comment('mst_box_gachas.id');
            $table->string('language', 10)->comment('言語コード');
            $table->string('name', 255)->comment('名称');
            $table->unique(['mst_box_gacha_id', 'language'], 'mst_box_gacha_id_language_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_box_gachas', function (Blueprint $table) {
            $table->dropColumn('asset_key');
            $table->dropColumn('display_mst_unit_id1');
            $table->dropColumn('display_mst_unit_id2');

            $table->timestamps();
            $table->dropColumn('release_key');
        });
        Schema::table('mst_box_gacha_groups', function (Blueprint $table) {
            $table->timestamps();
            $table->dropColumn('release_key');
        });
        Schema::table('mst_box_gacha_prizes', function (Blueprint $table) {
            $table->timestamps();
            $table->dropColumn('release_key');
        });
        Schema::dropIfExists('mst_box_gachas_i18n');
    }
};

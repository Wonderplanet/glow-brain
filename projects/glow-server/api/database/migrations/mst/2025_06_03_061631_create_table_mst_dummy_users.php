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
        Schema::create('mst_dummy_users', function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('id');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->string('mst_unit_id', 255)->nullable()->comment('mst_units.id');
            $table->string('mst_emblem_id', 255)->nullable()->comment('mst_emblems.id');
            $table->integer('grade_unit_level_total_count')->default(1)->comment('図鑑効果用グレードレベル合計');

            $table->comment('ダミーユーザー情報のマスターテーブル');
        });
        
        Schema::create('mst_dummy_users_i18n', function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('id');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->string('mst_dummy_user_id', 255)->comment('mst_dummy_users.id');
            $table->enum('language', ['ja'])->default('ja')->comment('言語');
            $table->string('name', 255)->nullable()->comment('ダミーユーザー名');

            $table->unique(['mst_dummy_user_id', 'language']);
            $table->comment('ダミーユーザー情報の多言語対応テーブル');
        });
        
        Schema::create('mst_dummy_outposts', function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('id');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->string('mst_dummy_user_id', 255)->comment('mst_dummy_users.id');
            $table->string('mst_outpost_enhancement_Id', 255)->comment('mst_outpost_enhancements.id');
            $table->integer('level')->default(1)->comment('アウトポストレベル');

            $table->unique('mst_dummy_user_id');
            $table->comment('ダミーユーザーのゲート情報マスターテーブル');
        });

        Schema::create('mst_dummy_user_units', function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('id');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->string('mst_dummy_user_id', 255)->comment('mst_dummy_users.id');
            $table->string('mst_unit_id', 255)->comment('mst_units.id');
            $table->integer('level')->default(1)->comment('ユニットレベル');
            $table->integer('rank')->default(0)->comment('ユニットランク');
            $table->integer('grade_level')->default(1)->comment('ユニットグレードレベル');

            $table->index('mst_dummy_user_id');
            $table->comment('ダミーユーザーのユニット情報マスターテーブル');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_dummy_users');

        Schema::dropIfExists('mst_dummy_users_i18n');

        Schema::dropIfExists('mst_dummy_outposts');

        Schema::dropIfExists('mst_dummy_user_units');
    }
};

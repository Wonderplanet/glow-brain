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
        Schema::dropIfExists('mst_client_paths');
        Schema::dropIfExists('mst_client_paths_i18n');

        Schema::table('opr_in_game_notices', function (Blueprint $table) {
            $table->string('destination_type', 255)->nullable(false)->comment('遷移先タイプ')->after('display_frequency_type');
            $table->string('destination_path', 255)->nullable(false)->comment('遷移先情報')->after('destination_type');
            $table->string('destination_path_detail', 255)->nullable(false)->comment('遷移先詳細情報')->after('destination_path');

            // mst_client_paths.id 削除
            $table->dropColumn('mst_client_path_id');
        });

        Schema::table('opr_in_game_notices_i18n', function (Blueprint $table) {
            $table->string('button_title', 255)->comment('ボタンに表示するテキスト')->after('banner_url');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('mst_client_paths', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->bigInteger('release_key');
            $table->enum('type', ['InGame', 'Web'])->comment('遷移先タイプ');
            $table->string('path', 255)->comment('遷移先情報');
            $table->string('path_detail', 255)->comment('遷移先詳細情報');
            $table->timestamps();
        });

        Schema::create('mst_client_paths_i18n', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->bigInteger('release_key');
            $table->string('mst_client_path_id', 255)->comment('mst_client_paths.id');
            $table->enum('language', ['ja'])->default('ja')->comment('言語');
            $table->string('button_title', 255)->comment('ボタンに表示するテキスト');
            $table->timestamps();
        });

        Schema::table('opr_in_game_notices', function (Blueprint $table) {
            $table->string('mst_client_path_id', 255)->nullable()->comment('mst_client_paths.id');

            $table->dropColumn('destination_type');
            $table->dropColumn('destination_path');
            $table->dropColumn('destination_path_detail');
        });

        Schema::table('opr_in_game_notices_i18n', function (Blueprint $table) {
            $table->dropColumn('button_title');
        });
    }
};

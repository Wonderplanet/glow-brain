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
        Schema::create('opr_master_releases', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->integer('release_key')->unsigned()->unique()->comment('リリースキー');
            $table->tinyInteger('enabled')->unsigned()->default(0)->comment('リリース状態');
            $table->string('target_release_version_id', 255)->nullable()->comment('opr_master_release_versions.id');
        });

        Schema::create('opr_master_release_versions', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->integer('release_key')->unsigned()->comment('リリースキー');
            $table->string('git_revision', 255)->comment('適用したGitリビジョン');
            $table->string('master_scheme_version', 255)->comment('マスターデータのテーブルスキームのhash化した値');
            $table->string('data_hash', 255)->comment('全ての実データを一意に識別できるハッシュ値');
            $table->string('server_db_hash', 255);
            $table->string('client_mst_data_hash', 255);
            $table->string('client_mst_data_i18n_ja_hash', 255);
            $table->string('client_mst_data_i18n_en_hash', 255);
            $table->string('client_mst_data_i18n_zh_hash', 255);
            $table->string('client_opr_data_hash', 255);
            $table->string('client_opr_data_i18n_ja_hash', 255);
            $table->string('client_opr_data_i18n_en_hash', 255);
            $table->string('client_opr_data_i18n_zh_hash', 255);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opr_master_releases');
        Schema::dropIfExists('opr_master_release_versions');
    }
};

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
        Schema::create('adm_master_import_histories', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('git_revision', 255)->comment('適用したGitリビジョン');
            $table->string('import_adm_user_id', 255)->comment('インポートを行った管理ツールユーザ');
            $table->string('import_source', 255)->comment('インポート元を判別するための文字列');
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');
        });

        Schema::create('adm_master_import_history_versions', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('adm_master_import_history_id', 255)->comment('adm_master_import_histories.id');
            $table->string('opr_master_release_version_id', 255)->comment('opr_master_release_versions.id');
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');
        });

        Schema::create('adm_master_release_version_statuses', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('opr_master_release_version_id', 255)->unique('opr_master_release_version_id_unique')->comment('opr_master_release_versions.id');
            $table->text('ocarina_validated_status');
            $table->string('ocarina_validation_version', 255)->nullable()->comment('オカリナのバージョン');
            $table->timestampTz('client_file_deleted_at')->nullable()->comment('s3のシリアライズデータファイルの削除日時');
            $table->timestampTz('server_db_deleted_at')->nullable()->comment('対となる論理DBの削除日時');
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adm_master_import_histories');
        Schema::dropIfExists('adm_master_import_history_versions');
        Schema::dropIfExists('adm_master_release_version_statuses');
    }
};

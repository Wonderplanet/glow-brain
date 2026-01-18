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
        Schema::create('adm_asset_import_histories', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('opr_asset_release_version_id', 255)->unique('opr_asset_release_version_id_unique')->comment('opr_asset_release_versions.id');
            $table->string('import_adm_user_id', 255)->comment('インポートを行った管理ツールユーザ');
            $table->string('import_source', 255)->comment('インポート元を判別するための文字列');
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');
        });

        Schema::create('adm_asset_release_version_statuses', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('opr_asset_release_version_id', 255)->unique('opr_asset_release_version_id_unique')->comment('opr_asset_release_versions.id');
            $table->timestampTz('asset_deleted_at')->nullable()->comment('アセット削除日時');
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adm_asset_import_histories');
        Schema::dropIfExists('adm_asset_release_version_statuses');
    }
};

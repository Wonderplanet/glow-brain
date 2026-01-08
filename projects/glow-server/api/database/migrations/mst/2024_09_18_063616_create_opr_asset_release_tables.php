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
        $platformList = [
            1,
            2,
        ];

        Schema::create('opr_asset_release_versions', function (Blueprint $table) use ($platformList) {
            $table->string('id', 255)->primary();
            $table->integer('release_key')->unsigned()->comment('リリースキー');
            $table->string('git_revision', 255)->comment('ビルドを行なったクライアントリポジトリのリビジョン');
            $table->string('git_branch', 255)->comment('ビルドを行なったクライアントリポジトリのカレントブランチ');
            $table->string('catalog_hash', 255)->comment('AddressableAssetをビルドした時のCatalogハッシュ値');
            $table->enum('platform', $platformList)->comment('iOS / Androidの識別子');
            $table->string('build_client_version', 255);
            $table->bigInteger('asset_total_byte_size')->unsigned();
            $table->bigInteger('catalog_byte_size')->unsigned();
            $table->string('catalog_file_name', 255);
            $table->string('catalog_hash_file_name', 255);
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');
        });

        Schema::create('opr_asset_releases', function (Blueprint $table) use ($platformList) {
            $table->string('id', 255)->primary();
            $table->integer('release_key')->unsigned()->comment('リリースキー');
            $table->enum('platform', $platformList)->comment('iOS / Androidの識別子');
            $table->boolean('enabled')->comment('リリース状態');
            $table->string('target_release_version_id', 255);
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');
            $table->unique([
                'release_key',
                'platform'
            ], 'release_key_platform_unique');
            $table->index('enabled', 'enabled_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opr_asset_release_versions');
        Schema::dropIfExists('opr_asset_releases');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    private $tables = [
        'mng_master_releases',
        'mng_master_release_versions',
        'mng_asset_release_versions',
        'mng_asset_releases',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (!Schema::hasColumn($tableName, 'release_key')) {
                continue; // カラムが存在しない場合はスキップ
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->bigInteger('release_key')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (!Schema::hasColumn($tableName, 'release_key')) {
                continue; // カラムが存在しない場合はスキップ
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->integer('release_key')->change();
            });
        }
    }
};

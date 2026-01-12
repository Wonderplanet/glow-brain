<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MNG_CONNECTION;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mng_client_versions', function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('UUID');
            $table->string('client_version', 32)->comment('クライアントバージョン');
            $table->integer('platform')->comment('プラットフォーム');
            $table->tinyInteger('is_force_update')->comment('強制アップデートするか');
            $table->timestampsTz();

            $table->unique(['client_version', 'platform'], 'uk_client_version_platform');

            $table->comment('クライアントバージョン管理');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mng_client_versions');
    }
};

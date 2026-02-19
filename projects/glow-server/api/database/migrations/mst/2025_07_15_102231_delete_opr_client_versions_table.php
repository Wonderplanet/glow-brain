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
        // Delete the opr_client_versions table
        Schema::dropIfExists('opr_client_versions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('opr_client_versions', function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('UUID');
            $table->string('client_version', 32)->comment('クライアントバージョン');
            $table->integer('platform')->comment('プラットフォーム');
            $table->tinyInteger('is_force_update')->comment('強制アップデートするか');
            $table->timestampsTz();

            $table->comment('クライアントバージョンごとの対応設定。強制アップデート必須など。');
        });
    }
};

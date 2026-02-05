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
        Schema::create('adm_env_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('version')->comment('バージョン');
            $table->string('client_version_hash')->comment('クライアントバージョンハッシュ');
            $table->text('env_status_string')->comment('環境ステータス');
            $table->timestamps();
            
            $table->unique(['version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adm_env_settings');
    }
};

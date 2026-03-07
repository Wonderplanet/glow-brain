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
        Schema::create('usr_os_platforms', function (Blueprint $table) {
            // TiKVストレージの都合のため、不要なid_uniqueインデックスをつけない
            $table->string('id', 255)->comment('ID');
            $table->string('usr_user_id')->comment('usr_users.id');
            $table->string('os_platform', 16)->comment('OSプラットフォーム');
            $table->timestamps();

            $table->primary(['usr_user_id', 'os_platform'], 'pk_usr_user_id_os_platform');
            $table->comment('ユーザーのOSプラットフォーム情報');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usr_os_platforms');
    }
};

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
        Schema::table('log_bnid_links', function (Blueprint $table) {
            $table->string('usr_device_id', 255)
                ->nullable()
                ->after('after_bn_user_id')
                ->comment('デバイスID');
            $table->string('os_platform', 16)
                ->default('')
                ->after('usr_device_id')
                ->comment('OSプラットフォーム');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_bnid_links', function (Blueprint $table) {
            $table->dropColumn(['usr_device_id', 'os_platform']);
        });
    }
};

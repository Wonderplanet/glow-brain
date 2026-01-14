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
        Schema::table('usr_devices', function (Blueprint $table) {
            $table->string('os_platform', 16)->comment('OSプラットフォーム')->after('bnid_linked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_devices', function (Blueprint $table) {
            $table->dropColumn('os_platform');
        });
    }
};

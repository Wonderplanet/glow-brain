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
        Schema::table('usr_users', function (Blueprint $table) {
            $table->string('bn_user_id', 255)->nullable()->default(null)->comment('BNIDユーザーID')->after('privacy_policy_version');
        });
        Schema::table('usr_devices', function (Blueprint $table) {
            $table->timestampTz('bnid_linked_at')->nullable()->default(null)->comment('BNID連携日時')->after('uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_users', function (Blueprint $table) {
            $table->dropColumn('bn_user_id');
        });
        Schema::table('usr_devices', function (Blueprint $table) {
            $table->dropColumn('bnid_linked_at');
        });
    }
};

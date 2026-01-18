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
        Schema::table('usr_messages', function (Blueprint $table) {
            $table->string('resource_type', 255)->nullable()->after('message_source');
        });
        Schema::table('usr_messages', function (Blueprint $table) {
            $table->string('resource_id', 255)->nullable()->after('resource_type');
        });
        Schema::table('usr_messages', function (Blueprint $table) {
            $table->integer('resource_amount')->unsigned()->nullable()->after('resource_id');
        });
        Schema::table('usr_messages', function (Blueprint $table) {
            $table->string('title', 255)->nullable()->after('resource_amount');
        });
        Schema::table('usr_messages', function (Blueprint $table) {
            $table->string('body', 255)->nullable()->after('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_messages', function (Blueprint $table) {
            $table->dropColumn('resource_type');
            $table->dropColumn('resource_id');
            $table->dropColumn('resource_amount');
            $table->dropColumn('title');
            $table->dropColumn('body');
        });
    }
};

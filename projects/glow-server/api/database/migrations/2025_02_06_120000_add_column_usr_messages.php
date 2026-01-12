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
            $table->tinyInteger('is_received')->unsigned()->default(0)->comment('受け取り済みフラグ')->after('resource_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_messages', function (Blueprint $table) {
            $table->dropColumn('is_received');
        });
    }
};

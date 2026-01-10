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
        Schema::table('usr_pvp_sessions', function (Blueprint $table) {
            $table->tinyInteger('is_use_item')->default(0)->comment('アイテム使用フラグ (0: 使用しない, 1: 使用する)')->after('sys_pvp_season_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_pvp_sessions', function (Blueprint $table) {
            $table->dropColumn('is_use_item');
        });
    }
};

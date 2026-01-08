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
        Schema::table('usr_gachas', function (Blueprint $table) {
            $table->dropColumn('free_played_at');
            $table->timestampTz('played_at')->nullable()->default(null)->comment('回した時間')->after('ad_played_at');
        });
        Schema::table('usr_gachas', function (Blueprint $table) {
            $table->integer('ad_count')->unsigned()->default(0)->comment('広告でガチャを回した回数')->after('played_at');
        });
        Schema::table('usr_gachas', function (Blueprint $table) {
            $table->integer('ad_daily_count')->unsigned()->default(0)->comment('広告で本日ガチャを回した回数')->after('ad_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_gachas', function (Blueprint $table) {
            $table->timestampTz('free_played_at')->nullable()->default(null)->comment('無料で回した時間')->after('ad_played_at');
            $table->dropColumn('played_at');
            $table->dropColumn('ad_count');
            $table->dropColumn('ad_daily_count');
        });
    }
};

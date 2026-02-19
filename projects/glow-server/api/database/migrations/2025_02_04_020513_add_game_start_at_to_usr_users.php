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
            $table->timestampTz('game_start_at')->nullable()->comment('ゲーム開始日時')->after('suspend_end_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_users', function (Blueprint $table) {
            $table->dropColumn('game_start_at');
        });
    }
};

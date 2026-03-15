<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // game_start_atがNULLの行に対してcreated_atの値を代入
        DB::statement("
            UPDATE usr_users
            SET game_start_at = created_at
            WHERE game_start_at IS NULL
        ");

        Schema::table('usr_users', function (Blueprint $table) {
            $table->timestampTz('game_start_at')->nullable(false)->comment('ゲーム開始日時')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_users', function (Blueprint $table) {
            $table->timestampTz('game_start_at')->nullable()->comment('ゲーム開始日時')->change();
        });
    }
};

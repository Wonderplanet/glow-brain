<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * キャラグレード5到達原画機能
     * usr_unitsテーブルに最後に報酬を受け取ったグレードレベルを追加
     */
    public function up(): void
    {
        Schema::table('usr_units', function (Blueprint $table) {
            $table->tinyInteger('last_reward_grade_level')
                ->default(0)
                ->after('is_new_encyclopedia')
                ->comment('最後に報酬を受け取ったグレードレベル');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_units', function (Blueprint $table) {
            $table->dropColumn('last_reward_grade_level');
        });
    }
};

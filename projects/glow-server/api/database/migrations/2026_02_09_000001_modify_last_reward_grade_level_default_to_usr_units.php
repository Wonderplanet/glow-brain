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
     * last_reward_grade_levelのデフォルト値を0から1に変更
     * ユニット取得時点でグレード1のため、グレード1の報酬は対象外とする
     */
    public function up(): void
    {
        Schema::table('usr_units', function (Blueprint $table) {
            $table->tinyInteger('last_reward_grade_level')
                ->default(1)
                ->comment('最後に報酬を受け取ったグレードレベル')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_units', function (Blueprint $table) {
            $table->tinyInteger('last_reward_grade_level')
                ->default(0)
                ->comment('最後に報酬を受け取ったグレードレベル')
                ->change();
        });
    }
};

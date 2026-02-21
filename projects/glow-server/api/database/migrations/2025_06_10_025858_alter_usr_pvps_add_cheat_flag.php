<?php

use App\Domain\Constants\Database;
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
        Schema::table('usr_pvps', function (Blueprint $table) {
            $table->tinyInteger('is_excluded_ranking')->unsigned()->default(0)->comment('ランキングから除外されているか')->after('ranking');
            $table->tinyInteger('is_season_reward_received')->unsigned()->default(0)->comment('シーズン報酬受け取り済みか')->after('ranking');
        });

        Schema::table('usr_pvp_sessions', function (Blueprint $table) {
            $table->renameColumn('start_at', 'battle_start_at');
            $table->dropColumn('end_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_pvps', function (Blueprint $table) {
            $table->dropColumn('is_excluded_ranking');
            $table->dropColumn('is_season_reward_received');
        });

        Schema::table('usr_pvp_sessions', function (Blueprint $table) {
            $table->timestampTz('start_at')->comment('セッション開始日時')->after('is_valid');
            $table->timestampTz('end_at')->nullable()->comment('セッション終了日時')->after('is_valid');
        });
    }
};

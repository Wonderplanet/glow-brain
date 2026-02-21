<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('opr_gachas', function (Blueprint $table) {
            $table->renameColumn('upper_type', 'upper_group');
            $table->renameColumn('permanent_prize_group_id', 'normal_prize_group_id');
            $table->renameColumn('pickup_mst_unit_id', 'display_mst_unit_id');
            $table->renameColumn('total_limit_play_limit', 'total_play_limit_count');
        });

        Schema::table('opr_gachas', function (Blueprint $table) {
            $table->string('rarity_group_id', 255)->nullable()->comment('opr_gacha_rarities.id')->change();
            $table->string('normal_prize_group_id', 255)->comment('opr_gacha_normal_prizes.group_id')->change();
            $table->after('total_play_limit_count', function ($table) {
                $table->integer('daily_ad_limit_count')->unsigned()->nullable()->default(null)->comment('1日に広告で回すことができる上限数');
                $table->integer('total_ad_limit_count')->unsigned()->nullable()->default(null)->comment('広告で回すことができる上限数');
            });
            $table->after('limited_prize_group_id', function ($table) {
                $table->enum('appearance_condition', ['Always', 'HasTicket'])->default('Always')->comment('登場条件');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opr_gachas', function (Blueprint $table) {
            $table->renameColumn('upper_group', 'upper_type');
            $table->renameColumn('normal_prize_group_id', 'permanent_prize_group_id');
            $table->renameColumn('display_mst_unit_id', 'pickup_mst_unit_id');
            $table->renameColumn('total_play_limit_count', 'total_limit_play_limit');
        });

        Schema::table('opr_gachas', function (Blueprint $table) {
            $table->string('permanent_prize_group_id', 255)->comment('opr_gacha_permanent_prizes.group_id')->change();
            $table->dropColumn('daily_ad_limit_count');
            $table->dropColumn('total_ad_limit_count');
            $table->dropColumn('appearance_condition');
        });
    }
};

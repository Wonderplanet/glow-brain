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
        // opr_gacha_uppers.upper_typeはenumだがユーザーデータは膨大になる可能性がありenumだと追加時にalterの時間がかかる可能性があるのでstringとする
        Schema::table('usr_gacha_uppers', function (Blueprint $table) {
            $table->string('upper_type')->default('')->comment('天井タイプ')->after('upper_group');
            $table->dropUnique('usr_user_id_upper_group_unique');
            $table->unique(['usr_user_id', 'upper_group', 'upper_type'], 'usr_user_id_upper_group_upper_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_gacha_uppers', function (Blueprint $table) {
            $table->dropColumn('upper_type');
            $table->dropUnique('usr_user_id_upper_group_upper_type_unique');
            $table->unique(['usr_user_id', 'upper_group'], 'usr_user_id_upper_group_unique');
        });
    }
};

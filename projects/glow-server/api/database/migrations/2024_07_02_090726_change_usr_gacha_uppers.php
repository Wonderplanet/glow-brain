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
        Schema::table('usr_gacha_uppers', function (Blueprint $table) {
            $table->renameColumn('upper_type', 'upper_group');
            $table->dropUnique('usr_user_id_upper_type_step_number_unique');
            $table->unique(['usr_user_id', 'upper_group'], 'usr_user_id_upper_group_unique');
            $table->dropColumn('step_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_gacha_uppers', function (Blueprint $table) {
            $table->renameColumn('upper_group', 'upper_type');
            $table->integer('step_number')->unsigned()->default(1)->comment('何段階目の天井回数かの設定');
            $table->unique(['usr_user_id', 'upper_type', 'step_number'], 'usr_user_id_upper_type_step_number_unique');
            $table->dropUnique('usr_user_id_upper_group_unique');
        });
    }
};

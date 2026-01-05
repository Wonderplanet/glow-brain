<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // 対象テーブル: log_coins, log_exps, log_items, log_staminas
    // amount列の後ろに、before_amount, after_amount列(どちらもunsigned biginter default 0)を追加
    // その後、amount列を削除

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('log_coins', function (Blueprint $table) {
            $table->unsignedBigInteger('before_amount')->default(0)->after('amount')->comment('変動前の量');
        });
        Schema::table('log_coins', function (Blueprint $table) {
            $table->unsignedBigInteger('after_amount')->default(0)->after('before_amount')->comment('変動後の量');
        });
        Schema::table('log_coins', function (Blueprint $table) {
            $table->dropColumn('amount');
        });

        Schema::table('log_exps', function (Blueprint $table) {
            $table->unsignedBigInteger('before_amount')->default(0)->after('amount')->comment('変動前の量');
        });
        Schema::table('log_exps', function (Blueprint $table) {
            $table->unsignedBigInteger('after_amount')->default(0)->after('before_amount')->comment('変動後の量');
        });
        Schema::table('log_exps', function (Blueprint $table) {
            $table->dropColumn('amount');
        });

        Schema::table('log_items', function (Blueprint $table) {
            $table->unsignedBigInteger('before_amount')->default(0)->after('amount')->comment('変動前の量');
        });
        Schema::table('log_items', function (Blueprint $table) {
            $table->unsignedBigInteger('after_amount')->default(0)->after('before_amount')->comment('変動後の量');
        });
        Schema::table('log_items', function (Blueprint $table) {
            $table->dropColumn('amount');
        });

        Schema::table('log_staminas', function (Blueprint $table) {
            $table->unsignedInteger('before_amount')->default(0)->after('amount')->comment('変動前の量');
        });
        Schema::table('log_staminas', function (Blueprint $table) {
            $table->unsignedInteger('after_amount')->default(0)->after('before_amount')->comment('変動後の量');
        });
        Schema::table('log_staminas', function (Blueprint $table) {
            $table->dropColumn('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_coins', function (Blueprint $table) {
            $table->unsignedInteger('amount')->default(0)->after('after_amount')->comment('変動数');
        });
        Schema::table('log_coins', function (Blueprint $table) {
            $table->dropColumn('before_amount');
            $table->dropColumn('after_amount');
        });

        Schema::table('log_exps', function (Blueprint $table) {
            $table->unsignedInteger('amount')->default(0)->after('after_amount')->comment('変動数');
        });
        Schema::table('log_exps', function (Blueprint $table) {
            $table->dropColumn('before_amount');
            $table->dropColumn('after_amount');
        });

        Schema::table('log_items', function (Blueprint $table) {
            $table->unsignedInteger('amount')->default(0)->after('after_amount')->comment('変動数');
        });
        Schema::table('log_items', function (Blueprint $table) {
            $table->dropColumn('before_amount');
            $table->dropColumn('after_amount');
        });

        Schema::table('log_staminas', function (Blueprint $table) {
            $table->unsignedInteger('amount')->default(0)->after('after_amount')->comment('変動数');
        });
        Schema::table('log_staminas', function (Blueprint $table) {
            $table->dropColumn('before_amount');
            $table->dropColumn('after_amount');
        });
    }
};

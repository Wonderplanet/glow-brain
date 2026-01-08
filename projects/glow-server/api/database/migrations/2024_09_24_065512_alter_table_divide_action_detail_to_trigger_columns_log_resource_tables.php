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
        // action_detail列の後に、trigger_source, trigger_value, trigger_option列を追加（全てvarchar(255)）
        // その後、action_detail列を削除
        // 対象テーブル: log_coins, log_staminas, log_items, log_exps, log_emblems

        Schema::table('log_coins', function (Blueprint $table) {
            $table->string('trigger_source')->after('action_detail')->nullable()->comment('経緯情報ソース');
        });
        Schema::table('log_coins', function (Blueprint $table) {
            $table->string('trigger_value')->after('trigger_source')->nullable()->comment('経緯情報値');
        });
        Schema::table('log_coins', function (Blueprint $table) {
            $table->string('trigger_option')->after('trigger_value')->nullable()->comment('経緯情報オプション');
        });
        Schema::table('log_coins', function (Blueprint $table) {
            $table->dropColumn('action_detail');
        });

        Schema::table('log_staminas', function (Blueprint $table) {
            $table->string('trigger_source')->after('action_detail')->nullable()->comment('経緯情報ソース');
        });
        Schema::table('log_staminas', function (Blueprint $table) {
            $table->string('trigger_value')->after('trigger_source')->nullable()->comment('経緯情報値');
        });
        Schema::table('log_staminas', function (Blueprint $table) {
            $table->string('trigger_option')->after('trigger_value')->nullable()->comment('経緯情報オプション');
        });
        Schema::table('log_staminas', function (Blueprint $table) {
            $table->dropColumn('action_detail');
        });

        Schema::table('log_items', function (Blueprint $table) {
            $table->string('trigger_source')->after('action_detail')->nullable()->comment('経緯情報ソース');
        });
        Schema::table('log_items', function (Blueprint $table) {
            $table->string('trigger_value')->after('trigger_source')->nullable()->comment('経緯情報値');
        });
        Schema::table('log_items', function (Blueprint $table) {
            $table->string('trigger_option')->after('trigger_value')->nullable()->comment('経緯情報オプション');
        });
        Schema::table('log_items', function (Blueprint $table) {
            $table->dropColumn('action_detail');
        });

        Schema::table('log_exps', function (Blueprint $table) {
            $table->string('trigger_source')->after('action_detail')->nullable()->comment('経緯情報ソース');
        });
        Schema::table('log_exps', function (Blueprint $table) {
            $table->string('trigger_value')->after('trigger_source')->nullable()->comment('経緯情報値');
        });
        Schema::table('log_exps', function (Blueprint $table) {
            $table->string('trigger_option')->after('trigger_value')->nullable()->comment('経緯情報オプション');
        });
        Schema::table('log_exps', function (Blueprint $table) {
            $table->dropColumn('action_detail');
        });

        Schema::table('log_emblems', function (Blueprint $table) {
            $table->string('trigger_source')->after('action_detail')->nullable()->comment('経緯情報ソース');
        });
        Schema::table('log_emblems', function (Blueprint $table) {
            $table->string('trigger_value')->after('trigger_source')->nullable()->comment('経緯情報値');
        });
        Schema::table('log_emblems', function (Blueprint $table) {
            $table->string('trigger_option')->after('trigger_value')->nullable()->comment('経緯情報オプション');
        });
        Schema::table('log_emblems', function (Blueprint $table) {
            $table->dropColumn('action_detail');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // trigger_optionの後に、action_detail列を追加（`action_detail` json DEFAULT NULL COMMENT 'アクション詳細',）

        Schema::table('log_coins', function (Blueprint $table) {
            $table->json('action_detail')->after('trigger_option')->nullable()->comment('アクション詳細');

            $table->dropColumn('trigger_source');
            $table->dropColumn('trigger_value');
            $table->dropColumn('trigger_option');
        });

        Schema::table('log_staminas', function (Blueprint $table) {
            $table->json('action_detail')->after('trigger_option')->nullable()->comment('アクション詳細');

            $table->dropColumn('trigger_source');
            $table->dropColumn('trigger_value');
            $table->dropColumn('trigger_option');
        });

        Schema::table('log_items', function (Blueprint $table) {
            $table->json('action_detail')->after('trigger_option')->nullable()->comment('アクション詳細');

            $table->dropColumn('trigger_source');
            $table->dropColumn('trigger_value');
            $table->dropColumn('trigger_option');
        });

        Schema::table('log_exps', function (Blueprint $table) {
            $table->json('action_detail')->after('trigger_option')->nullable()->comment('アクション詳細');

            $table->dropColumn('trigger_source');
            $table->dropColumn('trigger_value');
            $table->dropColumn('trigger_option');
        });

        Schema::table('log_emblems', function (Blueprint $table) {
            $table->json('action_detail')->after('trigger_option')->nullable()->comment('アクション詳細');

            $table->dropColumn('trigger_source');
            $table->dropColumn('trigger_value');
            $table->dropColumn('trigger_option');
        });
    }
};

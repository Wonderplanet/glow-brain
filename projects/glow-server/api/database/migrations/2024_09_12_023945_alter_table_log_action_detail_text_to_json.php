<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // 変更前
    // CREATE TABLE `log_coins` (
    //     `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
    //     `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
    //     `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
    //     `logging_no` int(10) unsigned NOT NULL COMMENT 'APIリクエスト中でのログの順番',
    //     `action_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Get: 獲得 Use: 消費',
    //     `amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '変動数',
    //     `action_detail` text COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'アクションの理由(シリアライズデータ)',
    //     `created_at` timestamp NULL DEFAULT NULL,
    //     `updated_at` timestamp NULL DEFAULT NULL,
    //     PRIMARY KEY (`id`) /*T![clustered_index] NONCLUSTERED */
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // action_detailの後にaction_detail_jsonを追加してからaction_detailを削除し、action_detail_jsonをaction_detailに改名

        Schema::table('log_coins', function (Blueprint $table) {
            $table->json('action_detail_json')->nullable()->after('action_detail')->comment('アクション詳細');
        });
        Schema::table('log_coins', function (Blueprint $table) {
            $table->dropColumn('action_detail');
        });
        Schema::table('log_coins', function (Blueprint $table) {
            $table->renameColumn('action_detail_json', 'action_detail');
        });

        Schema::table('log_staminas', function (Blueprint $table) {
            $table->json('action_detail_json')->nullable()->after('action_detail')->comment('アクション詳細');
        });
        Schema::table('log_staminas', function (Blueprint $table) {
            $table->dropColumn('action_detail');
        });
        Schema::table('log_staminas', function (Blueprint $table) {
            $table->renameColumn('action_detail_json', 'action_detail');
        });

        Schema::table('log_items', function (Blueprint $table) {
            $table->json('action_detail_json')->nullable()->after('action_detail')->comment('アクション詳細');
        });
        Schema::table('log_items', function (Blueprint $table) {
            $table->dropColumn('action_detail');
        });
        Schema::table('log_items', function (Blueprint $table) {
            $table->renameColumn('action_detail_json', 'action_detail');
        });

        Schema::table('log_exps', function (Blueprint $table) {
            $table->json('action_detail_json')->nullable()->after('action_detail')->comment('アクション詳細');
        });
        Schema::table('log_exps', function (Blueprint $table) {
            $table->dropColumn('action_detail');
        });
        Schema::table('log_exps', function (Blueprint $table) {
            $table->renameColumn('action_detail_json', 'action_detail');
        });

        Schema::table('log_emblems', function (Blueprint $table) {
            $table->json('action_detail_json')->nullable()->after('action_detail')->comment('アクション詳細');
        });
        Schema::table('log_emblems', function (Blueprint $table) {
            $table->dropColumn('action_detail');
        });
        Schema::table('log_emblems', function (Blueprint $table) {
            $table->renameColumn('action_detail_json', 'action_detail');
        });

        // log_stage_actionsのparty_units, used_outpost, in_game_battle_log列をjsonに変更
        // 変更前
        // CREATE TABLE `log_stage_actions` (
        //     `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        //     `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
        //     `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
        //     `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
        //     `logging_no` int(10) unsigned NOT NULL COMMENT 'APIリクエスト中でのログの順番',
        //     `mst_stage_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_stages.id',
        //     `api_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'リクエストされたステージ関連のAPI',
        //     `result` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'ステージ結果。0: 結果未確定, 1: 敗北, 2: 勝利',
        //     `party_units` text COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ユニットのステータス情報を含めたパーティ情報（シリアライズデータ）',
        //     `used_outpost` text COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '使用したゲート情報（シリアライズデータ）',
        //     `in_game_battle_log` text COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'インゲームのバトルログ（シリアライズデータ）',
        //     `created_at` timestamp NULL DEFAULT NULL,
        //     `updated_at` timestamp NULL DEFAULT NULL,
        //     PRIMARY KEY (`id`) /*T![clustered_index] NONCLUSTERED */
        //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        Schema::table('log_stage_actions', function (Blueprint $table) {
            $table->json('party_units_json')->nullable()->after('party_units')->comment('ユニットのステータス情報を含めたパーティ情報');
            $table->json('used_outpost_json')->nullable()->after('used_outpost')->comment('使用したゲート情報');
            $table->json('in_game_battle_log_json')->nullable()->after('in_game_battle_log')->comment('インゲームのバトルログ');
        });
        Schema::table('log_stage_actions', function (Blueprint $table) {
            $table->dropColumn('party_units');
            $table->dropColumn('used_outpost');
            $table->dropColumn('in_game_battle_log');
        });
        Schema::table('log_stage_actions', function (Blueprint $table) {
            $table->renameColumn('party_units_json', 'party_units');
            $table->renameColumn('used_outpost_json', 'used_outpost');
            $table->renameColumn('in_game_battle_log_json', 'in_game_battle_log');
        });

        // log_api_requestsのrequest_body列をjsonに変更
        // 変更前
        // CREATE TABLE `log_api_requests` (
        //     `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        //     `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
        //     `api_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'リクエストされたAPI',
        //     `api_version` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIバージョン。例：1.0.0',
        //     `client_version` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'クライアントバージョン。例：1.0.0',
        //     `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
        //     `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
        //     `requested_release_key` int(11) NOT NULL COMMENT 'apiリクエスト時に使用したマスタデータのリリースキー',
        //     `user_agent` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ユーザーエージェント',
        //     `os_platform` int(11) NOT NULL COMMENT 'OSプラットフォーム。UserConstantのPLATFORM_XXXの値。',
        //     `os_version` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'OSバージョン',
        //     `country_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '国コード',
        //     `ad_id` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '広告ID',
        //     `request_body` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'リクエストボディ',
        //     `request_at` timestamp NOT NULL COMMENT 'APIリクエスト日時',
        //     `created_at` timestamp NULL DEFAULT NULL,
        //     `updated_at` timestamp NULL DEFAULT NULL,
        //     PRIMARY KEY (`id`) /*T![clustered_index] NONCLUSTERED */
        //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        Schema::table('log_api_requests', function (Blueprint $table) {
            $table->json('request_body_json')->nullable()->after('request_body')->comment('リクエストボディ');
        });
        Schema::table('log_api_requests', function (Blueprint $table) {
            $table->dropColumn('request_body');
        });
        Schema::table('log_api_requests', function (Blueprint $table) {
            $table->renameColumn('request_body_json', 'request_body');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // アクションの理由(シリアライズデータ)

        Schema::table('log_coins', function (Blueprint $table) {
            $table->text('action_detail_text')->nullable()->after('action_detail')->comment('アクションの理由(シリアライズデータ)');
        });
        Schema::table('log_coins', function (Blueprint $table) {
            $table->dropColumn('action_detail');
        });
        Schema::table('log_coins', function (Blueprint $table) {
            $table->renameColumn('action_detail_text', 'action_detail');
        });

        Schema::table('log_staminas', function (Blueprint $table) {
            $table->text('action_detail_text')->nullable()->after('action_detail')->comment('アクションの理由(シリアライズデータ)');
        });
        Schema::table('log_staminas', function (Blueprint $table) {
            $table->dropColumn('action_detail');
        });
        Schema::table('log_staminas', function (Blueprint $table) {
            $table->renameColumn('action_detail_text', 'action_detail');
        });

        Schema::table('log_items', function (Blueprint $table) {
            $table->text('action_detail_text')->nullable()->after('action_detail')->comment('アクションの理由(シリアライズデータ)');
        });
        Schema::table('log_items', function (Blueprint $table) {
            $table->dropColumn('action_detail');
        });
        Schema::table('log_items', function (Blueprint $table) {
            $table->renameColumn('action_detail_text', 'action_detail');
        });

        Schema::table('log_exps', function (Blueprint $table) {
            $table->text('action_detail_text')->nullable()->after('action_detail')->comment('アクションの理由(シリアライズデータ)');
        });
        Schema::table('log_exps', function (Blueprint $table) {
            $table->dropColumn('action_detail');
        });
        Schema::table('log_exps', function (Blueprint $table) {
            $table->renameColumn('action_detail_text', 'action_detail');
        });

        Schema::table('log_emblems', function (Blueprint $table) {
            $table->text('action_detail_text')->nullable()->after('action_detail')->comment('アクションの理由(シリアライズデータ)');
        });
        Schema::table('log_emblems', function (Blueprint $table) {
            $table->dropColumn('action_detail');
        });
        Schema::table('log_emblems', function (Blueprint $table) {
            $table->renameColumn('action_detail_text', 'action_detail');
        });

        Schema::table('log_stage_actions', function (Blueprint $table) {
            $table->text('party_units_text')->nullable()->after('party_units')->comment('ユニットのステータス情報を含めたパーティ情報(シリアライズデータ)');
            $table->text('used_outpost_text')->nullable()->after('used_outpost')->comment('使用したゲート情報(シリアライズデータ)');
            $table->text('in_game_battle_log_text')->nullable()->after('in_game_battle_log')->comment('インゲームのバトルログ(シリアライズデータ)');
        });
        Schema::table('log_stage_actions', function (Blueprint $table) {
            $table->dropColumn('party_units');
            $table->dropColumn('used_outpost');
            $table->dropColumn('in_game_battle_log');
        });
        Schema::table('log_stage_actions', function (Blueprint $table) {
            $table->renameColumn('party_units_text', 'party_units');
            $table->renameColumn('used_outpost_text', 'used_outpost');
            $table->renameColumn('in_game_battle_log_text', 'in_game_battle_log');
        });

        Schema::table('log_api_requests', function (Blueprint $table) {
            $table->text('request_body_text')->nullable()->after('request_body')->comment('リクエストボディ');
        });
        Schema::table('log_api_requests', function (Blueprint $table) {
            $table->dropColumn('request_body');
        });
        Schema::table('log_api_requests', function (Blueprint $table) {
            $table->renameColumn('request_body_text', 'request_body');
        });
    }
};

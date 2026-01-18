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
        //     `party_units` json DEFAULT NULL COMMENT 'ユニットのステータス情報を含めたパーティ情報',
        //     `used_outpost` json DEFAULT NULL COMMENT '使用したゲート情報',
        //     `in_game_battle_log` json DEFAULT NULL COMMENT 'インゲームのバトルログ',
        //     `created_at` timestamp NULL DEFAULT NULL,
        //     `updated_at` timestamp NULL DEFAULT NULL,
        //     PRIMARY KEY (`id`) /*T![clustered_index] NONCLUSTERED */
        //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        // 変更後
        // カラム No	INDEX	table	column	データ型	NULL許容	デフォルト値	カラムの説明	パラメータ説明
        // 1	PRI	log_stage_actions	id	varchar(255)	FALSE		ULID
        // 2		log_stage_actions	usr_user_id	varchar(255)	FALSE		usr_users.id
        // 3		log_stage_actions	nginx_request_id	varchar(255)	FALSE		APIリクエスト単位でNginxにて生成されるユニークID
        // 4		log_stage_actions	request_id	varchar(255)	FALSE		APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID
        // 5		log_stage_actions	logging_no	int	FALSE		APIリクエスト中でのログの順番
        // 6		log_stage_actions	mst_stage_id	varchar(255)	FALSE		mst_stages.id
        // 7		log_stage_actions	api_path	varchar(255)	FALSE		リクエストされたステージ関連のAPI
        // 8		log_stage_actions	result	int	FALSE		ステージ結果。0: 結果未確定, 1: 敗北, 2: 勝利
        // 9		log_stage_actions	mst_outpost_id	varchar(255)	FALSE		使用中のゲート	ゲートの強化状況詳細は、log_outpost_enhancementsかusr_outpost_enhancementsから参照可
        // 10		log_stage_actions	mst_artwork_id	varchar(255)	FALSE		装備中の原画
        // 11		log_stage_actions	party_units	json	TRUE		ユニットのステータス情報を含めたパーティ情報	"データ例：
        // [
        //         {
        //                 ""grade_level"": 3,
        //                 ""level"": 20,
        //                 ""mst_unit_id"": ""chara_dan_00001"",
        //                 ""rank"": 1
        //         },
        //         {
        //                 ""grade_level"": 2,
        //                 ""level"": 20,
        //                 ""mst_unit_id"": ""chara_jig_00201"",
        //                 ""rank"": 1
        //         },
        // ]"
        // 12		log_stage_actions	in_game_battle_log	json	TRUE		インゲームのバトルログ	"{
        //     ""defeat_boss_enemy_count"": 0,
        //     ""defeat_enemy_count"": 0,
        //     ""score"": 35809
        // }"
        // 13		log_stage_actions	created_at	timestamp	TRUE		作成日時のタイムスタンプ
        // 14		log_stage_actions	updated_at	timestamp	TRUE		更新日時のタイムスタンプ	更新日時のタイムスタンプ

        Schema::table('log_stage_actions', function (Blueprint $table) {
            $table->dropColumn('used_outpost');

            $table->string('mst_artwork_id', 255)->comment('装備中の原画')->after('result');
            $table->string('mst_outpost_id', 255)->comment('使用中のゲート')->after('result');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_stage_actions', function (Blueprint $table) {
            $table->json('used_outpost')->nullable()->comment('使用したゲート情報')->after('party_units');

            $table->dropColumn('mst_artwork_id');
            $table->dropColumn('mst_outpost_id');
        });
    }
};

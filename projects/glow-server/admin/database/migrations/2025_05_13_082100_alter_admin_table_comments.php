<?php

use App\Traits\MigrationAddColumnCommentsTrait;
use App\Traits\MigrationAddTableCommentTrait;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use MigrationAddTableCommentTrait;
    use MigrationAddColumnCommentsTrait;

    private array $tableCommentList = [
        'adm_asset_import_histories' => 'アセットインポート履歴管理',
        'adm_asset_release_version_statuses' => 'アセット配信バージョンステータス管理',
        'adm_bank_f001' => 'BanKF001形式送信情報管理',
        'adm_bank_f002' => 'BanKF002形式送信情報管理',
        'adm_bank_f003' => 'BanKF003形式送信情報管理',
        'adm_data_controls' => 'データ制御情報管理',
        'adm_datalake_logs' => 'データレーク転送実行ログ管理',
        'adm_foreign_currency_rates' => '外貨為替レート情報管理',
        'adm_gacha_log_aggregation_progresses' => 'ガチャログ集計進捗管理',
        'adm_gacha_simulation_logs' => 'ガチャシミュレーション実行ログ管理',
        'adm_in_game_notices' => 'インゲームお知らせ管理',
        'adm_informations' => '各種お知らせ情報管理',
        'adm_master_import_histories' => 'マスタインポート履歴管理',
        'adm_master_import_history_versions' => 'マスタインポート履歴バージョン管理',
        'adm_master_release_version_statuses' => 'マスタ配信バージョンステータス管理',
        'adm_message_distribution_inputs' => 'メッセージ配信入力情報管理',
        'adm_model_has_permissions' => 'モデル割り当て権限情報管理',
        'adm_model_has_roles' => 'モデル割り当て役割情報管理',
        'adm_permissions' => '個別権限情報管理',
        'adm_posts' => '管理者からの投稿情報管理',
        'adm_role_has_permissions' => '役割割り当て権限情報管理',
        'adm_roles' => '役割情報管理',
        'adm_user_ban_operate_histories' => 'ユーザーBAN操作履歴管理',
        'adm_user_deletion_operate_histories' => 'ユーザー削除操作履歴管理',
        'adm_users' => '管理者ユーザー管理',
    ];

    private array $columnCommentList = [
        'adm_asset_import_histories' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'adm_asset_release_version_statuses' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'adm_bank_f001' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'adm_bank_f002' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'adm_bank_f003' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'adm_data_controls' => [
            'id' => 'UUID',
            'control_type' => 'ロック識別子',
            'version' => '楽観ロック用',
            'status' => 'ロック状況',
            'data' => 'ロック詳細',
            'deleted_at' => '削除日時',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'adm_datalake_logs' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'adm_foreign_currency_rates' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'adm_gacha_log_aggregation_progresses' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'adm_gacha_simulation_logs' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'adm_in_game_notices' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'adm_informations' => [
            'id' => 'UUID',
            'enable' => '有効フラグ',
            'banner_url' => 'バナーURL',
            'category' => 'カテゴリ',
            'title' => 'タイトル',
            'html' => 'htmlデータ',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'adm_master_import_histories' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'adm_master_import_history_versions' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'adm_master_release_version_statuses' => [
            'id' => 'UUID',
            'ocarina_validated_status' => 'オカリナ検証ステータス',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'adm_message_distribution_inputs' => [
            'id' => 'UUID',
            'create_status' => '作成ステータス',
            'title' => 'タイトル',
            'start_at' => '開始日時',
            'expired_at' => '終了日時',
            'opr_message_id' => 'mng_messages.id',
            'opr_messages_txt' => '運用メッセージテキスト',
            'opr_message_distributions_txt' => '運用メッセージ配信テキスト',
            'opr_message_i18ns_txt' => '運用メッセージ多言語テキスト',
            'target_type' => '対象タイプ',
            'target_ids_txt' => '対象IDリストテキスト',
            'display_target_id_input_type' => '対象IDリストの入力タイプ',
            'account_created_type' => 'アカウント生成タイプ',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'adm_model_has_permissions' => [
            'permission_id' => '権限ID',
            'model_type' => 'モデル種別',
            'model_id' => 'モデルID',
        ],
        'adm_model_has_roles' => [
            'role_id' => '役割ID',
            'model_type' => 'モデル種別',
            'model_id' => 'モデルID',
        ],
        'adm_permissions' => [
            'id' => 'UUID',
            'name' => '権限名',
            'description' => '権限説明',
            'guard_name' => '監視名',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'adm_posts' => [
            'id' => 'UUID',
            'title' => '件名',
            'body' => '本文',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'adm_promotion_tags' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'adm_role_has_permissions' => [
            'permission_id' => '権限ID',
            'role_id' => '役割ID',
        ],
        'adm_roles' => [
            'id' => 'UUID',
            'name' => '役割名',
            'description' => '役割説明',
            'guard_name' => '監視名',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'adm_user_ban_operate_histories' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'adm_user_deletion_operate_histories' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'adm_users' => [
            'id' => 'ID',
            'name' => '管理ツールユーザー名',
            'email' => 'メールアドレス',
            'email_verified_at' => 'メールアドレスが確認された日時',
            'password' => 'パスワード',
            'remember_token' => 'トークン',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tableCommentList as $tableName => $comment) {
            $this->addCommentToTable($tableName, $comment);
        }
        foreach ($this->columnCommentList as $tableName => $columnList) {
            $this->addCommentsToColumns($tableName, $columnList);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tableCommentList as $tableName => $comment) {
            $this->addCommentToTable($tableName, "");
        }
        foreach ($this->columnCommentList as $tableName => $columnList) {
            $this->addCommentsToColumns($tableName, array_fill_keys(array_keys($columnList), ""));
        }
    }
};


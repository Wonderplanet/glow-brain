<?php

use App\Traits\MigrationAddColumnCommentsTrait;
use App\Traits\MigrationAddTableCommentTrait;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use MigrationAddTableCommentTrait;
    use MigrationAddColumnCommentsTrait;

    private array $tableCommentList = [
        'log_ad_free_plays' => '広告視聴による無料プレイログ',
        'log_advent_battle_actions' => '降臨バトル挑戦ログ',
        'log_api_requests' => 'APIリクエストログ',
        'log_app_store_refunds' => 'AppStoreリファンドログ',
        'log_artwork_fragments' => '原画のかけら変動ログ',
        'log_bnid_links' => 'BNID連携ログ',
        'log_coins' => 'コイン変動ログ',
        'log_emblems' => 'エンブレム変動ログ',
        'log_exps' => 'EXP変動ログ',
        'log_gacha_actions' => 'ガチャ実行ログ',
        'log_gachas' => 'ガチャ排出ログ',
        'log_idle_incentive_rewards' => '探索報酬報酬ログ',
        'log_items' => 'アイテム変動ログ',
        'log_logins' => 'ログインログ',
        'log_outpost_enhancements' => 'ゲート強化変動ログ',
        'log_party_units' => 'パーティユニット変動ログ',
        'log_receive_message_rewards' => 'メッセージ報酬受取ログ',
        'log_stage_actions' => 'ステージ挑戦ログ',
        'log_staminas' => 'スタミナ変動ログ',
        'log_suspected_users' => 'チート疑惑ユーザーログ',
        'log_system_message_additions' => 'システムメッセージ追加ログ',
        'log_trade_shop_items' => 'トレードショップアイテム変動ログ',
        'log_tutorial_actions' => 'チュートリアル実行ログ',
        'log_unit_grade_ups' => 'ユニットグレードアップログ',
        'log_unit_level_ups' => 'ユニットレベルアップログ',
        'log_unit_rank_ups' => 'ユニットランクアップログ',
        'log_units' => 'ユニット変動ログ',
        'log_user_levels' => 'ユーザーレベル変動ログ',
        'log_user_profiles' => 'ユーザープロフィール変動ログ',
    ];

    private array $columnCommentList = [
        'log_ad_free_plays' => [
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'log_advent_battle_actions' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'log_allowances' => [
            'usr_user_id' => 'usr_users.id',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'log_api_requests' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'log_app_store_refunds' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'log_artwork_fragments' => [
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'log_bnid_links' => [
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'log_coins' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'log_currency_frees' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'log_currency_paids' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'log_currency_revert_histories' => [
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',

        ],
        'log_currency_revert_history_free_logs' => [
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'log_currency_revert_history_paid_logs' => [
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'log_emblems' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'log_exps' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'log_gacha_actions' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'log_gachas' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'log_idle_incentive_rewards' => [
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'log_items' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'log_logins' => [
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'log_outpost_enhancements' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'log_party_units' => [
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'log_receive_message_rewards' => [
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'log_stage_actions' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'log_staminas' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'log_stores' => [
            'id' => 'UUID',
            'usr_user_id' => 'usr_users.id',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'log_suspected_users' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'log_system_message_additions' => [
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'log_trade_shop_items' => [
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'log_tutorial_actions' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'log_unit_grade_ups' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'log_unit_level_ups' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'log_unit_rank_ups' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'log_units' => [
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'log_user_levels' => [
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'log_user_profiles' => [
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

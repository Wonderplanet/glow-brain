<?php

use App\Traits\MigrationAddColumnCommentsTrait;
use App\Traits\MigrationAddTableCommentTrait;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use MigrationAddTableCommentTrait;
    use MigrationAddColumnCommentsTrait;

    private array $tableCommentList = [
        'usr_advent_battle_sessions' => '降臨バトルのインゲームセッション管理',
        'usr_advent_battles' => '降臨バトルのステータス管理',
        'usr_artwork_fragments' => 'ユーザーの所持している原画のかけら',
        'usr_artworks' => 'ユーザーの所持している原画',
        'usr_cheat_sessions' => 'チート判定に使用するインゲーム情報の一時保存テーブル',
        'usr_comeback_bonus_progresses' => 'カムバックボーナス進捗管理',
        'usr_condition_packs' => '条件パック管理',
        'usr_device_link_passwords' => 'デバイス連携パスワード管理',
        'usr_device_link_socials' => 'デバイス連携ソーシャルアカウント管理',
        'usr_devices' => 'ユーザーデバイス管理',
        'usr_emblems' => 'エンブレム所持管理',
        'usr_gacha_uppers' => 'ガシャの天井管理',
        'usr_gachas' => 'ガシャ管理',
        'usr_idle_incentives' => '探索ステータス管理',
        'usr_items' => 'アイテム所持管理',
        'usr_messages' => 'メールボックスのメール管理',
        'usr_mission_daily_bonuses' => 'デイリーボーナスのステータス管理',
        'usr_mission_event_daily_bonus_progresses' => 'イベントミッションデイリーボーナス進捗管理',
        'usr_mission_event_daily_bonuses' => 'イベントデイリーボーナスのステータス管理',
        'usr_mission_events' => 'イベントミッションのステータス管理',
        'usr_mission_limited_terms' => '期間限定ミッションのステータス管理',
        'usr_mission_statuses' => 'ミッションステータス管理',
        'usr_outpost_enhancements' => 'ゲート強化ステータス管理',
        'usr_outposts' => 'ゲートステータス管理',
        'usr_parties' => 'パーティ編成管理',
        'usr_received_unit_encyclopedia_rewards' => 'ユーザーが受け取った図鑑報酬のID',
        'usr_shop_items' => 'ショップアイテム購入状況管理',
        'usr_shop_passes' => '購入したパス管理',
        'usr_stage_enhances' => '強化クエストステージのステータス管理',
        'usr_stage_events' => 'イベントクエストステージのステータス管理',
        'usr_stage_sessions' => 'ステージのインゲームセッション管理',
        'usr_stages' => 'ステージのステータス管理',
        'usr_store_products' => 'ショッププロダクトの購入状況管理',
        'usr_temporary_individual_messages' => 'ユーザー個別メッセージ一時保存テーブル',
        'usr_tutorial_gachas' => 'チュートリアルガチャ管理',
        'usr_tutorials' => 'チュートリアル管理',
        'usr_unit_summaries' => 'ユニット要約管理',
        'usr_units' => '所持ユニット管理',
        'usr_user_buy_counts' => 'スタミナ購入状況管理',
        'usr_user_logins' => 'ユーザーログイン回数記録',
        'usr_user_parameters' => 'コインなどのユーザーリソース管理',
        'usr_user_profiles' => 'ユーザープロフィール管理',
        'usr_users' => 'ユーザー基本情報',
    ];

    private array $columnCommentList = [
        'usr_advent_battle_sessions' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_advent_battles' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_artwork_fragments' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_artworks' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_cheat_sessions' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_comeback_bonus_progresses' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_condition_packs' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_currency_frees' => [
            'usr_user_id' => 'usr_users.id',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
            'deleted_at' => '削除日時のタイムスタンプ',
        ],
        'usr_currency_paids' => [
            'usr_user_id' => 'usr_users.id',
            'receipt_unique_id' => 'このレコードを生成した購入レシートID（購入の場合）',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
            'deleted_at' => '削除日時のタイムスタンプ',
        ],
        'usr_currency_summaries' => [
            'usr_user_id' => 'usr_users.id',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
            'deleted_at' => '削除日時のタイムスタンプ',
        ],
        'usr_device_link_passwords' => [
            'id' => 'UUID',
            'usr_user_id' => 'usr_users.id',
            'auth_id' => '認証ID',
            'auth_password' => '認証パスワード',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_device_link_socials' => [
            'id' => 'ID',
            'usr_user_id' => 'usr_users.id',
            'auth_type' => '認証タイプ',
            'auth_token' => '認証トークン',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_devices' => [
            'id' => 'UUID',
            'usr_user_id' => 'usr_users.id',
            'uuid' => 'デバイスのUUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_emblems' => [
            'id' => 'UUID',
            'usr_user_id' => 'usr_users.id',
            'mst_emblem_id' => 'mst_emblems.id',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_enemy_discoveries' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_gacha_uppers' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_gachas' => [
            'id' => 'UUID',
            'usr_user_id' => 'usr_users.id',
            'expires_at' => '終了日時',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_idle_incentives' => [
            'id' => 'UUID',
            'diamond_quick_receive_count' => '一次通貨でのクイック獲得回数',
            'diamond_quick_receive_at' => '一次通貨でクイック獲得を実行した時刻',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_item_trades' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_items' => [
            'id' => 'UUID',
            'usr_user_id' => 'usr_users.id',
            'mst_item_id' => 'mst_items.id',
            'amount' => '名前',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_jump_plus_rewards' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_messages' => [
            'id' => 'UUID',
            'usr_user_id' => 'usr_users.id',
            'opr_message_id' => 'opr_messages.id',
            'resource_type' => '報酬タイプ',
            'resource_id' => '報酬リソースID',
            'resource_amount' => '報酬の個数',
            'title' => 'opr_messages.idがない時に使用するタイトル',
            'body' => 'opr_messages.idがない時に使用する本文',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_mission_daily_bonuses' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_mission_event_daily_bonus_progresses' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_mission_event_daily_bonuses' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_mission_events' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_mission_limited_terms' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_mission_normals' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_mission_statuses' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_outpost_enhancements' => [
            'id' => 'UUID',
            'usr_user_id' => 'usr_users.id',
            'mst_outpost_id' => 'mst_outposts.id',
            'mst_outpost_enhancement_id' => 'mst_outpost_enhancements.id',
            'level' => 'レベル',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_outposts' => [
            'id' => 'UUID',
            'usr_user_id' => 'usr_users.id',
            'mst_outpost_id' => 'mst_outposts.id',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_parties' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_received_unit_encyclopedia_rewards' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_shop_items' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_shop_passes' => [
            'id' => 'UUID',
            'usr_user_id' => 'usr_users.id',
            'mst_shop_pass_id' => 'mst_shop_passes.id',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_stage_enhances' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_stage_events' => [
            'id' => 'UUID',
            'usr_user_id' => 'usr_users.id',
            'mst_stage_id' => 'mst_stages.id',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_stage_sessions' => [
            'id' => 'UUID',
            'usr_user_id' => 'usr_users.id',
            'mst_stage_id' => 'mst_stages.id',
            'is_valid' => 'ステージ挑戦中フラグ',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_stages' => [
            'id' => 'UUID',
            'usr_user_id' => 'usr_users.id',
            'mst_stage_id' => 'mst_stages.id',
            'clear_status' => 'クリアステータス',
            'clear_count' => 'クリア回数',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_store_allowances' => [
            'usr_user_id' => 'usr_users.id',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_store_infos' => [
            'usr_user_id' => 'usr_users.id',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
            'deleted_at' => '削除日時のタイムスタンプ',
        ],
        'usr_store_product_histories' => [
            'usr_user_id' => 'usr_users.id',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
            'deleted_at' => '削除日時のタイムスタンプ',
        ],
        'usr_store_products' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_temporary_individual_messages' => [
            'id' => 'UUID',
            'usr_user_id' => 'usr_users.id',
            'opr_message_id' => 'opr_messages.id',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_tutorial_gachas' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_tutorials' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_unit_summaries' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_units' => [
            'id' => 'UUID',
            'usr_user_id' => 'usr_users.id',
            'mst_unit_id' => 'mst_units.id',
            'level' => 'ユニットのレベル',
            'rank' => 'ユニットのランク',
            'grade_level' => 'ユニットのグレード',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_user_buy_counts' => [
            'id' => 'UUID',
            'usr_user_id' => 'usr_users.id',
            'daily_buy_stamina_ad_count' => '1日の広告視聴でのスタミナ購入回数',
            'daily_buy_stamina_ad_at' => '1日の広告視聴してスタミナを購入した日時',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_user_logins' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_user_parameters' => [
            'id' => 'UUID',
            'usr_user_id' => 'usr_users.id',
            'level' => 'ユーザーレベル',
            'exp' => '経験値',
            'coin' => '無償通貨',
            'stamina' => 'スタミナ',
            'stamina_updated_at' => 'スタミナ更新タイムスタンプ',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_user_profiles' => [
            'id' => 'UUID',
            'usr_user_id' => 'usr_users.id',
            'my_id' => 'MYID',
            'name' => '名前',
            'is_change_name' => '名前を変更したか',
            'mst_avatar_id' => 'アバターID',
            'mst_avatar_frame_id' => 'アバターのフレームID',
            'name_update_at' => '名前変更日時のタイムスタンプ',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'usr_users' => [
            'id' => 'UUID',
            'tos_version' => '同意した利用規約のバージョン 同意モジュールを使っているので未使用列',
            'privacy_policy_version' => '同意したプライバシーポリシーのバージョン 同意モジュールを使っているので未使用列',
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

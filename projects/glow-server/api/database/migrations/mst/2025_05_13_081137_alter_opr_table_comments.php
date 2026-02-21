<?php

use App\Domain\Constants\Database;
use App\Traits\MigrationAddColumnCommentsTrait;
use App\Traits\MigrationAddTableCommentTrait;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use MigrationAddTableCommentTrait;
    use MigrationAddColumnCommentsTrait;

    protected $connection = Database::MST_CONNECTION;

    private array $tableCommentList = [
        'opr_asset_release_controls' => 'アセット配信制御設定',
        'opr_asset_release_versions' => 'アセット配信バージョン情報設定',
        'opr_asset_releases' => 'リリース済みアセット情報設定',
        'opr_campaigns' => 'インゲームコンテンツに対するキャンペーン設定',
        'opr_campaigns_i18n' => 'キャンペーン説明などの多言語設定',
        'opr_client_versions' => 'クライアントバージョンごとの対応設定。強制アップデート必須など。',
        'opr_content_closes' => '機能単位で利用停止するための設定',
        'opr_gacha_prizes' => 'ガシャの排出物設定',
        'opr_gacha_uppers' => 'ガシャの天井設定',
        'opr_gacha_use_resources' => 'ガシャを引くために必要なリソースの設定',
        'opr_gachas' => 'ガシャの基本設定',
        'opr_gachas_i18n' => 'ガシャ名などの多言語設定',
        'opr_in_game_notices' => 'インゲームノーティスの設定',
        'opr_in_game_notices_i18n' => 'インゲームノーティスの文言やバナーなどの表示内容の設定',
        'opr_master_release_controls' => 'マスター配信制御設定',
        'opr_master_release_versions' => 'マスター配信バージョン情報設定',
        'opr_master_releases' => 'リリース済みマスター情報設定',
    ];

    private array $columnCommentList = [
        'opr_asset_release_controls' => [
            'id' => 'UUID',
            'release_key' => 'リリースキー',
            'version' => 'バージョン指定',
            'platform' => 'プラットフォーム指定',
            'version_no' => 'バージョンナンバー',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'opr_asset_release_versions' => [
            'id' => 'UUID',
            'build_client_version' => 'ビルドクライアントバージョン',
            'asset_total_byte_size' => 'アセット合計容量',
            'catalog_byte_size' => 'カタログ容量',
            'catalog_file_name' => 'カタログ名',
            'catalog_hash_file_name' => 'カタログハッシュファイル名',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'opr_asset_releases' => [
            'id' => 'UUID',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'opr_campaigns' => [
            'id' => 'UUID',
            'release_key' => 'リリースキー',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'opr_campaigns_i18n' => [
            'id' => 'UUID',
            'release_key' => 'リリースキー',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'opr_client_versions' => [
            'id' => 'UUID',
            'client_version' => 'クライアントバージョン',
            'platform' => 'プラットフォーム',
            'is_force_update' => '強制アップデートするか',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
            'release_key' => 'リリースキー',
        ],
        'opr_content_closes' => [
            'id' => 'UUID',
            'release_key' => 'リリースキー',
            'content_type' => 'コンテンツタイプ',
        ],
        'opr_gacha_display_units_i18n' => [
            'id' => 'UUID',
        ],
        'opr_gacha_prizes' => [
            'id' => 'UUID',
            'release_key' => 'リリースキー',
            'resource_type' => 'ガシャの消費リソースのタイプ',
            'resource_id' => 'ガシャの消費リソースid',
            'resource_amount' => 'ガシャの消費リソース量',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'opr_gacha_uppers' => [
            'id' => 'UUID',
            'release_key' => 'リリースキー',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'opr_gacha_use_resources' => [
            'id' => 'UUID',
            'cost_type' => 'ガシャで使用するコストのタイプ',
            'release_key' => 'リリースキー',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'opr_gachas' => [
            'id' => 'UUID',
            'gacha_type' => 'ガシャのタイプ',
            'release_key' => 'リリースキー',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'opr_gachas_i18n' => [
            'id' => 'UUID',
            'release_key' => 'リリースキー',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'opr_in_game_notices' => [
            'id' => 'UUID',
            'release_key' => 'リリースキー',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'opr_in_game_notices_i18n' => [
            'id' => 'UUID',
            'release_key' => 'リリースキー',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'opr_jump_plus_reward_schedules' => [
            'id' => 'UUID',
            'release_key' => 'リリースキー',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'opr_jump_plus_rewards' => [
            'id' => 'UUID',
            'release_key' => 'リリースキー',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'opr_master_release_controls' => [
            'id' => 'UUID',
            'release_key' => 'リリースキー',
            'client_data_hash' => 'クライアント共通データのハッシュ値',
            'zh-Hant_client_i18n_data_hash' => 'クライアント多言語データ（繁体字中国語）のハッシュ値',
            'en_client_i18n_data_hash' => 'クライアント多言語データ（英語）のハッシュ値',
            'ja_client_i18n_data_hash' => 'クライアント多言語データ（日本語）のハッシュ値',
            'client_opr_data_hash' => 'クライアント運用データのハッシュ値',
            'zh-Hant_client_opr_i18n_data_hash' => 'クライアント運用多言語データ（繁体字中国語）のハッシュ値',
            'en_client_opr_i18n_data_hash' => 'クライアント運用多言語データ（英語）のハッシュ値',
            'ja_client_opr_i18n_data_hash' => 'クライアント運用多言語データ（日本語）のハッシュ値',
            'created_at' => '作成日時のタイムスタンプ',
            'updated_at' => '更新日時のタイムスタンプ',
        ],
        'opr_master_release_versions' => [
            'id' => 'UUID',
            'server_db_hash' => 'サーバーDBのハッシュ値',
            'client_mst_data_hash' => 'クライアントマスターデータのハッシュ値',
            'client_mst_data_i18n_ja_hash' => 'クライアントマスターデータ多言語（日本語）のハッシュ値',
            'client_mst_data_i18n_en_hash' => 'クライアントマスターデータ多言語（英語）のハッシュ値',
            'client_mst_data_i18n_zh_hash' => 'クライアントマスターデータ多言語（繁体字中国語）のハッシュ値',
            'client_opr_data_hash' => 'クライアント運用データのハッシュ値',
            'client_opr_data_i18n_ja_hash' => 'クライアント運用データ多言語（日本語）のハッシュ値',
            'client_opr_data_i18n_en_hash' => 'クライアント運用データ多言語（英語）のハッシュ値',
            'client_opr_data_i18n_zh_hash' => 'クライアント運用データ多言語（繁体字中国語）のハッシュ値',
        ],
        'opr_master_releases' => [
            'id' => 'UUID',
        ],
        'opr_products' => [
            'id' => 'UUID',
            'mst_store_product_id' => 'mst_store_products.id',
            'product_type' => '商品タイプ',
            'release_key' => 'リリースキー',
        ],
        'opr_stage_continue_ad_schedules' => [
            'id' => 'UUID',
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

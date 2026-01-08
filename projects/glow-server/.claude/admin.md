# Laravel Filament Admin開発ドキュメント - /init相当

## 概要

**glow-server**のadmin側は、Laravel 11 + PHP 8.3 + Filament v3を使用した管理ツールです。ゲームサーバーの運用管理、マスターデータ管理、ユーザーサポート機能を提供します。

## 技術スタック

### 主要フレームワーク・ライブラリ
- **Laravel**: 11.0
- **PHP**: 8.3
- **Filament**: 3.0-stable (Laravel管理画面フレームワーク)
- **Spatie Laravel Permission**: 6.0 (権限管理)
- **TipTap Editor**: 3.0 (リッチテキストエディタ)

### 依存関係（Wonderplanet独自ライブラリ）
- `laravel-wp-billing` - 課金システム
- `laravel-wp-currency` - 通貨管理
- `laravel-wp-common-admin` - 共通管理機能
- `laravel-wp-master-asset-release-admin` - マスターデータ・アセット管理

## ディレクトリ構造

```
admin/
├── app/
│   ├── Constants/           # 定数・列挙型
│   ├── Entities/           # データ転送オブジェクト
│   ├── Filament/           # Filament関連ファイル
│   │   ├── Pages/         # カスタムページ
│   │   ├── Resources/     # CRUD操作画面
│   │   └── Tables/        # テーブル関連コンポーネント
│   ├── Livewire/          # Livewireコンポーネント
│   ├── Models/            # Eloquentモデル
│   ├── Operators/         # 外部API連携
│   └── Services/          # ビジネスロジック
├── config/
│   ├── admin.php          # 管理ツール設定
│   ├── filament.php       # Filament設定
│   └── database.php       # 複数DB接続設定
├── database/
│   ├── migrations/        # 管理ツール専用マイグレーション
│   └── seeders/          # 初期データ（管理者ユーザーなど）
└── routes/
    └── web.php            # Webルート
```

## 環境セットアップ

### 1. 初回セットアップ
```bash
# 完全な環境セットアップ（API側と共通）
./tools/setup.sh

# Dockerコンテナ起動
./tools/bin/sail-wp up -d
```

### 2. admin専用コマンド実行
admin側に対してコマンドを実行する場合は、`sail admin`プレフィックスを使用：
```bash
# マイグレーション実行
./tools/bin/sail-wp admin artisan migrate

# 初期データシード
./tools/bin/sail-wp admin artisan db:seed

# Tinker起動
./tools/bin/sail-wp admin artisan tinker
```

### 3. アクセス情報
- **URL**: http://localhost:8081/admin
- **初期ログイン**:
  - メールアドレス: `admin@wonderpla.net`
  - パスワード: `admin`

## データベース構成

### 複数データベース接続
admin側では4つのデータベースに接続：

```php
'connections' => [
    'admin' => [/*admin専用データベース*/],
    'api' => [/*メインアプリケーションデータ*/],
    'mst' => [/*マスターデータ*/],
    'mng' => [/*運用・管理データ*/],
]
```

### 主要テーブル群
- **adm_***: 管理ツール専用テーブル（ユーザー、権限、設定など）
- **usr_***: ゲームユーザーデータ（読み取り専用）
- **mst_***: マスターデータ（ゲーム設定データ）
- **log_***: ログデータ（ユーザー行動履歴）

## 認証・権限管理

### 認証システム
- **認証ガード**: Session-based (管理者用)
- **ユーザーモデル**: `App\Models\Adm\AdmUser`
- **ログイン制限**: 5回失敗で10800分（3時間）ロック

### 権限体系
- **Admin**: 全機能アクセス可能
- **Developer**: 管理ツール設定以外の機能にアクセス可能

### ロール・パーミッション
```php
// 初期ロール
'Admin' => 'AdministratorAccess'
'Developer' => 'PowerUserAccess'
```

## Filament機能構成

### Resource（CRUD操作画面）
主要なResourceクラス：
- **AdmUserResource**: 管理者ユーザー管理
- **MessageResource**: ゲーム内メッセージ配信
- **MstAdventBattleResource**: アドベントバトル設定
- **UsrUserResource**: ゲームユーザー管理
- **LogCurrencyRevertResource**: 通貨復旧履歴

### Pages（カスタム管理ページ）
- **UserSearch**: ユーザー検索・詳細表示
- **LogCurrencyHistory**: 通貨履歴分析
- **GachaSimulator**: ガチャシミュレーション
- **SystemMaintenance**: メンテナンス管理
- **MasterDataDiff**: マスターデータ差分確認

### Actions（カスタム操作）
- **ImportAction**: マスターデータインポート
- **GitApplyAction**: Git操作連携
- **AssetAction**: アセット管理操作

## 開発コマンド

### Resource作成
```bash
# 新しいCRUD画面作成
./tools/bin/sail-wp admin artisan make:filament-resource Customer

# 生成されるファイル構造
app/Filament/Resources/
├── CustomerResource.php
└── CustomerResource/
    └── Pages/
        ├── CreateCustomer.php
        ├── EditCustomer.php
        └── ListCustomers.php
```

### テスト実行
```bash
# admin側テスト実行
./tools/bin/sail-wp admin test

# カバレッジ付きテスト
./tools/bin/sail-wp admin test --coverage
```

### コード品質チェック
```bash
# 全品質チェック
./tools/bin/sail-wp admin check

# 個別チェック
./tools/bin/sail-wp admin phpcs
./tools/bin/sail-wp admin phpstan
```

## 重要な設定ファイル

### admin.php設定
```php
return [
    'loginLimit' => [
        'maxAttempts' => 5,
        'decayMinutes' => 10800,
    ],
    'repositoryUrl' => 'https://github.com/Wonderplanet/glow-masterdata.git',
    'adminApiDomain' => [/*環境別ドメイン設定*/],
];
```

### 環境別ドメイン
- **local**: http://host.docker.internal:8081
- **develop**: https://develop-admin.glow.nappers.jp
- **qa**: https://admin.qa.glow.nappers.jp
- **staging**: https://admin.staging.glow.nappers.jp

## 外部システム連携

### Git連携
- **マスターデータリポジトリ**: glow-masterdata
- **クライアントリポジトリ**: glow-client
- **自動取り込み**: スプレッドシート→CSV→DB

### AWS/GCP連携
- **S3**: アセットファイル管理
- **Google Drive**: スプレッドシート連携
- **Cloud Storage**: Datalake転送

### 通知システム
- **Slack**: 各種アラート通知
- **メール**: ガチャシミュレーション結果

## セキュリティ要件

### アクセス制御
- **IPアドレス制限**: BNEドメインからのアクセスのみ許可
- **2要素認証**: 本番環境では必須
- **セッション管理**: 120分でタイムアウト

### データ保護
- **個人情報**: ユーザーデータへの適切なアクセス制御
- **監査ログ**: 全操作履歴の記録
- **暗号化**: 機密データの暗号化保存

## 開発時の注意点

### パフォーマンス
- **大量データ**: ページネーション必須
- **複雑クエリ**: インデックス最適化
- **リアルタイム**: 必要に応じてキャッシュ活用

### データ整合性
- **トランザクション**: 複数DB間での整合性保証
- **レプリカ遅延**: 読み取り専用データの遅延考慮
- **マスターデータ**: バージョン管理の徹底

### デバッグ
- **Laravel Debugbar**: 開発環境で有効
- **ログレベル**: 環境別適切な設定
- **エラーハンドリング**: ユーザーフレンドリーなメッセージ

このドキュメントに基づいて、admin側の開発をスムーズに進めることができます。Filamentの豊富な機能を活用して、効率的な管理ツールの構築が可能です。

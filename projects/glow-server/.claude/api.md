# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## 開発コマンド

### 環境セットアップ
```bash
./tools/setup.sh                    # 完全な環境セットアップ
./tools/bin/sail-wp up -d           # Dockerコンテナ起動
./tools/bin/sail-wp down            # コンテナ停止
```

### API開発 (Laravel 11, PHP 8.3)
```bash
./tools/bin/sail-wp artisan migrate                 # データベースマイグレーション実行
./tools/bin/sail-wp artisan migrate:rollback        # マイグレーションをロールバック
./tools/bin/sail-wp artisan tinker                  # 対話型シェル
./tools/bin/sail-wp artisan queue:work              # ジョブキューの処理
```

### テスト実行
```bash
./tools/bin/sail-wp test                            # 全テスト実行
./tools/bin/sail-wp test --filter=TestName         # 特定のテスト実行
./tools/bin/sail-wp test --coverage                # カバレッジ付きテスト
```

### コード品質チェック
```bash
./tools/bin/sail-wp check           # 全品質チェック実行 (phpcs, phpstan, deptrac)
./tools/bin/sail-wp phpcs           # コーディング規約チェック (PSR-12 + Slevomat)
./tools/bin/sail-wp phpstan         # 静的解析 (Level 6)
./tools/bin/sail-wp deptrac         # アーキテクチャ依存関係検証
```

## APIアーキテクチャ

**glow-server**は、ドメイン駆動設計に基づいて構築されたモジュラーモノリス型のゲームサーバーです。

### 主要ドメイン
Auth, User, Currency, Gacha, PvP, Mission, Stage, Shop など30以上のドメインで構成

### アーキテクチャ詳細
ドメイン構造、レイヤー責務、依存関係ルールの詳細は「アーキテクチャドキュメント参照ガイド」セクションを参照してください。

## APIルーティング

### ルート構成
- 全APIルートは `/api/routes/api.php` で定義
- ドメイン別のコントローラーグループで整理
- RESTful規約に基づくがゲーム特化のアクション含む

### ミドルウェアスタック（保護されたルート）
```php
Route::middleware([
    'auth:api',              // JWT認証
    'user_status_check',     // ユーザー状態チェック
    'client_version_check',  // クライアントバージョン確認
    'asset_version_check',   // アセットバージョン確認
])->group(function () {
    // APIエンドポイント
});
```

## APIリクエスト・レスポンス処理

### リクエスト処理パターン
- **BaseApiRequest**: 全APIリクエストの基底クラス
- **自動型キャスト**: パラメータの自動変換システム
- **ヘッダー処理**: プラットフォーム、言語、バージョン検出

### レスポンス形式
- **ResponseFactory**: 各コントローラー専用のレスポンスファクトリー
- **一貫したデータ構造**: 全レスポンスが同じ形式に従う
- **ResponseDataFactory**: 中央化されたレスポンスデータ生成

### エラーハンドリング階層
1. **GameException**: ゲーム固有エラー（ゲームエラーコード付き）
2. **WpBillingException**: 課金システムエラー
3. **WpCurrencyException**: 通貨システムエラー
4. **ValidationException**: リクエスト検証エラー
5. **RetryableException**: 再試行可能な一時エラー

## 認証・暗号化

### 認証システム
- **カスタムアクセストークンガード**: RSAキーベースのJWT認証
- **デバイス連携**: パスワードおよびソーシャルログイン連携サポート
- **プラットフォーム検出**: iOS/Android固有の処理

### 必須ヘッダー
```
X-Platform: iOS/Android
X-Language: ユーザー言語設定
X-Client-Version: アプリバージョン
Authorization: Bearer <token>
```

## テスト開発パターン

### テスト基盤
- **BaseTestCase**: 複数DB接続対応の包括的テスト基盤
- **ファクトリーパターン**: モデルファクトリーによるテストデータ生成
- **モックサービス**: デバッグサービスのモック化
- **DBトランザクション**: 各テストは複数DB間でトランザクション実行

### APIテスト用ユーティリティ
```php
// テストユーザー作成
protected function createDummyUser()
protected function setUsrUserId(string $usrUserId)

// 時間操作（テスト用）
protected function fixTime(?string $dateTime = null)
protected function setTestNow(Carbon|CarbonImmutable|string|null $datetime = null)

// 通貨テストヘルパー
protected function createDiamond(string $usrUserId, int $freeDiamond = 0)
protected function getDiamond(string $usrUserId)
```

## データベース構成

### 複数接続構成
- **mysql** - メインアプリケーションデータ
- **mst** - マスター・静的ゲームデータ
- **admin** - 管理用データ
- **mng** - 運用・管理データ

### マイグレーション管理
- ドメイン別・リリースバージョン別に整理
- 中央化されたゲーム設定データ管理
- 全ユーザーアクション、取引、バトル結果の包括的ログ

## 開発ワークフロー

### 新機能開発時
1. **ドメイン選択**: 機能に適したドメインを選択
2. **実装**: 下記「主要ルール」に従ってレイヤー別に実装
3. **DB変更**: ドメイン固有ディレクトリにマイグレーション作成
4. **品質ゲート**: 全コードはphpcs, phpstan, deptrac, テストを通過必須

※ 詳細は「アーキテクチャドキュメント参照ガイド」のタスク別参照ガイドを確認

### バージョン管理戦略
- **クライアントバージョンチェック**: ミドルウェアによる互換性検証
- **マスターデータバージョニング**: アセットとマスターデータの個別バージョン管理
- **後方互換性**: バージョン不一致の適切な処理

## ゲーム固有の技術考慮事項

### ゲーム機能の実装要件
- **ガチャシステム**: 精密な確率計算と天井機能
- **PvPバトル**: リアルタイムデータ整合性とチート対策
- **通貨取引**: 監査証跡付きアトミック取引
- **ミッションシステム**: 複数ゲームアクション間の複雑な進行管理

### パフォーマンス・セキュリティ
- 全API リクエスト/レスポンスの暗号化
- 頻繁にアクセスされるゲームデータのRedisキャッシング
- Datadog APM統合による監視
- リクエスト検証とレート制限の実装

## アーキテクチャドキュメント参照ガイド

詳細なアーキテクチャドキュメントは `docs/01_project/architecture/` に配置されています。

### ドキュメント一覧

| No. | ファイル | 内容 |
|-----|---------|------|
| 00 | `アーキテクチャ概要.md` | 全体像、技術スタック、基本思想 |
| 01 | `レイヤードアーキテクチャ.md` | 各レイヤーの責務 |
| 02 | `モジュラーモノリス.md` | ドメイン分割、Delegatorパターン |
| 03 | `ディレクトリ構造.md` | 詳細なディレクトリ構成 |
| 04 | `依存関係ルール.md` | 依存関係の制約、deptrac |
| 05 | `データフロー.md` | リクエスト〜レスポンスの流れ |
| 06 | `共通基盤.md` | トランザクション、エラーハンドリング |
| 07 | `テスト戦略.md` | テスト方針、レイヤーごとの戦略 |

### タスク別参照ガイド

| タスク種別 | 参照ドキュメント |
|-----------|-----------------|
| 新規APIエンドポイント実装 | 00 → 01 → 03 → 04 |
| UseCase実装 | 01 → 05 → 06 |
| 他ドメイン連携 | 02 → 04 |
| テスト実装 | 07 |
| 既存コード調査・バグ修正 | 00 → 05 |

### 主要ルール（クイックリファレンス）

#### 依存関係
```
Controller → UseCase のみ
UseCase/Service → Service, Repository, Delegator
他ドメインへのアクセス → Delegator経由
```

#### 特例ルール
- **Resource/Mst（マスタデータ）**: 全ドメインから直接アクセス可
- **Gameドメイン**: 他ドメインService/Repositoryに直接アクセス可（課金除く）
- **Common**: 全ドメインから直接アクセス可

#### Delegator使用時の注意
- ユーザーデータを返す場合は **Entity に変換** してから返す
- プリミティブ型はそのまま返却可能


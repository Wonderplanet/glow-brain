# サーバーAPI機能要件実装設計

## 1. ドキュメント情報
- 対象機能: 交換所
- 作成日: 2025-11-26
- 参照ドキュメント:
  - 05_サーバーAPI要件書.md
  - APIコーディング規約: @docs/01_project/coding-standards/api-coding-standards.md
  - マスタデータ配信機構: @docs/01_project/architecture/マスタデータ配信機構.md

## 2. 実装設計概要

### 2.1 実装方針

**基本方針:**
- 既存のショップアイテム機能のアーキテクチャパターンを踏襲し、コードの一貫性を保つ
- クリーンアーキテクチャに基づき、ドメイン層の分離と責務の明確化を維持
- 複数リソース消費という新規要件に対して、拡張性のある設計を採用
- N+1問題を回避し、パフォーマンスを重視した実装とする

**既存実装との整合性:**
- `Domain\Shop`ドメインの設計パターンを参考にする
- `UsrModelManager`によるキャッシュ管理パターンを踏襲
- `RewardDelegator`を使用した報酬配布パターンを再利用
- `applyUserTransactionChanges`によるトランザクション管理パターンを採用

**実装時の重要な判断基準:**
- 新規ドメイン`Domain\Exchange`を作成し、独立した機能として実装
- マスタデータ配信機構に従い、マスタテーブルは参照のみ
- 複数リソース消費はループ処理で順次実行し、トランザクション内で原子性を保証
- リセット処理はリクエスト時判定方式を採用（バッチ処理ではない）

### 2.2 実装の全体像

**新規追加されるコンポーネント:**

1. **API層:**
   - `ExchangeController`: 交換所一覧取得、特定交換所取得、交換実行の3つのエンドポイントを提供

2. **Domain層（`Domain\Exchange`）:**
   - `ExchangeDelegator`: Controllerから呼ばれるファサード
   - `ExchangeGetListUseCase`: 交換所一覧取得のユースケース
   - `ExchangeGetByIdUseCase`: 特定交換所取得のユースケース
   - `ExchangeLineupTradeUseCase`: 交換実行のユースケース
   - `ExchangeService`: 交換所関連のドメインロジック
   - `ExchangeLineupService`: ラインナップ関連のドメインロジック
   - `MstExchangeEntity`, `MstExchangeLineupEntity`, `MstExchangeLineupCostEntity`: マスタEntityクラス
   - `UsrExchangeLineup`: ユーザー交換履歴モデル
   - 各種Repository: マスタデータとユーザーデータの取得・更新

3. **DB層:**
   - `mst_exchanges`: 交換所マスタテーブル
   - `mst_exchange_lineups`: ラインナップマスタテーブル
   - `mst_exchange_lineup_costs`: ラインナップコストマスタテーブル
   - `usr_exchange_lineups`: ユーザー交換履歴テーブル
   - `log_trade_exchange_lineups`: 交換ログテーブル

**既存コンポーネントへの影響範囲:**
- `RewardDelegator`: 新しいRewardクラス（`ExchangeLineupReward`）を追加
- `AppCurrencyDelegator`, `UsrItemService`: 既存のコスト消費メソッドを利用（変更なし）
- マスタデータ配信機構: 新規マスタテーブルをS3配信対象に追加

**アーキテクチャ上の考慮点:**
- ドメイン分離: `Domain\Exchange`として独立したドメインを作成
- 既存ドメインへの依存: `Domain\Reward`, `Domain\Currency`, `Domain\Item`を利用
- クリーンアーキテクチャの遵守: Controller → Delegator → UseCase → Service → Repository の階層構造
- トランザクション境界: UseCaseレベルでトランザクション管理

## 3. 機能要件別実装設計

### 3.1 マスタデータ参照・管理

#### 要件 M-1: 交換所マスタの管理と取得

##### 3.1.1 要件概要
- **要件ID:** REQ-M-1
- **実現内容:** 有効な交換所マスタを取得し、開催期間・カテゴリー・有効フラグに基づいて表示対象を絞り込む

##### 3.1.2 API設計

**新規API追加 / 既存API改修:**
- [x] 新規API追加
- [ ] 既存API改修

**対象エンドポイント:**
- エンドポイント: `GET /api/exchanges`
- HTTPメソッド: GET
- 認証: 必要

**リクエストパラメータ（JSON形式）:**
なし（クエリパラメータなし）

**レスポンス構造（JSON形式）:**
```json
{
  "exchange_shops": [
    {
      "id": "normal_exchange_001",
      "name": "通常交換所",
      "category": "NORMAL",
      "banner_url": "https://example.com/banners/normal_shop.png",
      "start_at": null,
      "end_at": null,
      "lineups": [...]
    }
  ]
}
```

**レスポンスフィールド説明:**
| フィールド名 | 型 | 説明 |
|-------------|-----|------|
| exchange_shops | array | 交換所の配列 |
| exchange_shops[].id | string | 交換所ID |
| exchange_shops[].name | string | 交換所名 |
| exchange_shops[].category | string | カテゴリー（NORMAL/EVENT/FRAGMENT_BOX） |
| exchange_shops[].banner_url | string | バナー画像URL |
| exchange_shops[].start_at | string\|null | 開催開始日時（ISO 8601形式、nullは無期限） |
| exchange_shops[].end_at | string\|null | 開催終了日時（ISO 8601形式、nullは無期限） |
| exchange_shops[].lineups | array | ラインナップ配列（詳細は要件M-2参照） |

##### 3.1.3 ドメイン設計

**新規ドメイン追加 / 既存ドメイン改修:**
- [x] 新規ドメイン追加
- [ ] 既存ドメイン改修

**対象ドメイン:**
- ドメイン分類: 通常ドメイン
- ドメイン名: `Domain\Exchange`

**ファイル構成:**

*新規作成が必要なファイル:*
- [x] `api/app/Domain/Exchange/Entities/MstExchangeEntity.php`
- [x] `api/app/Domain/Exchange/Repositories/MstExchangeRepository.php`
- [x] `api/app/Domain/Exchange/Services/ExchangeService.php`
- [x] `api/app/Domain/Exchange/UseCases/ExchangeGetListUseCase.php`
- [x] `api/app/Domain/Exchange/Delegators/ExchangeDelegator.php`
- [x] `api/app/Http/Controllers/Api/ExchangeController.php`
- [x] `api/app/Domain/Exchange/ResponseFactories/ExchangeResponseFactory.php`

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| ExchangeController | getList() | GET /api/exchanges エンドポイント | ルーティングから呼ばれる |
| ExchangeDelegator | getExchangeList() | 交換所一覧取得の実行 | Controllerから呼ばれる、return型はarray |
| ExchangeGetListUseCase | execute() | 交換所一覧取得のビジネスロジック | Delegatorから呼ばれる |
| ExchangeService | getActiveExchanges() | 有効な交換所マスタを取得 | 開催期間・有効フラグでフィルタ |
| MstExchangeRepository | getAll() | 交換所マスタを全件取得 | Eloquentでの取得 |
| MstExchangeRepository | getById() | 交換所マスタをIDで取得 | 単一取得用 |
| ExchangeResponseFactory | createExchangeListResponse() | レスポンス構造を作成 | 日時データをISO8601形式に変換 |

**Entity設計:**
- Entityタイプ: Eloquent Entity
- 主要プロパティ: `id`, `name`, `category`, `banner_url`, `start_at`, `end_at`, `sort_order`, `is_active`, `created_at`, `updated_at`
- 既存Entityとの関係: なし（独立したEntity）

##### 3.1.4 DB設計

**テーブル構造追加 / テーブル構造変更:**
- [x] テーブル構造追加（新規テーブル作成）
- [ ] テーブル構造変更（既存テーブル変更）

**新規テーブル作成の場合:**

*テーブル名:* `mst_exchanges`

*CREATE TABLE文:*
```sql
CREATE TABLE mst_exchanges (
    id VARCHAR(255) NOT NULL COMMENT '交換所ID',
    name VARCHAR(255) NOT NULL COMMENT '交換所名',
    category ENUM('NORMAL','EVENT','FRAGMENT_BOX') NOT NULL COMMENT 'カテゴリー',
    banner_url VARCHAR(500) NOT NULL COMMENT 'バナー画像URL',
    start_at DATETIME(6) NULL COMMENT '開催開始日時',
    end_at DATETIME(6) NULL COMMENT '開催終了日時',
    sort_order INT NOT NULL DEFAULT 0 COMMENT '表示順序',
    is_active TINYINT(1) NOT NULL DEFAULT 1 COMMENT '有効フラグ',
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) COMMENT '作成日時',
    updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6) COMMENT '更新日時',
    PRIMARY KEY (id),
    INDEX idx_category (category),
    INDEX idx_sort_order (sort_order),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='交換所マスタ';
```

*カラム説明:*
| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-----|------|-----------|------|
| id | VARCHAR(255) | NOT NULL | - | 交換所ID（主キー） |
| name | VARCHAR(255) | NOT NULL | - | 交換所名 |
| category | ENUM | NOT NULL | - | カテゴリー（NORMAL/EVENT/FRAGMENT_BOX） |
| banner_url | VARCHAR(500) | NOT NULL | - | バナー画像URL |
| start_at | DATETIME(6) | NULL | - | 開催開始日時（NULLは無期限） |
| end_at | DATETIME(6) | NULL | - | 開催終了日時（NULLは無期限） |
| sort_order | INT | NOT NULL | 0 | 表示順序（小さい順に表示） |
| is_active | TINYINT(1) | NOT NULL | 1 | 有効フラグ（0: 無効, 1: 有効） |

##### 3.1.5 エラーハンドリング

**エラーコード定義:**

| エラーコード | エラー名 | 発生条件 | ユーザーへのメッセージ | 対処方法 |
|-------------|---------|---------|---------------------|---------|
| E_EXCHANGE_001 | EXCHANGE_NOT_FOUND | 交換所IDが存在しない | 交換所が見つかりません | 交換所IDの存在チェック |
| E_EXCHANGE_002 | EXCHANGE_NOT_AVAILABLE | 交換所が開催期間外 | この交換所は現在開催されていません | 開催期間チェック |

**エラーハンドリングの実装方針:**
- Serviceレイヤーでエラーをthrow（`ExchangeService::getActiveExchanges()`）
- 開催期間チェックはRepository取得時に実施
- エラーレスポンスは統一フォーマット（`ErrorResponseFactory`）
- ログはUseCaseレベルで記録

##### 3.1.6 実装上の注意事項

**パフォーマンス考慮点:**
- N+1問題の回避: 交換所とラインナップを一括取得し、メモリ上で突合
- インデックスの活用: `category`, `sort_order`, `is_active`にインデックス設定
- キャッシュ戦略: マスタデータはS3経由でクライアントに配信されるため、サーバー側ではキャッシュ不要

**セキュリティ考慮点:**
- 入力値検証: 交換所IDのフォーマットチェック（255文字以内）
- 権限チェック: ログイン済みユーザーのみアクセス可能
- 不正防止: 開催期間外の交換所は表示しない

**データ整合性:**
- トランザクション制御: 読み取りのみのため不要
- ロック戦略: 読み取りのみのため不要
- ロールバック処理: 読み取りのみのため不要

**既存実装との整合性:**
- 類似機能との関係: `Domain\Shop`の`MstShopItem`と類似
- 既存パターンの踏襲: `MstShopItemRepository::getActiveShopItems()`のパターンを参考
- 影響範囲: 既存機能への影響なし
- **命名規則の整合性**: `exchange`に統一（`shop`との混同を避けるため）

**マスタデータに関する考慮点:**
- マスタデータ配信機構に従い、S3経由でクライアントに配信
- game/version APIでハッシュ値とパスを返却
- サーバーAPIではマスタデータの参照のみ行う

---

#### 要件 M-3: 複数リソース消費の管理

##### 3.1.1 要件概要
- **要件ID:** REQ-M-3
- **実現内容:** 1つのラインナップに対して複数種類のコスト（コイン、アイテムなど）を設定可能にし、交換時に全コストを順次消費する

##### 3.1.2 API設計

**新規API追加 / 既存API改修:**
- [x] 新規API追加（交換実行API）
- [ ] 既存API改修

**対象エンドポイント:**
- エンドポイント: `POST /api/exchange-shops/lineups/{lineupId}/trade`
- HTTPメソッド: POST
- 認証: 必要

**リクエストパラメータ（JSON形式）:**
```json
{
  "amount": 1
}
```

**リクエストパラメータ説明:**
| パラメータ名 | 型 | 必須 | 説明 | バリデーション |
|-------------|-----|------|------|---------------|
| amount | integer | ○ | 交換回数 | 1以上の整数 |

**レスポンス構造（JSON形式）:**
```json
{
  "result": {
    "exchange_count": 4,
    "exchange_total_count": 16,
    "remaining_count": 6,
    "rewards": [...],
    "consumed_resources": [...]
  }
}
```

##### 3.1.3 ドメイン設計

**ファイル構成:**

*新規作成が必要なファイル:*
- [x] `api/app/Domain/Exchange/Entities/MstExchangeLineupCostEntity.php`
- [x] `api/app/Domain/Exchange/Repositories/MstExchangeLineupCostRepository.php`
- [x] `api/app/Domain/Exchange/Services/ExchangeLineupCostService.php`

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| ExchangeLineupCostService | consumeCosts() | 複数コストを順次消費 | ループ処理で全コスト消費 |
| ExchangeLineupCostService | validateCosts() | 全コストの所持数チェック | 1つでも不足していればエラー |
| MstExchangeLineupCostRepository | getByLineupId() | ラインナップIDでコスト取得 | 複数コストを一括取得 |
| AppCurrencyDelegator | consumeCoin() | コイン消費（既存） | 既存メソッドを利用 |
| UsrItemService | consumeItem() | アイテム消費（既存） | 既存メソッドを利用 |

**Entity設計:**
- Entityタイプ: Eloquent Entity
- 主要プロパティ: `id`, `mst_exchange_lineup_id`, `cost_type`, `cost_id`, `cost_amount`, `sort_order`

##### 3.1.4 DB設計

**新規テーブル作成の場合:**

*テーブル名:* `mst_exchange_lineup_costs`

*CREATE TABLE文:*
```sql
CREATE TABLE mst_exchange_lineup_costs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'コストID',
    mst_exchange_lineup_id VARCHAR(255) NOT NULL COMMENT 'ラインナップID',
    cost_type ENUM('COIN','ITEM') NOT NULL COMMENT 'コスト種別',
    cost_id VARCHAR(255) NOT NULL COMMENT 'コストID',
    cost_amount INT UNSIGNED NOT NULL COMMENT 'コスト個数',
    sort_order INT NOT NULL DEFAULT 0 COMMENT '表示順序',
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) COMMENT '作成日時',
    updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6) COMMENT '更新日時',
    PRIMARY KEY (id),
    INDEX idx_lineup_id (mst_exchange_lineup_id),
    UNIQUE KEY uk_lineup_cost (mst_exchange_lineup_id, cost_type, cost_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ラインナップコストマスタ';
```

*カラム説明:*
| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-----|------|-----------|------|
| id | BIGINT UNSIGNED | NOT NULL | AUTO_INCREMENT | コストID（主キー） |
| mst_exchange_lineup_id | VARCHAR(255) | NOT NULL | - | ラインナップID |
| cost_type | ENUM | NOT NULL | - | コスト種別（COIN/ITEM） |
| cost_id | VARCHAR(255) | NOT NULL | - | コストID（COINの場合は"coin"固定） |
| cost_amount | INT UNSIGNED | NOT NULL | - | コスト個数 |
| sort_order | INT | NOT NULL | 0 | 表示順序 |

##### 3.1.5 エラーハンドリング

**エラーコード定義:**

| エラーコード | エラー名 | 発生条件 | ユーザーへのメッセージ | 対処方法 |
|-------------|---------|---------|---------------------|---------|
| E_EXCHANGE_010 | INSUFFICIENT_COIN | コインが不足 | コインが不足しています | コイン所持数チェック |
| E_EXCHANGE_011 | INSUFFICIENT_ITEM | アイテムが不足 | {アイテム名}が不足しています | アイテム所持数チェック |

**エラーハンドリングの実装方針:**
- Serviceレイヤーで所持数チェックを実施（`ExchangeLineupCostService::validateCosts()`）
- 1つでも不足している場合、最初に不足したリソースのエラーをthrow
- エラーメッセージにアイテム名を含める

##### 3.1.6 実装上の注意事項

**パフォーマンス考慮点:**
- N+1問題の回避: ラインナップIDでコストを一括取得（`getByLineupId()`）
- ループ処理: 通常1ラインナップあたり1〜3個程度のコストのため、パフォーマンス影響は限定的

**データ整合性:**
- トランザクション制御: 全コスト消費をトランザクション内で実行
- ロールバック処理: 1つでも消費に失敗した場合、全体をロールバック

**既存実装との整合性:**
- 既存パターンの踏襲: `Domain\Shop`の`ShopService::consumeCost()`を参考に拡張
- 既存メソッドの利用: `AppCurrencyDelegator::consumeCoin()`, `UsrItemService::consumeItem()`を利用

---

### 3.2 ユーザーデータ管理（交換履歴）

#### 要件 U-2: 交換回数のリセット周期による自動リセット処理

##### 3.2.1 要件概要
- **要件ID:** REQ-U-2
- **実現内容:** リセット周期（DAILY/WEEKLY/MONTHLY）に基づいて、交換実行時に自動的に交換回数をリセットする

##### 3.2.2 API設計

**対象エンドポイント:**
- エンドポイント: `POST /api/exchanges/lineups/{lineupId}/trade`（交換実行APIの一部として実装）
- HTTPメソッド: POST
- 認証: 必要

##### 3.2.3 ドメイン設計

**ファイル構成:**

*新規作成が必要なファイル:*
- [x] `api/app/Domain/Exchange/Services/ExchangeLineupResetService.php`
- [x] `api/app/Domain/Exchange/Models/UsrExchangeLineup.php`
- [x] `api/app/Domain/Exchange/Repositories/UsrExchangeLineupRepository.php`

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| ExchangeLineupResetService | resetIfNeeded() | リセット判定とリセット実行 | Clock serviceを使用 |
| ExchangeLineupResetService | isResetNeeded() | リセットが必要か判定 | DAILY/WEEKLY/MONTHLYに対応 |
| UsrExchangeLineup | reset() | 交換回数をリセット | exchange_countを0に、last_reset_atを更新 |
| UsrExchangeLineup | makeModelKey() | UsrModelManagerのキー生成 | usr_user_id + mst_exchange_lineup_id |
| Clock | isFirstToday() | 今日が最初のアクセスか判定（既存） | DAILY用 |
| Clock | isFirstWeek() | 今週が最初のアクセスか判定（既存） | WEEKLY用 |
| Clock | isFirstMonth() | 今月が最初のアクセスか判定（新規） | MONTHLY用（新規実装が必要） |

**Entity設計:**
- Entityタイプ: Eloquent Entity（UsrModelManager対応）
- 主要プロパティ: `usr_user_id`, `mst_exchange_lineup_id`, `exchange_count`, `exchange_total_count`, `last_reset_at`
- `makeModelKey()`メソッドの実装が必須

##### 3.2.4 DB設計

**新規テーブル作成の場合:**

*テーブル名:* `usr_exchange_lineups`

*CREATE TABLE文:*
```sql
CREATE TABLE usr_exchange_lineups (
    usr_user_id VARCHAR(255) NOT NULL COMMENT 'ユーザーID',
    mst_exchange_lineup_id VARCHAR(255) NOT NULL COMMENT 'ラインナップID',
    exchange_count INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '現在期間内の交換回数',
    exchange_total_count INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '累計交換回数',
    last_reset_at DATETIME(6) NULL COMMENT '最終リセット日時',
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) COMMENT '作成日時',
    updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6) COMMENT '更新日時',
    PRIMARY KEY (usr_user_id, mst_exchange_lineup_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザー交換履歴';
```

*テーブル設計の注意事項:*
- **usrテーブル:** PRIMARY KEYには必ずusr_user_idを含める。複合キーの場合、usr_user_idを最初のカラムとする
- TiDB最適化の観点から、複合PK（usr_user_id, mst_exchange_lineup_id）を採用

*カラム説明:*
| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-----|------|-----------|------|
| usr_user_id | VARCHAR(255) | NOT NULL | - | ユーザーID（複合PKの1つ目） |
| mst_exchange_lineup_id | VARCHAR(255) | NOT NULL | - | ラインナップID（複合PKの2つ目） |
| exchange_count | INT UNSIGNED | NOT NULL | 0 | 現在期間内の交換回数（リセット対象） |
| exchange_total_count | INT UNSIGNED | NOT NULL | 0 | 累計交換回数（リセット対象外） |
| last_reset_at | DATETIME(6) | NULL | - | 最終リセット日時 |

##### 3.2.5 エラーハンドリング

**エラーコード定義:**

リセット処理自体はエラーをthrowしない（内部処理のため）

##### 3.2.6 実装上の注意事項

**パフォーマンス考慮点:**
- UsrModelManagerによるキャッシュ: `makeModelKey()`を実装し、リクエスト内で重複取得を防ぐ
- リセット判定は交換実行前に1回だけ実施

**データ整合性:**
- トランザクション制御: リセット処理も交換処理と同じトランザクション内で実行
- `exchange_count`は0にリセット、`exchange_total_count`はリセットしない

**既存実装との整合性:**
- 既存パターンの踏襲: `Domain\Shop`の`ShopService::resetUsrShopItem()`を参考
- Clock serviceの拡張: `Clock::isFirstMonth()`メソッドを新規実装

**マスタデータに関する考慮点:**
- `reset_type`はマスタデータ（mst_exchange_lineups）から取得

---

### 3.3 交換処理・トランザクション

#### 要件 T-1: 交換実行のトランザクション管理

##### 3.3.1 要件概要
- **要件ID:** REQ-T-1
- **実現内容:** 交換処理全体（コスト消費、報酬付与、カウント更新、ログ記録）を1つのトランザクションで実行し、原子性を保証する

##### 3.3.2 API設計

**対象エンドポイント:**
- エンドポイント: `POST /api/exchange-shops/lineups/{lineupId}/trade`
- HTTPメソッド: POST
- 認証: 必要

##### 3.3.3 ドメイン設計

**ファイル構成:**

*新規作成が必要なファイル:*
- [x] `api/app/Domain/Exchange/UseCases/ExchangeLineupTradeUseCase.php`
- [x] `api/app/Domain/Exchange/Rewards/ExchangeLineupReward.php`
- [x] `api/app/Domain/Exchange/Repositories/LogTradeExchangeLineupRepository.php`

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| ExchangeLineupTradeUseCase | execute() | 交換処理の全体フロー | applyUserTransactionChangesを使用 |
| ExchangeLineupResetService | resetIfNeeded() | リセット判定・実行 | トランザクション内で実行 |
| ExchangeLineupCostService | consumeCosts() | 複数コスト消費 | トランザクション内で実行 |
| RewardDelegator | addReward() | 報酬を追加（既存） | ExchangeLineupRewardを追加 |
| RewardDelegator | sendRewards() | 報酬を配布（既存） | トランザクション内で実行 |
| UsrExchangeLineupRepository | incrementExchangeCount() | 交換回数をインクリメント | トランザクション内で実行 |
| LogTradeExchangeLineupRepository | create() | 交換ログを記録 | トランザクション内で実行 |

**処理フロー:**
1. ラインナップマスタ取得（有効期間チェック）
2. ユーザー交換履歴取得 or 新規作成
3. リセット判定・実行
4. 交換上限チェック
5. 全コストの所持数チェック
6. **トランザクション開始**（`applyUserTransactionChanges`）
7. 複数コスト消費
8. 報酬配布（RewardDelegator経由）
9. 交換回数インクリメント
10. 交換ログ記録
11. **トランザクションコミット**

##### 3.3.4 DB設計

**新規テーブル作成の場合:**

*テーブル名:* `log_trade_exchange_lineups`

*CREATE TABLE文:*
```sql
CREATE TABLE log_trade_exchange_lineups (
    log_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ログID',
    usr_user_id VARCHAR(255) NOT NULL COMMENT 'ユーザーID',
    mst_exchange_lineup_id VARCHAR(255) NOT NULL COMMENT 'ラインナップID',
    exchange_count_before INT UNSIGNED NOT NULL COMMENT '交換前の交換回数',
    exchange_count_after INT UNSIGNED NOT NULL COMMENT '交換後の交換回数',
    trade_amount INT UNSIGNED NOT NULL COMMENT '交換回数',
    costs JSON NOT NULL COMMENT '消費したコスト情報',
    rewards JSON NOT NULL COMMENT '付与された報酬情報',
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) COMMENT '作成日時',
    PRIMARY KEY (log_id),
    INDEX idx_usr_user_id_created_at (usr_user_id, created_at),
    INDEX idx_lineup_id (mst_exchange_lineup_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='交換ログ';
```

*テーブル設計の注意事項:*
- **logテーブル:** PRIMARY KEYはAUTO_INCREMENTのlog_idとする。usr_user_idとcreated_atの複合インデックスを設定する

*カラム説明:*
| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-----|------|-----------|------|
| log_id | BIGINT UNSIGNED | NOT NULL | AUTO_INCREMENT | ログID（主キー） |
| usr_user_id | VARCHAR(255) | NOT NULL | - | ユーザーID |
| mst_exchange_lineup_id | VARCHAR(255) | NOT NULL | - | ラインナップID |
| exchange_count_before | INT UNSIGNED | NOT NULL | - | 交換前の交換回数 |
| exchange_count_after | INT UNSIGNED | NOT NULL | - | 交換後の交換回数 |
| trade_amount | INT UNSIGNED | NOT NULL | - | 交換回数（何回分交換したか） |
| costs | JSON | NOT NULL | - | 消費したコスト情報（JSON配列） |
| rewards | JSON | NOT NULL | - | 付与された報酬情報（JSON配列） |

**costs例:**
```json
[
  {"cost_type": "ITEM", "cost_id": "item_exchange_ticket_a", "cost_amount": 1},
  {"cost_type": "COIN", "cost_id": "coin", "cost_amount": 1000}
]
```

**rewards例:**
```json
[
  {"reward_type": "ITEM", "reward_id": "item_gold_coin_1000", "reward_amount": 1000}
]
```

##### 3.3.5 エラーハンドリング

**エラーコード定義:**

| エラーコード | エラー名 | 発生条件 | ユーザーへのメッセージ | 対処方法 |
|-------------|---------|---------|---------------------|---------|
| E_EXCHANGE_020 | TRANSACTION_FAILED | トランザクション失敗 | 交換処理に失敗しました | トランザクションロールバック、エラーログ記録 |

**エラーハンドリングの実装方針:**
- UseCaseレベルでトランザクション制御（`applyUserTransactionChanges`）
- トランザクション内でエラーが発生した場合、自動的にロールバック
- エラーログは`Log::error()`で記録

##### 3.3.6 実装上の注意事項

**データ整合性:**
- トランザクション制御: `UseCaseTrait::applyUserTransactionChanges`を使用
- ロールバック処理: 自動ロールバック（例外発生時）
- 部分的な成功は許容しない（全て成功 or 全て失敗）

**既存実装との整合性:**
- 既存パターンの踏襲: `Domain\Shop`の`ShopTradeShopItemUseCase`を参考
- `applyUserTransactionChanges`の使用方法を踏襲

---

## 4. 実装の優先順位と依存関係

### 4.1 実装の段階分け

**フェーズ1: 基盤実装（マスタデータとDB設計）**
1. REQ-M-1: 交換所マスタテーブル作成とEntity実装
2. REQ-M-2: ラインナップマスタテーブル作成とEntity実装
3. REQ-M-3: ラインナップコストマスタテーブル作成とEntity実装
4. REQ-U-1: ユーザー交換履歴テーブル作成とModel実装
5. REQ-L-1: 交換ログテーブル作成とRepository実装

**フェーズ2: データ取得系API実装**
1. REQ-M-1, REQ-M-2: GET /api/exchange-shops 実装
2. REQ-M-5: GET /api/exchange-shops/{id} 実装
3. REQ-U-2: リセット処理の実装（Clock::isFirstMonth追加）
4. REQ-P-1: N+1問題回避の最適化

**フェーズ3: 交換実行API実装**
1. REQ-T-2: 複数リソース消費の実装
2. REQ-T-3: RewardDelegator連携の実装（ExchangeLineupReward作成）
3. REQ-U-3: 交換上限チェックの実装
4. REQ-V-1, REQ-V-2: バリデーション実装
5. REQ-T-1: トランザクション管理の実装
6. POST /api/exchange-shops/lineups/{lineupId}/trade 実装

### 4.2 依存関係マップ

```
REQ-M-1 (交換所マスタ)
  ↓
REQ-M-2 (ラインナップマスタ) ← REQ-M-3 (コストマスタ)
  ↓
REQ-U-1 (ユーザー交換履歴)
  ↓
REQ-U-2 (リセット処理) → Clock::isFirstMonth実装
  ↓
REQ-T-1 (トランザクション管理)
  ├→ REQ-T-2 (複数リソース消費)
  ├→ REQ-T-3 (報酬配布)
  ├→ REQ-U-3 (上限チェック)
  └→ REQ-L-1 (ログ記録)
```

### 4.3 実装時の注意点

- フェーズ1を完了してからフェーズ2に進む（DBスキーマが確定していることが前提）
- フェーズ2の実装後、マスタデータを投入してデータ取得系APIの動作確認を行う
- フェーズ3はトランザクション境界が複雑なため、ユニットテストを充実させる
- 各フェーズ完了後、機能テストとシナリオテストを実施
- Clock::isFirstMonth()の実装は早めに着手（既存のisFirstToday(), isFirstWeek()と同じパターン）

## 5. テスト設計概要

### 5.1 ユニットテスト

**テスト対象:**
- ExchangeShopService, ExchangeLineupService, ExchangeLineupResetService, ExchangeLineupCostService
- UsrExchangeLineupRepository, MstExchangeShopRepository, MstExchangeLineupRepository

**テストケース例:**
| テスト対象 | テストケース | 期待結果 |
|-----------|------------|---------|
| ExchangeLineupResetService::isResetNeeded() | DAILY、前日にリセット済み | リセット必要（true） |
| ExchangeLineupResetService::isResetNeeded() | DAILY、本日既にリセット済み | リセット不要（false） |
| ExchangeLineupResetService::isResetNeeded() | MONTHLY、前月にリセット済み | リセット必要（true） |
| ExchangeLineupCostService::validateCosts() | 全コストの所持数が十分 | エラーなし |
| ExchangeLineupCostService::validateCosts() | 1つでもコストが不足 | INSUFFICIENT_XXXエラー |

### 5.2 機能テスト

**テスト対象:**
- GET /api/exchange-shops
- GET /api/exchange-shops/{id}
- POST /api/exchange-shops/lineups/{lineupId}/trade

**テストケース例:**
| API | テストケース | 期待結果 |
|-----|------------|---------|
| GET /api/exchange-shops | 有効な交換所が存在 | 200 OK、交換所一覧が返る |
| GET /api/exchange-shops | 全ての交換所が開催期間外 | 200 OK、空の配列が返る |
| GET /api/exchange-shops/{id} | 存在する交換所ID | 200 OK、交換所詳細が返る |
| GET /api/exchange-shops/{id} | 存在しない交換所ID | 404 Not Found、EXCHANGE_SHOP_NOT_FOUNDエラー |
| POST /api/exchange-shops/lineups/{lineupId}/trade | 正常なリクエスト、十分なリソース | 200 OK、交換成功レスポンス |
| POST /api/exchange-shops/lineups/{lineupId}/trade | リソース不足 | 400 Bad Request、INSUFFICIENT_XXXエラー |
| POST /api/exchange-shops/lineups/{lineupId}/trade | 交換上限到達 | 400 Bad Request、EXCHANGE_LIMIT_REACHEDエラー |

### 5.3 シナリオテスト

**テストシナリオ例:**
1. ユーザーが交換所一覧を取得する（GET /api/exchange-shops）
2. 特定の交換所を選択し、詳細を取得する（GET /api/exchange-shops/{id}）
3. ラインナップを選択し、交換を実行する（POST /api/exchange-shops/lineups/{lineupId}/trade）
4. コストが消費され、報酬が付与される
5. 交換回数がインクリメントされる
6. 交換ログが記録される
7. 再度交換所一覧を取得すると、残り回数が減っている

**月次リセットのシナリオ:**
1. 月初日03:59に交換実行（上限到達）
2. 月初日04:01に再度交換実行
3. リセット処理が自動実行され、交換回数が0になる
4. 交換が成功する

## 6. マイグレーション計画

### 6.1 マイグレーションファイル一覧

**新規作成が必要なマイグレーション:**
- [ ] `YYYY_MM_DD_000001_create_mst_exchanges_table.php` - mst_exchangesテーブル作成
- [ ] `YYYY_MM_DD_000002_create_mst_exchange_lineups_table.php` - mst_exchange_lineupsテーブル作成
- [ ] `YYYY_MM_DD_000003_create_mst_exchange_lineup_costs_table.php` - mst_exchange_lineup_costsテーブル作成
- [ ] `YYYY_MM_DD_000004_create_usr_exchange_lineups_table.php` - usr_exchange_lineupsテーブル作成
- [ ] `YYYY_MM_DD_000005_create_log_trade_exchange_lineups_table.php` - log_trade_exchange_lineupsテーブル作成

### 6.2 マイグレーション実行順序

1. マスタテーブルの作成（mst_exchanges, mst_exchange_lineups, mst_exchange_lineup_costs）
2. ユーザーテーブルの作成（usr_exchange_lineups）
3. ログテーブルの作成（log_trade_exchange_lineups）

**外部キー制約:**
- mst_exchange_lineups.mst_exchange_id → mst_exchanges.id
- mst_exchange_lineup_costs.mst_exchange_lineup_id → mst_exchange_lineups.id
- usr_exchange_lineups.mst_exchange_lineup_id → mst_exchange_lineups.id

### 6.3 ロールバック方針

- 各マイグレーションにはdown()メソッドを実装する（DROP TABLE）
- ロールバック時はログテーブル、ユーザーテーブル、マスタテーブルの順で削除
- 外部キー制約がある場合、依存先から削除

## 7. 連携が必要なスキル・ツール

**Claude Code スキル:**
- `api-request-validation`: リクエストパラメータのバリデーション実装
- `domain-layer`: ドメインレイヤーの実装パターン参照
- `api-schema-reference`: glow-schema YAMLとの整合性確認
- `api-response`: レスポンス構造の実装
- `migration`: マイグレーションファイルの作成・実行
- `api-test-implementation`: テストコードの実装

**使用タイミング:**
- API実装時: `api-request-validation`, `api-response`, `api-schema-reference`
- ドメイン実装時: `domain-layer`
- DB設計時: `migration`
- テスト実装時: `api-test-implementation`

## 8. 実装時の判断が必要な事項

### 8.1 技術的な選択肢

**項目:** 複数リソース消費の実装方法
- 選択肢A: ループ処理で順次消費
  - メリット: シンプルで理解しやすい、既存のconsume系メソッドを再利用できる
  - デメリット: ループ回数が多い場合、パフォーマンスに影響
- 選択肢B: 一括消費処理
  - メリット: パフォーマンス最適化
  - デメリット: 新規実装が必要、複雑度が上がる
- 推奨: **選択肢A（ループ処理）**
  - 理由: 通常1ラインナップあたり1〜3個程度のコストのため、パフォーマンス影響は限定的。シンプルさを優先。

**項目:** Clock::isFirstMonth()の実装
- 選択肢A: 既存のisFirstToday(), isFirstWeek()と同じパターン
  - メリット: 既存コードとの一貫性、理解しやすい
  - デメリット: なし
- 選択肢B: 独自の実装
  - メリット: 柔軟性
  - デメリット: 既存パターンとの不整合
- 推奨: **選択肢A（既存パターンを踏襲）**
  - 理由: アーキテクチャの一貫性を保つ。

### 8.2 仕様の解釈

**項目:** 月次リセットの「毎月初日の04:00」の解釈
- 解釈A: バッチ処理で04:00ちょうどにリセット
  - この解釈の場合の実装: cron jobでバッチ処理を実装
- 解釈B: 04:00以降の初回アクセス時にリセット（リクエスト時判定）
  - この解釈の場合の実装: 交換実行時にリセット判定を実施
- 確認先: プランナー（既に確認済み）
- **確定事項:** 解釈B（リクエスト時判定）を採用。既存のショップアイテム実装を参考にする。

## 9. 補足情報

### 9.1 参考にすべき既存実装

**類似機能の実装例:**
- `Domain\Shop`: ショップアイテム機能（交換所と類似）
  - `ShopService`: リセット処理、上限チェック、コスト消費の参考
  - `UsrShopItem`: ユーザー交換履歴の参考
  - `ShopTradeShopItemUseCase`: トランザクション管理の参考

**参考になるドメイン設計:**
- `Domain\Item`: アイテム管理（ItemExchangeSelectItemUseCase）
- `Domain\Reward`: 報酬配布（RewardDelegator）
- `Domain\Currency`: コイン管理（AppCurrencyDelegator）

**参考になるDB設計:**
- `usr_shop_items`: ユーザーショップアイテムテーブル
- `log_trade_shop_items`: ショップアイテム交換ログテーブル

### 9.2 参考ドキュメント

- **APIコーディング規約**: @docs/01_project/coding-standards/api-coding-standards.md
  - 命名規則、アーキテクチャパターン、実装パターンの詳細
  - Controller層、Domain層、Response層のパターン
- **マスタデータ配信機構**: @docs/01_project/architecture/マスタデータ配信機構.md
  - マスタデータの配信フロー、S3連携、バージョン管理の仕組み

### 9.3 実装時のTips

**UsrModelManagerの活用:**
- `makeModelKey()`メソッドを必ず実装する
- キャッシュキーは`usr_user_id + mst_exchange_lineup_id`の連結文字列
- リクエスト内で同じユーザー交換履歴を複数回取得する場合、2回目以降はキャッシュから取得

**N+1問題の回避:**
- `keyBy()`, `groupBy()`を活用してメモリ上で突合
- 交換所、ラインナップ、コスト、ユーザー交換履歴を一括取得
- Eager Loadingを使用（`with()`メソッド）

**トランザクション管理:**
- `applyUserTransactionChanges`を使用
- トランザクション内で例外が発生した場合、自動的にロールバック
- `Log::error()`でエラーログを記録

**日時データの変換:**
- レスポンスの日時データは必ず`StringUtil::convertToISO8601()`でISO8601形式に変換
- DBへの保存時は`CarbonImmutable`を使用

**よくあるハマりポイント:**
- UsrModelManagerのキャッシュキーを正しく実装しないと、同じデータを複数回取得してしまう
- トランザクション外でDB更新すると、エラー時にロールバックされない
- 日時データをISO8601形式に変換し忘れると、クライアント側でパースエラーが発生

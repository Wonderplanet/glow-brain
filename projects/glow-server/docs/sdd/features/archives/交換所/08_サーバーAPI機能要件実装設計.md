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

交換所機能は、既存のShop機能のアーキテクチャパターンを踏襲しつつ、以下の点で独立した実装とします：

1. **既存Shop機能との整合性**
   - UsrShopItemと同様の二重カウンタ管理（trade_count / trade_total_count）
   - Clock機能を使用した月次リセット判定
   - RewardDelegator経由での報酬付与
   - トランザクション制御のパターン踏襲

2. **独立したドメインとして実装**
   - ドメイン名: `Domain\Exchange` (交換所専用ドメイン)
   - Shop機能とは別の独立したテーブル構造
   - 将来的な機能拡張に対応できる柔軟な設計

3. **クリーンアーキテクチャの遵守**
   - Controller → Delegator → UseCase → Service → Repository の階層構造
   - Entity/Modelによるデータ構造の明確化
   - 各層の責務を明確に分離

4. **複数リソース消費への対応**
   - 1ラインナップに対して複数コストを設定可能な拡張可能設計
   - 交換コストテーブルを正規化（1:N構造）

### 2.2 実装の全体像

**新規追加されるコンポーネント:**

1. **ドメイン層**
   - `Domain\Exchange` ドメインの新規作成
   - Entities: ExchangeEntity, ExchangeLineupEntity, ExchangeLineupCostEntity
   - Models: UsrExchangeLineup, LogTradeExchangeLineup
   - Repositories: UsrExchangeLineupRepository, LogTradeExchangeLineupRepository
   - Services: ExchangeService, ExchangeLineupService
   - UseCases: ExchangeTradeUseCase, GetExchangeListUseCase, GetExchangeLineupListUseCase
   - Delegators: ExchangeDelegator

2. **データベース層**
   - mstDB: mst_exchanges, mst_exchange_lineups, mst_exchange_lineup_costs
   - usrDB: usr_exchange_lineups
   - logDB: log_trade_exchange_lineups

3. **API層**
   - Controller: ExchangeController
   - ResponseFactory: ExchangeResponseFactory
   - ResultData: ExchangeTradeResultData, GetExchangeListResultData, GetExchangeLineupListResultData

**既存コンポーネントへの影響範囲:**

- Reward機能: 既存のRewardDelegatorを活用（変更不要）
- User機能: 既存のUserDelegatorを活用（変更不要）
- Item機能: 既存のUsrItemServiceを活用（変更不要）
- Clock機能: 既存のClock::isFirstMonth()を活用（変更不要）

**アーキテクチャ上の考慮点:**

- マスタデータ（mst_exchanges等）はS3経由でクライアントに配信され、サーバーAPIは参照のみ
- ユーザーデータ（usr_exchange_lineups）はUsrModelManagerによるキャッシュ管理
- ログデータ（log_trade_exchange_lineups）は永続保存

## 3. 機能要件別実装設計

### 3.1 交換所マスタデータ管理

#### 要件 MASTER-1: 交換所の種別管理

##### 3.1.1 要件概要
- **要件ID:** REQ-MASTER-1
- **実現内容:** 交換所を3種類のカテゴリー（通常、イベント、キャラのかけらBOX）に分類し、それぞれ異なる開催期間とリセット方式を管理

##### 3.1.2 API設計

**新規API追加 / 既存API改修:**
- [x] 新規API追加（この要件自体はマスタデータのため、APIは不要。マスタデータ配信機構で対応）

**実装方針:**
- 交換所の種別はマスタデータ（mst_exchanges）で管理
- S3経由でクライアントに配信される
- サーバーAPIでは参照のみ（開催期間フィルタリング等で使用）

##### 3.1.3 ドメイン設計

**新規ドメイン追加 / 既存ドメイン改修:**
- [x] 新規ドメイン追加

**対象ドメイン:**
- ドメイン分類: 通常ドメイン
- ドメイン名: `Domain\Exchange`

**ファイル構成:**

*新規作成が必要なファイル:*
- [x] `api/app/Domain/Exchange/Enums/ExchangeType.php` - 交換所種別のEnum定義
- [x] `api/app/Domain/Exchange/Enums/ExchangeResetType.php` - リセット種別のEnum定義
- [x] `api/app/Domain/Exchange/Repositories/MstExchangeRepository.php` - マスタ交換所リポジトリ
- [x] `api/app/Domain/Exchange/Repositories/MstExchangeLineupRepository.php` - マスタラインナップリポジトリ

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| ExchangeType | - | 交換所種別の定義 | Normal, Event, CharacterFragmentBox |
| ExchangeResetType | - | リセット種別の定義 | None, Monthly |
| MstExchangeRepository | findById() | 交換所マスタの取得 | - |
| MstExchangeRepository | getActiveExchanges() | 開催期間内の交換所取得 | 期間フィルタリング |

**Enum設計:**

```php
// ExchangeType.php
enum ExchangeType: string
{
    case Normal = 'Normal';                    // 通常交換所
    case Event = 'Event';                      // イベント交換所
    case CharacterFragmentBox = 'CharacterFragmentBox'; // キャラのかけらBOX
}

// ExchangeResetType.php
enum ExchangeResetType: string
{
    case None = 'None';       // リセットなし
    case Monthly = 'Monthly'; // 月次リセット
}
```

##### 3.1.4 DB設計

**テーブル構造追加 / テーブル構造変更:**
- [x] テーブル構造追加（新規テーブル作成）

**新規テーブル作成の場合:**

*テーブル名:* `mst_exchanges`

*CREATE TABLE文:*
```sql
CREATE TABLE mst_exchanges (
    mst_exchange_id VARCHAR(255) NOT NULL COMMENT '交換所ID',
    exchange_name VARCHAR(255) NOT NULL COMMENT '交換所名',
    exchange_type ENUM('Normal', 'Event', 'CharacterFragmentBox') NOT NULL COMMENT '交換所種別',
    reset_type ENUM('None', 'Monthly') NOT NULL COMMENT 'リセット種別',
    start_date DATETIME(6) NULL COMMENT '開催開始日時（NULLは無期限）',
    end_date DATETIME(6) NULL COMMENT '開催終了日時（NULLは無期限）',
    display_order INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '表示順序',
    is_active TINYINT(1) NOT NULL DEFAULT 1 COMMENT '有効フラグ',
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) COMMENT '作成日時',
    updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6) COMMENT '更新日時',
    PRIMARY KEY (mst_exchange_id),
    INDEX idx_exchange_type (exchange_type),
    INDEX idx_is_active (is_active),
    INDEX idx_start_date (start_date),
    INDEX idx_end_date (end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='交換所マスタ';
```

*カラム説明:*
| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-----|------|-----------|------|
| mst_exchange_id | VARCHAR(255) | NOT NULL | - | 交換所ID（主キー） |
| exchange_name | VARCHAR(255) | NOT NULL | - | 交換所名（表示用） |
| exchange_type | ENUM | NOT NULL | - | 交換所種別（Normal/Event/CharacterFragmentBox） |
| reset_type | ENUM | NOT NULL | - | リセット種別（None/Monthly） |
| start_date | DATETIME(6) | NULL | - | 開催開始日時（NULLは無期限） |
| end_date | DATETIME(6) | NULL | - | 開催終了日時（NULLは無期限） |
| display_order | INT UNSIGNED | NOT NULL | 0 | 表示順序 |
| is_active | TINYINT(1) | NOT NULL | 1 | 有効フラグ |

##### 3.1.5 エラーハンドリング

**エラーコード定義:**

| エラーコード | エラー名 | 発生条件 | ユーザーへのメッセージ | 対処方法 |
|-------------|---------|---------|---------------------|---------|
| E_EXCHANGE_001 | EXCHANGE_NOT_FOUND | 交換所が存在しない、または開催期間外 | 交換所が見つかりません | 交換所の存在チェックと期間チェック |

**エラーハンドリングの実装方針:**
- MstExchangeRepository::getActiveExchangeById()でマスタが見つからない場合、MST_NOT_FOUNDをthrow
- 期間外の交換所は取得時に除外（エラーではなく空を返す）

##### 3.1.6 実装上の注意事項

**パフォーマンス考慮点:**
- 開催期間チェックはINDEXを活用（idx_start_date, idx_end_date）
- マスタデータはS3から配信されるため、サーバー側の負荷は最小限

**データ整合性:**
- 交換所マスタは読み取り専用（管理ツールからのみ更新）
- マスタデータの更新はS3配信と連動

**既存実装との整合性:**
- ShopTypeとShopResetTypeのパターンを踏襲
- マスタデータ配信機構に従う

**マスタデータに関する考慮点:**
- マスタデータ配信機構を参照し、S3経由でクライアントに配信されることを理解
- game/version APIでハッシュ値とパスを返却する仕組みを活用
- サーバーAPIではマスタデータの参照のみ行い、配信はしない

---

#### 要件 MASTER-2: 交換所の開催期間管理

##### 3.1.1 要件概要
- **要件ID:** REQ-MASTER-2
- **実現内容:** 交換所の開催期間（start_date, end_date）をチェックし、開催期間外の交換所は非表示とする

##### 3.1.2 API設計

**新規API追加 / 既存API改修:**
- [x] 新規API追加

**対象エンドポイント:**
- エンドポイント: `/exchange/list`
- HTTPメソッド: POST
- 認証: 必要

**リクエストパラメータ（JSON形式）:**
```json
{
}
```

**リクエストパラメータ説明:**
| パラメータ名 | 型 | 必須 | 説明 | バリデーション |
|-------------|-----|------|------|---------------|
| （なし） | - | - | - | - |

**レスポンス構造（JSON形式）:**
```json
{
  "result": true,
  "data": {
    "exchanges": [
      {
        "mst_exchange_id": "exchange_001",
        "exchange_name": "通常交換所",
        "exchange_type": "Normal",
        "remaining_time": {
          "days": 10,
          "hours": 5
        },
        "is_unlimited": false
      }
    ]
  }
}
```

**レスポンスフィールド説明:**
| フィールド名 | 型 | 説明 |
|-------------|-----|------|
| result | boolean | 処理結果 |
| data.exchanges | array | 開催期間内の交換所リスト |
| data.exchanges[].mst_exchange_id | string | 交換所ID |
| data.exchanges[].exchange_name | string | 交換所名 |
| data.exchanges[].exchange_type | string | 交換所種別 |
| data.exchanges[].remaining_time.days | integer | 残り日数 |
| data.exchanges[].remaining_time.hours | integer | 残り時間 |
| data.exchanges[].is_unlimited | boolean | 無期限フラグ |

##### 3.1.3 ドメイン設計

**新規ドメイン追加 / 既存ドメイン改修:**
- [x] 新規ドメイン追加

**ファイル構成:**

*新規作成が必要なファイル:*
- [x] `api/app/Domain/Exchange/UseCases/GetExchangeListUseCase.php`
- [x] `api/app/Domain/Exchange/Services/ExchangeService.php`
- [x] `api/app/Domain/Exchange/Delegators/ExchangeDelegator.php`

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| ExchangeDelegator | getExchangeList() | 交換所一覧取得のファサード | Controllerから呼ばれる |
| GetExchangeListUseCase | exec() | 交換所一覧取得のユースケース | - |
| ExchangeService | calculateRemainingTime() | 残り時間計算 | - |
| MstExchangeRepository | getActiveExchanges() | 開催期間内の交換所取得 | 期間フィルタリング |

##### 3.1.4 DB設計

（REQ-MASTER-1で定義済み）

##### 3.1.5 エラーハンドリング

（REQ-MASTER-1で定義済み）

##### 3.1.6 実装上の注意事項

**パフォーマンス考慮点:**
- 期間フィルタリングクエリでINDEXを活用
- 残り時間計算は効率的なアルゴリズムで実装（Carbonの活用）

**セキュリティ考慮点:**
- 開催期間外の交換所は完全に除外（クライアントに返却しない）

**データ整合性:**
- 現在時刻はClock機能から取得（一貫性確保）

**既存実装との整合性:**
- MstShopItemRepository::getActiveShopItemById()の期間チェックパターンを踏襲

---

#### 要件 MASTER-3: 交換所の残り時間計算

##### 3.1.1 要件概要
- **要件ID:** REQ-MASTER-3
- **実現内容:** 交換所の残り時間を計算し、「dd日tt時間」または「期限なし」の形式で返却する

##### 3.1.2 API設計

（REQ-MASTER-2で定義済み）

##### 3.1.3 ドメイン設計

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| ExchangeService | calculateRemainingTime() | 残り時間計算 | Carbonを使用 |

**残り時間計算ロジック:**
```php
public function calculateRemainingTime(
    ?CarbonImmutable $endDate,
    CarbonImmutable $now
): array {
    if ($endDate === null) {
        return [
            'is_unlimited' => true,
            'days' => null,
            'hours' => null,
        ];
    }

    $diff = $now->diff($endDate);
    return [
        'is_unlimited' => false,
        'days' => $diff->days,
        'hours' => $diff->h,
    ];
}
```

##### 3.1.4 DB設計

（REQ-MASTER-1で定義済み）

##### 3.1.5 エラーハンドリング

（特になし）

##### 3.1.6 実装上の注意事項

**パフォーマンス考慮点:**
- 残り時間計算はCarbonの効率的なメソッドを使用
- キャッシュは不要（計算コストが低い）

---

### 3.2 交換ラインナップ管理

#### 要件 LINEUP-1: ラインナップの詳細設定管理

##### 3.2.1 要件概要
- **要件ID:** REQ-LINEUP-1
- **実現内容:** 各ラインナップには、報酬アイテム、必要リソース、交換期間、交換上限数を個別に設定可能

##### 3.2.2 API設計

**新規API追加 / 既存API改修:**
- [x] 新規API追加

**対象エンドポイント:**
- エンドポイント: `/exchange/lineup/list`
- HTTPメソッド: POST
- 認証: 必要

**リクエストパラメータ（JSON形式）:**
```json
{
  "mst_exchange_id": "exchange_001"
}
```

**リクエストパラメータ説明:**
| パラメータ名 | 型 | 必須 | 説明 | バリデーション |
|-------------|-----|------|------|---------------|
| mst_exchange_id | string | ○ | 交換所ID | 存在チェック |

**レスポンス構造（JSON形式）:**
```json
{
  "result": true,
  "data": {
    "lineups": [
      {
        "mst_exchange_lineup_id": "lineup_001",
        "reward": {
          "resource_type": "Item",
          "resource_id": "item_001",
          "resource_amount": 10
        },
        "costs": [
          {
            "cost_type": "Coin",
            "cost_id": null,
            "cost_amount": 1000
          }
        ],
        "tradable_count": 10,
        "user_trade_count": 3,
        "user_trade_total_count": 15,
        "remaining_time": {
          "days": 5,
          "hours": 10
        },
        "is_unlimited": false
      }
    ]
  }
}
```

**レスポンスフィールド説明:**
| フィールド名 | 型 | 説明 |
|-------------|-----|------|
| result | boolean | 処理結果 |
| data.lineups | array | ラインナップリスト |
| data.lineups[].mst_exchange_lineup_id | string | ラインナップID |
| data.lineups[].reward | object | 報酬情報 |
| data.lineups[].costs | array | 必要リソースリスト |
| data.lineups[].tradable_count | integer | 交換上限数（NULLは無制限） |
| data.lineups[].user_trade_count | integer | ユーザーの交換回数 |
| data.lineups[].user_trade_total_count | integer | ユーザーの通算交換回数 |

##### 3.2.3 ドメイン設計

**新規ドメイン追加 / 既存ドメイン改修:**
- [x] 新規ドメイン追加

**ファイル構成:**

*新規作成が必要なファイル:*
- [x] `api/app/Domain/Exchange/Entities/ExchangeLineupEntity.php`
- [x] `api/app/Domain/Exchange/Entities/ExchangeLineupCostEntity.php`
- [x] `api/app/Domain/Exchange/UseCases/GetExchangeLineupListUseCase.php`
- [x] `api/app/Domain/Exchange/Services/ExchangeLineupService.php`

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| ExchangeDelegator | getExchangeLineupList() | ラインナップ一覧取得 | Controllerから呼ばれる |
| GetExchangeLineupListUseCase | exec() | ラインナップ一覧取得のユースケース | - |
| ExchangeLineupService | filterActiveLineups() | 期間内のラインナップフィルタリング | - |
| MstExchangeLineupRepository | findByExchangeId() | ラインナップマスタ取得 | - |

**Entity設計:**
- Entityタイプ: Plain Entity
- ExchangeLineupEntity: ラインナップ情報を保持
- ExchangeLineupCostEntity: コスト情報を保持（1:N）

##### 3.2.4 DB設計

**テーブル構造追加 / テーブル構造変更:**
- [x] テーブル構造追加（新規テーブル作成）

**新規テーブル作成の場合:**

*テーブル名:* `mst_exchange_lineups`

*CREATE TABLE文:*
```sql
CREATE TABLE mst_exchange_lineups (
    mst_exchange_lineup_id VARCHAR(255) NOT NULL COMMENT 'ラインナップID',
    mst_exchange_id VARCHAR(255) NOT NULL COMMENT '交換所ID',
    lineup_name VARCHAR(255) NOT NULL COMMENT 'ラインナップ名',
    reward_resource_type VARCHAR(50) NOT NULL COMMENT '報酬リソースタイプ',
    reward_resource_id VARCHAR(255) NULL COMMENT '報酬リソースID',
    reward_resource_amount BIGINT UNSIGNED NOT NULL COMMENT '報酬数量',
    tradable_count INT UNSIGNED NULL COMMENT '交換上限数（NULLは無制限）',
    start_date DATETIME(6) NULL COMMENT '交換期間開始日時（NULLは無期限）',
    end_date DATETIME(6) NULL COMMENT '交換期間終了日時（NULLは無期限）',
    display_order INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '表示順序',
    is_active TINYINT(1) NOT NULL DEFAULT 1 COMMENT '有効フラグ',
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) COMMENT '作成日時',
    updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6) COMMENT '更新日時',
    PRIMARY KEY (mst_exchange_lineup_id),
    INDEX idx_exchange_id (mst_exchange_id),
    INDEX idx_is_active (is_active),
    INDEX idx_start_date (start_date),
    INDEX idx_end_date (end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='交換所ラインナップマスタ';
```

*テーブル名:* `mst_exchange_lineup_costs`

*CREATE TABLE文:*
```sql
CREATE TABLE mst_exchange_lineup_costs (
    mst_exchange_lineup_cost_id VARCHAR(255) NOT NULL COMMENT 'ラインナップコストID',
    mst_exchange_lineup_id VARCHAR(255) NOT NULL COMMENT 'ラインナップID',
    cost_type VARCHAR(50) NOT NULL COMMENT 'コストタイプ',
    cost_id VARCHAR(255) NULL COMMENT 'コストID（アイテムの場合のみ）',
    cost_amount BIGINT UNSIGNED NOT NULL COMMENT 'コスト数量',
    display_order INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '表示順序',
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) COMMENT '作成日時',
    updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6) COMMENT '更新日時',
    PRIMARY KEY (mst_exchange_lineup_cost_id),
    INDEX idx_lineup_id (mst_exchange_lineup_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='交換所ラインナップコストマスタ';
```

*カラム説明（mst_exchange_lineups）:*
| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-----|------|-----------|------|
| mst_exchange_lineup_id | VARCHAR(255) | NOT NULL | - | ラインナップID（主キー） |
| mst_exchange_id | VARCHAR(255) | NOT NULL | - | 交換所ID（外部キー） |
| reward_resource_type | VARCHAR(50) | NOT NULL | - | 報酬リソースタイプ |
| reward_resource_id | VARCHAR(255) | NULL | - | 報酬リソースID |
| reward_resource_amount | BIGINT UNSIGNED | NOT NULL | - | 報酬数量 |
| tradable_count | INT UNSIGNED | NULL | - | 交換上限数（NULLは無制限） |
| start_date | DATETIME(6) | NULL | - | 交換期間開始日時 |
| end_date | DATETIME(6) | NULL | - | 交換期間終了日時 |

*カラム説明（mst_exchange_lineup_costs）:*
| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-----|------|-----------|------|
| mst_exchange_lineup_cost_id | VARCHAR(255) | NOT NULL | - | ラインナップコストID（主キー） |
| mst_exchange_lineup_id | VARCHAR(255) | NOT NULL | - | ラインナップID（外部キー） |
| cost_type | VARCHAR(50) | NOT NULL | - | コストタイプ（Coin/Diamond/Item等） |
| cost_id | VARCHAR(255) | NULL | - | コストID（アイテムの場合のみ） |
| cost_amount | BIGINT UNSIGNED | NOT NULL | - | コスト数量 |

##### 3.2.5 エラーハンドリング

**エラーコード定義:**

| エラーコード | エラー名 | 発生条件 | ユーザーへのメッセージ | 対処方法 |
|-------------|---------|---------|---------------------|---------|
| E_EXCHANGE_002 | LINEUP_NOT_FOUND | ラインナップが存在しない、または期間外 | ラインナップが見つかりません | ラインナップの存在チェックと期間チェック |

##### 3.2.6 実装上の注意事項

**パフォーマンス考慮点:**
- N+1問題の回避: ラインナップとコストを一度のクエリで取得（JOIN使用）
- 期間フィルタリングにINDEXを活用

**データ整合性:**
- 複数コストは正規化テーブル（mst_exchange_lineup_costs）で管理

**既存実装との整合性:**
- MstShopItemテーブルの構造を参考に拡張

---

### 3.3 ユーザー交換履歴管理

#### 要件 HISTORY-1: 交換回数の二重管理

##### 3.3.1 要件概要
- **要件ID:** REQ-HISTORY-1
- **実現内容:** ユーザーの交換履歴は、リセット可能なtrade_countと、リセット不可能な通算trade_total_countの2つのカウンタで管理する

##### 3.3.2 API設計

（ラインナップ取得APIのレスポンスに含まれる）

##### 3.3.3 ドメイン設計

**ファイル構成:**

*新規作成が必要なファイル:*
- [x] `api/app/Domain/Exchange/Models/UsrExchangeLineup.php`
- [x] `api/app/Domain/Exchange/Repositories/UsrExchangeLineupRepository.php`

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| UsrExchangeLineup | incrementTradeCount() | 交換回数のインクリメント | trade_count, trade_total_count両方を+1 |
| UsrExchangeLineup | reset() | リセット処理 | trade_countのみを0にリセット |
| UsrExchangeLineupRepository | findOrCreate() | 履歴の取得または作成 | - |

**Model設計:**
```php
class UsrExchangeLineup extends UsrEloquentModel
{
    protected $fillable = [
        'usr_user_id',
        'mst_exchange_lineup_id',
        'trade_count',
        'trade_total_count',
        'last_reset_at',
    ];

    public function makeModelKey(): string
    {
        return $this->usr_user_id . $this->mst_exchange_lineup_id;
    }

    public function incrementTradeCount(): void
    {
        $this->trade_count++;
        $this->trade_total_count++;
    }

    public function reset(CarbonImmutable $now): void
    {
        $this->trade_count = 0;
        $this->last_reset_at = $now->format('Y-m-d H:i:s');
    }
}
```

##### 3.3.4 DB設計

**テーブル構造追加 / テーブル構造変更:**
- [x] テーブル構造追加（新規テーブル作成）

**新規テーブル作成の場合:**

*テーブル名:* `usr_exchange_lineups`

*CREATE TABLE文:*
```sql
CREATE TABLE usr_exchange_lineups (
    usr_user_id VARCHAR(255) NOT NULL COMMENT 'ユーザーID',
    mst_exchange_lineup_id VARCHAR(255) NOT NULL COMMENT 'ラインナップID',
    trade_count INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '交換回数（リセット対象）',
    trade_total_count INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '通算交換回数（永続）',
    last_reset_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) COMMENT '最終リセット日時',
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) COMMENT '作成日時',
    updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6) COMMENT '更新日時',
    PRIMARY KEY (usr_user_id, mst_exchange_lineup_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザー交換所ラインナップ';
```

*テーブル設計の注意事項:*
- **usrテーブル:** PRIMARY KEYにはusr_user_idを最初のカラムとする複合キー

*カラム説明:*
| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-----|------|-----------|------|
| usr_user_id | VARCHAR(255) | NOT NULL | - | ユーザーID（主キー1） |
| mst_exchange_lineup_id | VARCHAR(255) | NOT NULL | - | ラインナップID（主キー2） |
| trade_count | INT UNSIGNED | NOT NULL | 0 | 交換回数（リセット対象） |
| trade_total_count | INT UNSIGNED | NOT NULL | 0 | 通算交換回数（永続） |
| last_reset_at | DATETIME(6) | NOT NULL | CURRENT_TIMESTAMP(6) | 最終リセット日時 |

##### 3.3.5 エラーハンドリング

（特になし）

##### 3.3.6 実装上の注意事項

**パフォーマンス考慮点:**
- UsrModelManagerのキャッシュ機構を活用
- キャッシュキーは`usr_user_id + mst_exchange_lineup_id`

**データ整合性:**
- trade_countとtrade_total_countは同時にインクリメント
- リセット時はtrade_countのみを0にリセット

**既存実装との整合性:**
- UsrShopItem::incrementTradeCount()と同様の実装パターン

---

#### 要件 HISTORY-2: リセットタイミングの判定

##### 3.3.1 要件概要
- **要件ID:** REQ-HISTORY-2
- **実現内容:** 交換回数のリセット判定は、Clock機能のメソッド（isFirstMonth等）を使用する

##### 3.3.2 API設計

（内部処理のためAPIレベルでの影響なし）

##### 3.3.3 ドメイン設計

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| ExchangeService | resetIfNeeded() | リセット判定と実行 | Clock::isFirstMonth()を使用 |

**リセット判定ロジック:**
```php
public function resetIfNeeded(
    UsrExchangeLineup $usrExchangeLineup,
    string $resetType,
    CarbonImmutable $now
): void {
    if ($resetType === ExchangeResetType::None->value) {
        return; // リセットなし
    }

    if ($resetType === ExchangeResetType::Monthly->value) {
        if ($this->clock->isFirstMonth($usrExchangeLineup->getLastResetAt())) {
            $usrExchangeLineup->reset($now);
        }
    }
}
```

##### 3.3.4 DB設計

（REQ-HISTORY-1で定義済み）

##### 3.3.5 エラーハンドリング

（特になし）

##### 3.3.6 実装上の注意事項

**パフォーマンス考慮点:**
- リセット判定はキャッシュ上で実行（DBアクセス最小化）

**データ整合性:**
- リセット基準時刻はClockの設定（04:00）に従う

**既存実装との整合性:**
- ShopService::resetUsrShopItem()と同様のパターン

---

### 3.4 交換実行処理

#### 要件 EXCHANGE-1: 交換実行の事前バリデーション

##### 3.4.1 要件概要
- **要件ID:** REQ-EXCHANGE-1
- **実現内容:** 交換実行前に、交換所・ラインナップの有効性、交換回数、リソース所持数を検証する

##### 3.4.2 API設計

**新規API追加 / 既存API改修:**
- [x] 新規API追加

**対象エンドポイント:**
- エンドポイント: `/exchange/trade`
- HTTPメソッド: POST
- 認証: 必要

**リクエストパラメータ（JSON形式）:**
```json
{
  "mst_exchange_lineup_id": "lineup_001",
  "trade_amount": 1
}
```

**リクエストパラメータ説明:**
| パラメータ名 | 型 | 必須 | 説明 | バリデーション |
|-------------|-----|------|------|---------------|
| mst_exchange_lineup_id | string | ○ | ラインナップID | 存在チェック |
| trade_amount | integer | ○ | 交換個数 | 1以上 |

**レスポンス構造（JSON形式）:**
```json
{
  "result": true,
  "data": {
    "usr_exchange_lineup": {
      "mst_exchange_lineup_id": "lineup_001",
      "trade_count": 4,
      "trade_total_count": 16
    },
    "usr_parameter": {
      "coin": 50000,
      "diamond": 1000
    },
    "changed_items": [
      {
        "item_id": "item_001",
        "amount": 10
      }
    ]
  }
}
```

##### 3.4.3 ドメイン設計

**ファイル構成:**

*新規作成が必要なファイル:*
- [x] `api/app/Domain/Exchange/UseCases/ExchangeTradeUseCase.php`
- [x] `api/app/Domain/Exchange/Services/ExchangeService.php`

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| ExchangeDelegator | trade() | 交換実行のファサード | Controllerから呼ばれる |
| ExchangeTradeUseCase | exec() | 交換実行のユースケース | - |
| ExchangeService | validateTrade() | 交換バリデーション | 全チェック項目を実行 |
| ExchangeService | validateTradeCount() | 交換上限チェック | - |
| ExchangeService | validateCost() | リソース所持数チェック | - |

**バリデーション順序:**
```php
public function validateTrade(
    MstExchange $mstExchange,
    MstExchangeLineup $mstExchangeLineup,
    UsrExchangeLineup $usrExchangeLineup,
    int $tradeAmount,
    CarbonImmutable $now
): void {
    // 1. 交換所の存在・期間チェック（取得時に実施済み）
    // 2. ラインナップの存在・期間チェック（取得時に実施済み）
    // 3. リセット判定
    $this->resetIfNeeded($usrExchangeLineup, $mstExchange->getResetType(), $now);
    // 4. 交換上限チェック
    $this->validateTradeCount($mstExchangeLineup, $usrExchangeLineup, $tradeAmount);
    // 5. リソース所持数チェック
    $this->validateCost($mstExchangeLineup, $tradeAmount);
    // 6. 交換個数の妥当性チェック
    $this->validateTradeAmount($tradeAmount);
}
```

##### 3.4.4 DB設計

（既に定義済み）

##### 3.4.5 エラーハンドリング

**エラーコード定義:**

| エラーコード | エラー名 | 発生条件 | ユーザーへのメッセージ | 対処方法 |
|-------------|---------|---------|---------------------|---------|
| E_EXCHANGE_003 | TRADE_COUNT_LIMIT | 交換上限到達 | 交換上限に達しています | 交換上限チェック |
| E_EXCHANGE_004 | LACK_OF_RESOURCES | リソース不足 | リソースが不足しています | リソース所持数チェック |
| E_EXCHANGE_005 | INVALID_TRADE_AMOUNT | 交換個数が不正 | 交換個数が正しくありません | 交換個数バリデーション |

**エラーハンドリングの実装方針:**
- バリデーション失敗時は即座にエラーをthrow
- トランザクション実行前にエラー検出

##### 3.4.6 実装上の注意事項

**パフォーマンス考慮点:**
- バリデーションは軽量な処理を先に実行（早期リターン）

**セキュリティ考慮点:**
- クライアントからのtrade_amountパラメータを厳密にバリデーション

**データ整合性:**
- 全バリデーション通過後のみトランザクション実行

**既存実装との整合性:**
- ShopTradeShopItemUseCase::exec()のバリデーション処理パターンを踏襲

---

#### 要件 EXCHANGE-2: トランザクション内でのリソース消費と報酬付与

##### 3.4.1 要件概要
- **要件ID:** REQ-EXCHANGE-2
- **実現内容:** 交換処理（リソース消費と報酬付与）は、トランザクション内で原子性を保証する

##### 3.4.2 API設計

（REQ-EXCHANGE-1で定義済み）

##### 3.4.3 ドメイン設計

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| ExchangeTradeUseCase | exec() | トランザクション制御 | UseCaseTrait::applyUserTransactionChanges()使用 |

**トランザクション処理:**
```php
public function exec(
    CurrentUser $user,
    string $mstExchangeLineupId,
    int $tradeAmount
): ExchangeTradeResultData {
    $usrUserId = $user->id;
    $now = $this->clock->now();

    // マスタデータ取得
    $mstExchangeLineup = $this->mstExchangeLineupRepository->getActiveLineupById($mstExchangeLineupId, $now);
    $mstExchange = $this->mstExchangeRepository->findById($mstExchangeLineup->getMstExchangeId());

    // ユーザー交換履歴取得
    $usrExchangeLineup = $this->usrExchangeLineupRepository->findOrCreate($usrUserId, $mstExchangeLineupId, $now);

    // バリデーション
    $this->exchangeService->validateTrade($mstExchange, $mstExchangeLineup, $usrExchangeLineup, $tradeAmount, $now);

    // 交換回数インクリメント（トランザクション前）
    for ($i = 0; $i < $tradeAmount; $i++) {
        $usrExchangeLineup->incrementTradeCount();
    }

    // トランザクション実行
    $this->applyUserTransactionChanges(function () use (
        $usrUserId,
        $mstExchangeLineup,
        $tradeAmount,
        $now,
    ) {
        // コスト消費
        $this->exchangeService->consumeCosts($usrUserId, $mstExchangeLineup, $tradeAmount);

        // 報酬付与
        $this->rewardDelegator->sendRewards($usrUserId, $platform, $now);
    });

    // レスポンス生成
    return new ExchangeTradeResultData(...);
}
```

##### 3.4.4 DB設計

（既に定義済み）

##### 3.4.5 エラーハンドリング

（トランザクション内で例外が発生した場合、自動的にロールバック）

##### 3.4.6 実装上の注意事項

**パフォーマンス考慮点:**
- トランザクション内の処理は最小限に

**データ整合性:**
- リソース消費と報酬付与は同一トランザクション内
- 例外発生時は全変更をロールバック

**既存実装との整合性:**
- ShopTradeShopItemUseCase::exec()のトランザクション処理パターンを踏襲

---

#### 要件 EXCHANGE-3: リソース消費処理

##### 3.4.1 要件概要
- **要件ID:** REQ-EXCHANGE-3
- **実現内容:** 交換に必要なリソース（コイン、アイテム、ダイヤ等）を消費する

##### 3.4.2 API設計

（REQ-EXCHANGE-1で定義済み）

##### 3.4.3 ドメイン設計

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| ExchangeService | consumeCosts() | 複数コストの消費 | - |
| ExchangeService | consumeCost() | 単一コストの消費 | - |

**リソース消費ロジック:**
```php
public function consumeCosts(
    string $usrUserId,
    MstExchangeLineup $mstExchangeLineup,
    int $tradeAmount
): void {
    $costs = $mstExchangeLineup->getCosts();

    foreach ($costs as $cost) {
        $this->consumeCost($usrUserId, $cost, $tradeAmount);
    }
}

private function consumeCost(
    string $usrUserId,
    ExchangeLineupCostEntity $cost,
    int $tradeAmount
): void {
    $amount = $cost->getCostAmount() * $tradeAmount;

    switch ($cost->getCostType()) {
        case 'Coin':
            $this->userDelegator->consumeCoin($usrUserId, $amount);
            break;
        case 'Diamond':
            $this->appCurrencyDelegator->consumeDiamond($usrUserId, $amount);
            break;
        case 'PaidDiamond':
            $this->appCurrencyDelegator->consumePaidDiamond($usrUserId, $amount);
            break;
        case 'Item':
            $this->usrItemService->consumeItem($usrUserId, $cost->getCostId(), $amount);
            break;
    }
}
```

##### 3.4.4 DB設計

（既に定義済み）

##### 3.4.5 エラーハンドリング

（各消費サービスが自動的にLACK_OF_RESOURCESエラーをthrow）

##### 3.4.6 実装上の注意事項

**パフォーマンス考慮点:**
- 複数コスト消費時も効率的に処理

**データ整合性:**
- 各消費サービスが自動的にログ出力

**既存実装との整合性:**
- ShopService::consumeCost()と同様のロジック

---

#### 要件 EXCHANGE-4: 報酬付与処理

##### 3.4.1 要件概要
- **要件ID:** REQ-EXCHANGE-4
- **実現内容:** 交換で獲得する報酬（アイテム、キャラ、コイン等）をユーザーに付与する

##### 3.4.2 API設計

（REQ-EXCHANGE-1で定義済み）

##### 3.4.3 ドメイン設計

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| ExchangeService | addReward() | 報酬をRewardDelegatorに登録 | - |

**報酬付与ロジック:**
```php
public function addReward(
    MstExchangeLineup $mstExchangeLineup,
    int $tradeAmount
): void {
    $rewardAmount = $mstExchangeLineup->getRewardResourceAmount() * $tradeAmount;

    $this->rewardDelegator->addReward(
        $mstExchangeLineup->getRewardResourceType(),
        $mstExchangeLineup->getRewardResourceId(),
        $rewardAmount
    );
}
```

##### 3.4.4 DB設計

（既に定義済み）

##### 3.4.5 エラーハンドリング

（RewardDelegatorが自動的にエラーハンドリング）

##### 3.4.6 実装上の注意事項

**パフォーマンス考慮点:**
- RewardDelegatorが一括配布を最適化

**データ整合性:**
- 各SendServiceが自動的にログ出力
- アイテム所持上限超過時の自動変換

**既存実装との整合性:**
- ShopService::tradeShopItem()のRewardDelegator利用パターンを踏襲

---

#### 要件 EXCHANGE-6: 交換ログの保存

##### 3.4.1 要件概要
- **要件ID:** REQ-EXCHANGE-6
- **実現内容:** 交換実行時には、交換ログ（ユーザーID、交換物ID、交換回数、コストタイプ、コスト数、実際に受け取った報酬）を保存する

##### 3.4.2 API設計

（内部処理のためAPIレベルでの影響なし）

##### 3.4.3 ドメイン設計

**ファイル構成:**

*新規作成が必要なファイル:*
- [x] `api/app/Domain/Exchange/Models/LogTradeExchangeLineup.php`
- [x] `api/app/Domain/Exchange/Repositories/LogTradeExchangeLineupRepository.php`

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| LogTradeExchangeLineupRepository | create() | 交換ログ保存 | - |

##### 3.4.4 DB設計

**テーブル構造追加 / テーブル構造変更:**
- [x] テーブル構造追加（新規テーブル作成）

**新規テーブル作成の場合:**

*テーブル名:* `log_trade_exchange_lineups`

*CREATE TABLE文:*
```sql
CREATE TABLE log_trade_exchange_lineups (
    log_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ログID',
    usr_user_id VARCHAR(255) NOT NULL COMMENT 'ユーザーID',
    mst_exchange_lineup_id VARCHAR(255) NOT NULL COMMENT 'ラインナップID',
    trade_count INT UNSIGNED NOT NULL COMMENT '交換回数（何回目の交換か）',
    trade_amount INT UNSIGNED NOT NULL COMMENT '交換個数',
    costs JSON NOT NULL COMMENT '消費したコスト情報',
    received_reward JSON NOT NULL COMMENT '実際に受け取った報酬情報',
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) COMMENT '作成日時',
    PRIMARY KEY (log_id),
    INDEX idx_usr_user_id_created_at (usr_user_id, created_at),
    INDEX idx_lineup_id (mst_exchange_lineup_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='交換所取引ログ';
```

*テーブル設計の注意事項:*
- **logテーブル:** PRIMARY KEYはAUTO_INCREMENTのlog_id、usr_user_idとcreated_atの複合インデックスを設定

*カラム説明:*
| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-----|------|-----------|------|
| log_id | BIGINT UNSIGNED | NOT NULL | AUTO_INCREMENT | ログID（主キー） |
| usr_user_id | VARCHAR(255) | NOT NULL | - | ユーザーID |
| mst_exchange_lineup_id | VARCHAR(255) | NOT NULL | - | ラインナップID |
| trade_count | INT UNSIGNED | NOT NULL | - | 交換回数 |
| trade_amount | INT UNSIGNED | NOT NULL | - | 交換個数 |
| costs | JSON | NOT NULL | - | 消費したコスト情報 |
| received_reward | JSON | NOT NULL | - | 実際に受け取った報酬情報 |

##### 3.4.5 エラーハンドリング

（特になし）

##### 3.4.6 実装上の注意事項

**パフォーマンス考慮点:**
- ログ保存は非同期処理を検討（ただし、現状は同期処理でOK）

**データ整合性:**
- ログは永続保存（削除しない）
- JSON形式で柔軟なデータ構造を保持

**既存実装との整合性:**
- LogTradeShopItemRepository::create()と同様の実装パターン

---

### 3.5 原画アイテムの特別処理

#### 要件 ORIGINAL-1: 原画の付与方法

##### 3.5.1 要件概要
- **要件ID:** REQ-ORIGINAL-1
- **実現内容:** 原画を交換した際、原画アイテム（完成品）と原画のかけら16個を同時に付与する

##### 3.5.2 API設計

（REQ-EXCHANGE-1で定義済み。報酬付与ロジックの一部）

##### 3.5.3 ドメイン設計

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| ExchangeService | addOriginalArtReward() | 原画専用の報酬登録 | - |

**原画報酬付与ロジック:**
```php
public function addOriginalArtReward(
    string $originalArtItemId,
    string $originalArtFragmentItemId,
    int $tradeAmount
): void {
    // 原画アイテム（完成品）を付与
    $this->rewardDelegator->addReward(
        'Item',
        $originalArtItemId,
        1 * $tradeAmount
    );

    // 原画のかけら16個を付与
    $this->rewardDelegator->addReward(
        'Item',
        $originalArtFragmentItemId,
        16 * $tradeAmount
    );
}
```

##### 3.5.4 DB設計

（既に定義済み。マスタデータで原画判定フラグを追加する可能性あり）

##### 3.5.5 エラーハンドリング

（特になし）

##### 3.5.6 実装上の注意事項

**パフォーマンス考慮点:**
- 原画判定はマスタデータのフラグで行う（ItemTypeやカスタムフラグ）

**データ整合性:**
- 原画とかけらの両方が正しく付与される

**既存実装との整合性:**
- 既存の原画実装例が見つからないため、新規実装

**判断が必要な事項:**
- 原画判定方法（ItemTypeで判定？カスタムフラグ追加？）
- プランナーと確認が必要

---

### 3.6 複数リソース消費のサポート

#### 要件 MULTI-COST-1: 複数リソース消費の実装

##### 3.6.1 要件概要
- **要件ID:** REQ-MULTI-COST-1
- **実現内容:** 1つのラインナップで複数種類のリソース（例: コイン1000 + アイテムA×10）を同時に消費できる

##### 3.6.2 API設計

（REQ-EXCHANGE-1で定義済み）

##### 3.6.3 ドメイン設計

（REQ-EXCHANGE-3で定義済み。consumeCosts()メソッドで複数コストに対応）

##### 3.6.4 DB設計

（REQ-LINEUP-1で定義済み。mst_exchange_lineup_costsテーブルで1:N構造）

##### 3.6.5 エラーハンドリング

（各リソースの消費時にLACK_OF_RESOURCESエラーをthrow）

##### 3.6.6 実装上の注意事項

**パフォーマンス考慮点:**
- 複数コストのバリデーションは効率的に実行

**データ整合性:**
- 全コストのバリデーション通過後にトランザクション実行
- 1つでも不足している場合はエラー

**既存実装との整合性:**
- 既存のShop機能は単一コストのみのため、新規実装パターン

**判断が必要な事項:**
- 最大組み合わせ数（暫定: 制限なし）
- バリデーション順序（暫定: マスタデータのdisplay_order順）
- プランナーと詳細確認が必要

---

## 4. 実装の優先順位と依存関係

### 4.1 実装の段階分け

**フェーズ1: 基盤実装（Week 1-2）**
1. REQ-MASTER-1: 交換所の種別管理（Enum定義、マスタテーブル作成）
2. REQ-LINEUP-1: ラインナップの詳細設定管理（マスタテーブル、コストテーブル作成）
3. REQ-HISTORY-1: 交換回数の二重管理（usrテーブル作成、Model実装）
4. マイグレーションファイル作成と実行

**フェーズ2: コア機能実装（Week 3-4）**
1. REQ-MASTER-2: 交換所一覧取得API（開催期間管理）
2. REQ-MASTER-3: 残り時間計算
3. REQ-LINEUP-2: ラインナップ一覧取得API（交換期間フィルタリング）
4. REQ-HISTORY-2: リセットタイミング判定
5. REQ-EXCHANGE-1〜5: 交換実行処理（バリデーション、トランザクション、リソース消費、報酬付与）
6. REQ-EXCHANGE-6: 交換ログの保存

**フェーズ3: 拡張機能実装（Week 5）**
1. REQ-ORIGINAL-1: 原画アイテムの特別処理
2. REQ-MULTI-COST-1: 複数リソース消費（既にフェーズ2で対応済みだが、追加テスト必要）
3. エラーハンドリングの充実
4. パフォーマンスチューニング

### 4.2 依存関係マップ

```
REQ-MASTER-1 (交換所マスタ)
  ↓
REQ-LINEUP-1 (ラインナップマスタ) + REQ-HISTORY-1 (usrテーブル)
  ↓
REQ-MASTER-2 (交換所一覧API) + REQ-LINEUP-2 (ラインナップ一覧API)
  ↓
REQ-HISTORY-2 (リセット判定)
  ↓
REQ-EXCHANGE-1 (バリデーション)
  ↓
REQ-EXCHANGE-2 (トランザクション)
  ├→ REQ-EXCHANGE-3 (リソース消費)
  └→ REQ-EXCHANGE-4 (報酬付与)
  ↓
REQ-EXCHANGE-6 (ログ保存)
  ↓
REQ-ORIGINAL-1 (原画処理) + REQ-MULTI-COST-1 (複数コスト)
```

### 4.3 実装時の注意点

- フェーズ1を完了してからフェーズ2に進む（DBスキーマが確定していることが前提）
- 各フェーズ内でも依存関係を考慮した順序で実装する
- テストは各要件実装後に都度実施する
- マスタデータの定義はフェーズ1で完了させる（マスタデータ配信機構との連携確認）

---

## 5. テスト設計概要

### 5.1 ユニットテスト

**テスト対象:**
- Domain\Exchange層の各Service、UseCase、Repository
- ビジネスロジックの正常系・異常系

**テストケース例:**
| テスト対象 | テストケース | 期待結果 |
|-----------|------------|---------|
| ExchangeService::calculateRemainingTime() | end_dateがNULL | is_unlimited = true |
| ExchangeService::calculateRemainingTime() | end_dateが未来 | 正しい残り時間が返る |
| ExchangeService::validateTradeCount() | 交換上限到達 | TRADE_COUNT_LIMIT例外 |
| ExchangeService::resetIfNeeded() | 月次リセット対象 | trade_countが0にリセット |
| UsrExchangeLineup::incrementTradeCount() | 実行 | trade_count, trade_total_count両方+1 |

### 5.2 機能テスト

**テスト対象:**
- APIエンドポイント
- リクエスト/レスポンスの検証
- データベースの状態変化

**テストケース例:**
| API | テストケース | 期待結果 |
|-----|------------|---------|
| POST /exchange/list | 正常なリクエスト | 200 OK、開催期間内の交換所リストが返る |
| POST /exchange/lineup/list | 正常なリクエスト | 200 OK、交換期間内のラインナップリストが返る |
| POST /exchange/trade | 正常なリクエスト | 200 OK、交換完了、trade_count+1 |
| POST /exchange/trade | 交換上限到達 | 400 Bad Request、TRADE_COUNT_LIMIT |
| POST /exchange/trade | リソース不足 | 400 Bad Request、LACK_OF_RESOURCES |

### 5.3 シナリオテスト

**テストシナリオ例:**
1. ユーザーが交換所一覧を取得する
2. 開催期間内の交換所のみが返される
3. 特定の交換所のラインナップ一覧を取得する
4. 交換期間内のラインナップのみが返される
5. ラインナップを1個交換する
6. リソースが消費され、報酬が付与される
7. trade_countとtrade_total_countが+1される
8. 交換ログが記録される
9. 月をまたいでアクセスする
10. trade_countが0にリセットされる（trade_total_countは継続）

---

## 6. マイグレーション計画

### 6.1 マイグレーションファイル一覧

**新規作成が必要なマイグレーション:**
- [x] `YYYY_MM_DD_HHMMSS_create_mst_exchanges_table.php` - mst_exchangesテーブル作成
- [x] `YYYY_MM_DD_HHMMSS_create_mst_exchange_lineups_table.php` - mst_exchange_lineupsテーブル作成
- [x] `YYYY_MM_DD_HHMMSS_create_mst_exchange_lineup_costs_table.php` - mst_exchange_lineup_costsテーブル作成
- [x] `YYYY_MM_DD_HHMMSS_create_usr_exchange_lineups_table.php` - usr_exchange_lineupsテーブル作成
- [x] `YYYY_MM_DD_HHMMSS_create_log_trade_exchange_lineups_table.php` - log_trade_exchange_lineupsテーブル作成

### 6.2 マイグレーション実行順序

1. mst_exchanges（基盤テーブル）
2. mst_exchange_lineups（mst_exchangesを参照）
3. mst_exchange_lineup_costs（mst_exchange_lineupsを参照）
4. usr_exchange_lineups（ユーザーデータ）
5. log_trade_exchange_lineups（ログデータ）

### 6.3 ロールバック方針

- 各マイグレーションにはdown()メソッドを実装する
- ロールバック時は逆順で実行
- ログデータは削除せず、論理削除を検討

---

## 7. 連携が必要なスキル・ツール

**Claude Code スキル:**
- `migration`: マイグレーションファイルの作成・実行
- `domain-layer`: ドメインレイヤーの実装パターン参照
- `api-schema-reference`: glow-schema YAMLとの整合性確認（必要に応じて）
- `api-request-validation`: リクエストパラメータのバリデーション実装
- `api-response`: レスポンス構造の実装
- `api-test-implementation`: テストコードの実装
- `reward-send-service`: 報酬付与機能の実装パターン参照

**使用タイミング:**
- DB設計時: `migration`
- ドメイン実装時: `domain-layer`, `reward-send-service`
- API実装時: `api-request-validation`, `api-response`, `api-schema-reference`
- テスト実装時: `api-test-implementation`

---

## 8. 実装時の判断が必要な事項

### 8.1 技術的な選択肢

**項目:** 複数リソース消費のテーブル設計

- 選択肢A: 交換コストテーブルを正規化（1:N構造）- **採用**
  - メリット: 柔軟性が高い、将来的な拡張に対応しやすい、組み合わせ数の制限なし
  - デメリット: クエリが複雑になる、JOIN処理が増える
- 選択肢B: 1レコードに複数カラムを持たせる（cost_type_1, cost_id_1, cost_amount_1, cost_type_2, ...）
  - メリット: クエリがシンプル、パフォーマンスが高い
  - デメリット: 柔軟性が低い、最大組み合わせ数に制限がある
- **推奨:** 選択肢Aを推奨（柔軟性とメンテナンス性を重視）

**項目:** 原画アイテムの判定方法

- 選択肢A: ItemTypeで判定（既存のアイテムタイプ分類を使用）
  - メリット: 既存の仕組みを活用
  - デメリット: ItemTypeに「原画」という分類がない場合、追加が必要
- 選択肢B: カスタムフラグ追加（is_original_art）
  - メリット: 明確な判定が可能
  - デメリット: マスタデータの拡張が必要
- **推奨:** プランナーと確認して決定（暫定的には選択肢Bを推奨）

### 8.2 仕様の解釈

**項目:** 復刻時のラインナップID運用

- 解釈A: 既存ラインナップIDを再利用
  - この解釈の場合: mst_exchange_lineupsテーブルはそのまま、交換所IDのみ変更
- 解釈B: 新しいラインナップIDを作成
  - この解釈の場合: mst_exchange_lineupsテーブルに新規レコード作成、ユーザー交換履歴も新規
- **確認先:** プランナー（マスタデータ運用担当者）
- **推奨:** 解釈Bを推奨（データの独立性確保）

**項目:** 複数リソース消費のバリデーション順序

- 解釈A: マスタデータのdisplay_order順
  - この解釈の場合: mst_exchange_lineup_costsのdisplay_order順にバリデーション
- 解釈B: コストタイプの優先順位順（例: Diamond → PaidDiamond → Coin → Item）
  - この解釈の場合: システム側で優先順位を定義
- **確認先:** プランナー
- **推奨:** 解釈Aを推奨（マスタデータ設定で柔軟に制御）

---

## 9. 補足情報

### 9.1 参考にすべき既存実装

**類似機能の実装例:**
- Shop機能（`Domain\Shop`）: 交換所機能の基本パターン
  - UsrShopItem: 二重カウンタ管理のパターン
  - ShopTradeShopItemUseCase: トランザクション制御のパターン
  - ShopService: バリデーション、リソース消費、リセット判定のパターン

**参考になるドメイン設計:**
- Reward機能（`Domain\Reward`）: 報酬付与の基盤
  - RewardDelegator: 報酬の一括配布
  - 各SendService: 報酬タイプごとの付与処理

**参考になるDB設計:**
- usr_shop_items: 二重カウンタ管理、キャッシュキー設計
- log_trade_shop_items: ログテーブルの設計パターン
- mst_shop_items: マスタテーブルの設計パターン

### 9.2 参考ドキュメント

- **APIコーディング規約**: @docs/01_project/coding-standards/api-coding-standards.md
  - 命名規則、アーキテクチャパターン、実装パターンの詳細
  - Domain層、Repository層、Controller層の設計パターン
- **マスタデータ配信機構**: @docs/01_project/architecture/マスタデータ配信機構.md
  - マスタデータの配信フロー、S3連携、バージョン管理の仕組み
  - game/version APIの実装パターン

### 9.3 実装時のTips

**よくあるハマりポイント:**
- UsrModelManagerのキャッシュキー設計: makeModelKey()で複合キーを正しく生成する
- Clock機能のリセット判定: 基準時刻（04:00）を考慮する
- トランザクション制御: UseCaseTrait::applyUserTransactionChanges()の使い方を理解する
- RewardDelegator: addReward()で登録後、sendRewards()で一括配布を忘れない

**パフォーマンスチューニングのポイント:**
- N+1問題の回避: ラインナップとコストを一度のクエリで取得（eagerLoad使用）
- INDEXの活用: 期間フィルタリング、ユーザーIDでの検索に適切なINDEXを設定
- キャッシュの活用: UsrModelManagerのキャッシュ機構を正しく活用

**エラーハンドリングのコツ:**
- 早期リターン: 軽量なバリデーションを先に実行してエラー検出
- 適切なエラーコード: ユーザーに分かりやすいエラーメッセージを返す
- ログ出力: トラブルシューティングのために詳細なログを記録

---

**作成日:** 2025-11-26
**作成者:** Claude Code Agent
**次のステップ:** フェーズ1の実装開始（マイグレーション作成、Enum定義、基盤テーブル作成）

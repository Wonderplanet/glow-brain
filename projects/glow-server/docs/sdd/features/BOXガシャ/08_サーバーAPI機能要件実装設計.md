# サーバーAPI機能要件実装設計

## 1. ドキュメント情報
- 対象機能: BOXガシャ
- 作成日: 2025-11-26
- 参照ドキュメント:
  - 05_サーバーAPI要件書.md
  - APIコーディング規約: @docs/01_project/coding-standards/api-coding-standards.md
  - マスタデータ配信機構: @docs/01_project/architecture/マスタデータ配信機構.md

## 2. 実装設計概要

### 2.1 実装方針
- **既存ガチャ実装パターンの踏襲:**
  - 既存のGachaDrawUseCase、GachaService、RewardDelegatorの設計パターンを参考にする
  - トランザクション管理、コスト消費→報酬配布の順序、ログ記録などの実装パターンを流用
  - ただし、抽選ロジックはBOXガシャ固有のため新規実装

- **クリーンアーキテクチャの遵守:**
  - Domain層で BOXガシャドメイン（Domain\BoxGacha）を新規作成
  - Controller → Delegator → UseCase → Service → Repository の階層構造を維持
  - DelegatorのreturnTypeは必ずarrayとする

- **新規ドメインとして実装:**
  - 既存のGachaドメインとは独立した BoxGachaドメインを作成
  - これにより、既存ガチャへの影響を最小化し、保守性を向上

### 2.2 実装の全体像

**新規追加されるコンポーネント:**
- APIエンドポイント: POST /api/box-gacha/draw, POST /api/box-gacha/next, GET /api/box-gacha/progress
- ドメイン: Domain\BoxGacha（Entities, Models, Repositories, Services, UseCases, Delegators）
- DBテーブル: opr_box_gachas（BOX固有設定）, usr_box_gachas（ユーザーBOX進捗・残りアイテム）

**既存コンポーネントの活用:**
- opr_gachas: BOXガシャのガチャ基本情報を格納（gacha_type='Box'）
- opr_gacha_prizes: BOXラインナップを格納（group_id="{box_gacha_id}_box{number}"）
- usr_gachas: ユーザーごとの累積引き回数を記録
- log_gacha_actions: BOXガシャのログを記録
- RewardDelegator: 既存の報酬配布処理を流用（改修不要）
- GachaMissionTriggerService: 既存のミッショントリガー送信処理を流用（改修不要）

**アーキテクチャ上の考慮点:**
- BOXガシャは既存ガチャ機能のインフラを活用しつつ、抽選方式のみBOX専用ロジックを実装
- 既存のガチャマスタデータ構造を活用し、BOX固有の設定のみopr_box_gachasで拡張
- ユーザーデータは2つのテーブルに集約（usr_box_gachas: BOX進捗と残りアイテムをJSON管理）
- マスタデータ配信は既存のマスタデータ配信機構に従う（S3経由でクライアントに配信）

## 3. 機能要件別実装設計

### 3.1 BOXラインナップ管理

#### 要件 REQ-BOX-001: BOXマスタデータの参照

##### 3.1.1 要件概要
- **要件ID:** REQ-BOX-001
- **実現内容:** BOXガシャイベントごとのラインナップ情報をマスタデータから取得する
- **実装方針:** 既存のopr_gachas、opr_gacha_prizesテーブルを活用し、BOX固有設定のみopr_box_gachasテーブルで拡張

##### 3.1.2 API設計

**新規API追加 / 既存API改修:**
- [x] 新規APIは不要（既存のマスタデータ配信機構を利用）

**マスタデータ配信:**
- 既存のマスタデータ配信機構（S3経由）でクライアントに配信
- サーバーAPIでは opr_gachas、opr_gacha_prizes、opr_box_gachas テーブルから参照のみ

##### 3.1.3 ドメイン設計

**新規ドメイン追加 / 既存ドメイン改修:**
- [x] 新規ドメイン追加

**対象ドメイン:**
- ドメイン分類: 通常ドメイン
- ドメイン名: `Domain\BoxGacha`

**ファイル構成:**

*新規作成が必要なファイル:*
- [x] `api/app/Domain/BoxGacha/Entities/OprBoxGachaEntity.php`
- [x] `api/app/Domain/BoxGacha/Models/OprBoxGacha.php` (Eloquent Model)
- [x] `api/app/Domain/BoxGacha/Repositories/OprBoxGachaRepository.php`

*既存ドメインの活用:*
- `Domain\Gacha\Entities\OprGachaEntity` - ガチャ基本情報（gacha_type='Box'として活用）
- `Domain\Gacha\Entities\OprGachaPrizeEntity` - BOXラインナップ（group_id="{box_gacha_id}_box{number}"として活用）
- `Domain\Gacha\Repositories\OprGachaRepository` - ガチャマスタ取得
- `Domain\Gacha\Repositories\OprGachaPrizeRepository` - ラインナップ取得

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| OprBoxGachaRepository | findById($boxGachaId) | BOX固有設定取得 | opr_box_gachasから取得 |
| OprGachaRepository | findById($gachaId) | ガチャ基本情報取得 | 既存リポジトリを活用 |
| OprGachaPrizeRepository | findByGroupId($groupId) | BOXラインナップ取得 | group_id="{box_gacha_id}_box{number}"で取得 |

**Entity設計:**
- Entityタイプ: Eloquent Entity
- 主要プロパティ:
  - OprBoxGachaEntity: `id`, `total_box_count`, `infinite_box_group_id`, `cost_item_id`, `cost_per_draw`（JSON）

##### 3.1.4 DB設計

**テーブル構造追加 / テーブル構造変更:**
- [x] テーブル構造追加（新規テーブル作成）

**新規テーブル作成の場合:**

*テーブル名:* `opr_box_gachas`

*CREATE TABLE文:*
```sql
CREATE TABLE opr_box_gachas (
    id VARCHAR(255) NOT NULL COMMENT 'BOXガシャID（opr_gachas.idと一致）',
    total_box_count INT UNSIGNED NOT NULL DEFAULT 1 COMMENT '通常BOXの総数（無限ボックス除く）',
    infinite_box_group_id VARCHAR(255) NULL COMMENT '無限ボックスのgroup_id（NULL: 通常BOX最後のBOXと同じラインナップ）',
    cost_item_id VARCHAR(255) NOT NULL COMMENT 'コストアイテムID（アイテムA）',
    cost_per_draw TEXT NOT NULL COMMENT 'コスト設定（JSON形式）例: {"1": 150, "10": 1500}',
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) COMMENT '作成日時',
    updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6) COMMENT '更新日時',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='BOXガシャ固有設定';
```

**既存テーブルの活用:**

*テーブル名:* `opr_gachas`（既存テーブル）
- BOXガシャのレコードを追加（gacha_type='Box'）
- start_at, end_atでイベント期間を管理
- prize_group_idでBOX1のラインナップを指定（例: "box_gacha_001_box1"）

*テーブル名:* `opr_gacha_prizes`（既存テーブル）
- BOXラインナップをgroup_id="{box_gacha_id}_box{number}"で管理
- BOX1: group_id="box_gacha_001_box1"
- BOX2: group_id="box_gacha_001_box2"
- 無限BOX: group_id="box_gacha_001_infinite"

##### 3.1.5 エラーハンドリング

**エラーコード定義:**

| エラーコード | エラー名 | 発生条件 | ユーザーへのメッセージ | 対処方法 |
|-------------|---------|---------|---------------------|---------|
| E_BOX_GACHA_001 | BOX_GACHA_NOT_FOUND | BOXガシャIDが存在しない | BOXガシャが見つかりません | マスタデータの存在チェック |

**エラーハンドリングの実装方針:**
- Repositoryレイヤーでマスタデータが見つからない場合は例外をthrow
- UseCaseレイヤーでキャッチし、適切なエラーレスポンスを返却

##### 3.1.6 実装上の注意事項

**マスタデータに関する考慮点:**
- マスタデータは既存のマスタデータ配信機構に従いS3経由でクライアントに配信
- サーバーAPIでは opr_gachas、opr_gacha_prizes、opr_box_gachas テーブルからの読み取りのみ
- BOXラインナップはopr_gacha_prizesに格納（group_idで各BOXを識別）
- cost_per_drawはJSON形式で柔軟なコスト設定を可能にする（{"1": 150, "10": 1500}など）

---

### 3.2 BOX進捗管理

#### 要件 REQ-BOX-002, REQ-PROG-001, REQ-PROG-002: BOX残りアイテム管理、BOX番号管理、引き回数記録

##### 3.2.1 要件概要
- **要件ID:** REQ-BOX-002, REQ-PROG-001, REQ-PROG-002
- **実現内容:** ユーザーごとにBOXの残りアイテム、BOX番号、引き回数を管理する

##### 3.2.2 API設計

**新規API追加 / 既存API改修:**
- [x] 新規API追加

**対象エンドポイント:**
- エンドポイント: `/box-gacha/progress`
- HTTPメソッド: GET
- 認証: 必要

**リクエストパラメータ（クエリパラメータ）:**
| パラメータ名 | 型 | 必須 | 説明 | バリデーション |
|-------------|-----|------|------|---------------|
| boxGachaId | string | ○ | BOXガシャID | opr_gachasに存在すること |

**レスポンス構造（JSON形式）:**
```json
{
  "boxGacha": {
    "boxGachaId": "box_gacha_001",
    "name": "〇〇イベントBOXガシャ",
    "startAt": "2025-11-01T00:00:00Z",
    "endAt": "2025-11-30T23:59:59Z",
    "totalBoxCount": 3
  },
  "boxProgress": {
    "currentBoxNumber": 1,
    "remainingItemsCount": 95,
    "drewCount": 5
  },
  "costInfo": {
    "costItemId": "item_a",
    "costPerDraw": {
      "1": 150,
      "10": 1500
    },
    "currentAmount": 3000
  }
}
```

##### 3.2.3 ドメイン設計

**ファイル構成:**

*新規作成が必要なファイル:*
- [x] `api/app/Domain/BoxGacha/Entities/UsrBoxGachaEntity.php`
- [x] `api/app/Domain/BoxGacha/Models/UsrBoxGacha.php` (Eloquent Model)
- [x] `api/app/Domain/BoxGacha/Repositories/UsrBoxGachaRepository.php`
- [x] `api/app/Domain/BoxGacha/Services/BoxGachaProgressService.php`
- [x] `api/app/Domain/BoxGacha/UseCases/BoxGachaProgressUseCase.php`
- [x] `api/app/Domain/BoxGacha/Delegators/BoxGachaDelegator.php`

*既存ドメインの活用:*
- `Domain\Gacha\Repositories\UsrGachaRepository` - 累積引き回数の管理に活用

*改修が必要な既存ファイル:*
- [x] `api/app/Http/Controllers/BoxGachaController.php` (新規作成)

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| BoxGachaDelegator | getProgress($usrUserId, $boxGachaId): array | BOX進捗情報取得 | Controllerから呼ばれる |
| BoxGachaProgressUseCase | execute($usrUser, $boxGachaId) | BOX進捗情報取得のビジネスロジック | - |
| BoxGachaProgressService | initializeProgress($usrUserId, $boxGachaId) | BOX進捗の初期化 | 初回アクセス時 |
| BoxGachaProgressService | getRemainingItemsCount($usrUserId, $boxGachaId) | 残りアイテム数取得 | JSONから計算 |
| UsrBoxGachaRepository | findByUserAndBoxGacha($usrUserId, $boxGachaId) | ユーザーBOX進捗取得 | - |

##### 3.2.4 DB設計

**新規テーブル作成の場合:**

*テーブル名:* `usr_box_gachas`

*CREATE TABLE文:*
```sql
CREATE TABLE usr_box_gachas (
    usr_user_id VARCHAR(255) NOT NULL COMMENT 'ユーザーID',
    opr_gacha_id VARCHAR(255) NOT NULL COMMENT 'BOXガシャID（opr_gachas.id）',
    current_box_number INT UNSIGNED NOT NULL DEFAULT 1 COMMENT '現在のBOX番号（1, 2, 3, ...）',
    drew_count INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '現在BOXで引いた回数',
    remaining_prizes_json TEXT NOT NULL COMMENT '残りアイテム情報（JSON形式）[{"prizeId": "prize_001", "count": 5}, ...]',
    last_drew_at DATETIME(6) NULL COMMENT '最終実行日時',
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) COMMENT '作成日時',
    updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6) COMMENT '更新日時',
    PRIMARY KEY (usr_user_id, opr_gacha_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザーBOX進捗と残りアイテム';
```

**既存テーブルの活用:**

*テーブル名:* `usr_gachas`（既存テーブル）
- BOXガシャの累積引き回数を記録
- count フィールドで全BOXを通じた累積引き回数を管理

##### 3.2.5 エラーハンドリング

**エラーコード定義:**

| エラーコード | エラー名 | 発生条件 | ユーザーへのメッセージ | 対処方法 |
|-------------|---------|---------|---------------------|---------|
| E_BOX_GACHA_001 | BOX_GACHA_NOT_FOUND | BOXガシャIDが存在しない | BOXガシャが見つかりません | マスタデータの存在チェック |

##### 3.2.6 実装上の注意事項

**データ整合性:**
- 初回アクセス時、usr_box_gachasとusr_gachasをトランザクション内で初期化
- 残りアイテム数の取得はremaining_prizes_json（JSON形式）をデコードして計算
- JSONフォーマット: `[{"prizeId": "prize_001", "count": 5}, {"prizeId": "prize_002", "count": 3}]`

---

### 3.3 BOXガシャ抽選

#### 要件 REQ-DRAW-001, REQ-DRAW-002, REQ-COST-001, REQ-REWARD-001: BOXガシャ抽選処理

##### 3.3.1 要件概要
- **要件ID:** REQ-DRAW-001, REQ-DRAW-002, REQ-COST-001, REQ-REWARD-001
- **実現内容:** BOX内の残りアイテムから抽選し、コスト消費、報酬配布を実行する

##### 3.3.2 API設計

**新規API追加 / 既存API改修:**
- [x] 新規API追加

**対象エンドポイント:**
- エンドポイント: `/box-gacha/draw`
- HTTPメソッド: POST
- 認証: 必要

**リクエストパラメータ（JSON形式）:**
```json
{
  "boxGachaId": "box_gacha_001",
  "playNum": 1,
  "drewCount": 5
}
```

**リクエストパラメータ説明:**
| パラメータ名 | 型 | 必須 | 説明 | バリデーション |
|-------------|-----|------|------|---------------|
| boxGachaId | string | ○ | BOXガシャID | opr_gachasに存在すること |
| playNum | integer | ○ | 引く回数 | マスタで定義された回数であること、残りアイテム数以下であること |
| drewCount | integer | ○ | クライアント側の累計引き回数 | サーバー側の記録と一致すること |

**レスポンス構造（JSON形式）:**
```json
{
  "gachaRewards": [...],
  "boxProgress": {
    "currentBoxNumber": 1,
    "remainingItemsCount": 90,
    "drewCount": 10
  },
  "usrUnits": [...],
  "usrItems": [...],
  "usrParameter": {...}
}
```

##### 3.3.3 ドメイン設計

**ファイル構成:**

*新規作成が必要なファイル:*
- [x] `api/app/Domain/BoxGacha/Services/BoxGachaDrawService.php`
- [x] `api/app/Domain/BoxGacha/Services/BoxGachaLotteryService.php`
- [x] `api/app/Domain/BoxGacha/Services/BoxGachaValidationService.php`
- [x] `api/app/Domain/BoxGacha/Services/BoxGachaCostService.php`
- [x] `api/app/Domain/BoxGacha/UseCases/BoxGachaDrawUseCase.php`

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| BoxGachaDelegator | draw($usrUserId, $boxGachaId, $playNum, $drewCount): array | BOXガシャ実行 | Controllerから呼ばれる |
| BoxGachaDrawUseCase | execute($usrUser, $boxGachaId, $playNum, $drewCount) | BOXガシャ実行のビジネスロジック | トランザクション管理 |
| BoxGachaValidationService | validateExpiration($boxGacha) | イベント期間チェック | - |
| BoxGachaValidationService | validatePlayNum($boxGacha, $playNum) | 引く回数チェック | - |
| BoxGachaValidationService | validateRemainingItems($remainingItemsCount, $playNum) | 残りアイテム数チェック | - |
| BoxGachaValidationService | validateDrewCount($usrProgress, $drewCount) | 引いた回数のズレチェック | - |
| BoxGachaCostService | validateCost($usrUser, $costItemId, $costAmount) | コスト所持数チェック | - |
| BoxGachaCostService | consumeCost($usrUser, $costItemId, $costAmount) | コスト消費 | - |
| BoxGachaLotteryService | lottery($remainingItems, $playNum) | BOX残りアイテムから抽選 | - |
| BoxGachaLotteryService | updateRemainingItems($usrUserId, $boxGachaId, $boxNumber, $lotteryResults) | 残りアイテムを更新 | - |
| BoxGachaDrawService | updateProgress($usrProgress, $playNum) | BOX進捗更新 | - |
| BoxGachaDrawService | checkBoxEmpty($remainingItemsCount) | BOXが空かチェック | - |
| BoxGachaDrawService | moveToNextBox($usrProgress) | 次のBOXに移行 | - |

##### 3.3.4 DB設計

**テーブル構造追加 / テーブル構造変更:**
- DB設計は3.2と同じ（usr_box_gachas, usr_gachas）
- 抽選時、remaining_prizes_jsonを更新して引いたアイテムを除外
- 既存のlog_gacha_actionsテーブルにログを記録

##### 3.3.5 エラーハンドリング

**エラーコード定義:**

| エラーコード | エラー名 | 発生条件 | ユーザーへのメッセージ | 対処方法 |
|-------------|---------|---------|---------------------|---------|
| E_BOX_GACHA_001 | BOX_GACHA_NOT_FOUND | BOXガシャIDが存在しない | BOXガシャが見つかりません | マスタデータの存在チェック |
| E_BOX_GACHA_002 | BOX_GACHA_EXPIRED | イベント期間外 | イベント期間外です | イベント期間チェック |
| E_BOX_GACHA_003 | BOX_GACHA_INSUFFICIENT_ITEMS | 残りアイテム不足 | 残りアイテムが不足しています | 残りアイテム数チェック |
| E_BOX_GACHA_004 | BOX_GACHA_INSUFFICIENT_COST | コスト不足 | アイテムが不足しています | コスト所持数チェック |
| E_BOX_GACHA_005 | BOX_GACHA_INVALID_PLAY_NUM | 引く回数が不正 | 不正なリクエストです | マスタで定義された回数かチェック |
| E_BOX_GACHA_006 | BOX_GACHA_DREW_COUNT_MISMATCH | 引いた回数のズレ | データが同期されていません | クライアント側とサーバー側の引いた回数を比較 |

**エラーハンドリングの実装方針:**
- Serviceレイヤーでバリデーションを実施し、エラー時は例外をthrow
- UseCaseレイヤーでキャッチし、トランザクションロールバック
- Controllerレイヤーで適切なエラーレスポンスを返却

##### 3.3.6 実装上の注意事項

**トランザクション制御:**
- applyUserTransactionChanges()を使用してトランザクション管理
- コスト消費→報酬配布の順序を厳守
- エラー発生時は自動的にロールバック

**抽選ロジック:**
- remaining_prizes_jsonをデコードして配列化し、PHPのarray_rand()で抽選
- 抽選されたアイテムをJSONから除外して更新

**パフォーマンス考慮点:**
- remaining_prizes_jsonのデコード/エンコードのオーバーヘッドは軽微（100アイテム程度）
- JSON形式により柔軟な拡張性を確保

---

### 3.4 BOX進行（次BOXへの移行）

#### 要件 REQ-RESET-001, REQ-RESET-002: 手動BOX進行、無限ボックスのBOX進行

##### 3.4.1 要件概要
- **要件ID:** REQ-RESET-001, REQ-RESET-002
- **実現内容:** BOXを次に進める（次のBOXへの移行または無限ボックスでのリセット）

##### 3.4.2 API設計

**新規API追加 / 既存API改修:**
- [x] 新規API追加

**対象エンドポイント:**
- エンドポイント: `/box-gacha/next`
- HTTPメソッド: POST
- 認証: 必要

**リクエストパラメータ（JSON形式）:**
```json
{
  "boxGachaId": "box_gacha_001"
}
```

##### 3.4.3 ドメイン設計

**ファイル構成:**

*新規作成が必要なファイル:*
- [x] `api/app/Domain/BoxGacha/Services/BoxGachaNextService.php`
- [x] `api/app/Domain/BoxGacha/UseCases/BoxGachaNextUseCase.php`

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| BoxGachaDelegator | moveToNext($usrUserId, $boxGachaId): array | 次BOXへ進む | Controllerから呼ばれる |
| BoxGachaNextUseCase | execute($usrUser, $boxGachaId) | BOX進行のビジネスロジック | トランザクション管理 |
| BoxGachaNextService | moveToNextBox($usrBoxGacha, $boxGacha) | 次BOXへ移行処理 | - |
| BoxGachaNextService | determineNextBoxNumber($currentBoxNumber, $totalBoxCount) | 次のBOX番号を決定 | - |
| BoxGachaNextService | initializeRemainingPrizes($usrUserId, $boxGachaId, $nextBoxNumber) | 残りアイテムを再初期化（JSON更新） | - |

##### 3.4.4 DB設計

**テーブル構造追加 / テーブル構造変更:**
- DB設計は3.2と同じ（usr_box_gachas, usr_gachas）
- current_box_numberを更新、remaining_prizes_jsonを次のBOXのラインナップで再初期化

##### 3.4.5 エラーハンドリング

**エラーコード定義:**

| エラーコード | エラー名 | 発生条件 | ユーザーへのメッセージ | 対処方法 |
|-------------|---------|---------|---------------------|---------|
| E_BOX_GACHA_001 | BOX_GACHA_NOT_FOUND | BOXガシャIDが存在しない | BOXガシャが見つかりません | マスタデータの存在チェック |
| E_BOX_GACHA_002 | BOX_GACHA_EXPIRED | イベント期間外 | イベント期間外です | イベント期間チェック |

##### 3.4.6 実装上の注意事項

**BOX番号の更新:**
- 通常BOXの場合: current_box_number++
- 無限ボックスの場合: current_box_number変更なし（同じBOX番号で再初期化）

**残りアイテムの再初期化:**
- remaining_prizes_jsonを次のBOXのラインナップで上書き
- opr_gacha_prizesテーブルから次のBOXのgroup_idで取得して、JSON形式に変換

---

## 4. 実装の優先順位と依存関係

### 4.1 実装の段階分け

**フェーズ1: 基盤実装**
1. REQ-BOX-001: BOXマスタデータの参照（opr_box_gachasテーブル作成、既存テーブル活用、Repository実装）
2. REQ-BOX-002, REQ-PROG-001, REQ-PROG-002: BOX進捗管理（usr_box_gachasテーブル作成、Repository実装）
3. GET /api/box-gacha/progress: BOX進捗情報取得API実装

**フェーズ2: コア機能実装**
1. REQ-DRAW-001, REQ-DRAW-002, REQ-COST-001, REQ-REWARD-001: BOXガシャ抽選処理
2. POST /api/box-gacha/draw: BOXガシャ実行API実装

**フェーズ3: 拡張機能実装**
1. REQ-RESET-001, REQ-RESET-002: BOX進行処理
2. POST /api/box-gacha/next: BOX進行API実装

### 4.2 依存関係マップ

```
REQ-BOX-001 (BOXマスタ参照 - opr_box_gachas + 既存テーブル活用)
  ↓
REQ-BOX-002, REQ-PROG-001, REQ-PROG-002 (BOX進捗管理 - usr_box_gachas)
  ↓
GET /api/box-gacha/progress (進捗情報取得)
  ↓
REQ-DRAW-001 (BOXガシャ抽選) → REQ-COST-001 (コスト消費) → REQ-REWARD-001 (報酬配布)
  ↓
POST /api/box-gacha/draw (ガシャ実行)
  ↓
REQ-RESET-001, REQ-RESET-002 (BOX進行)
  ↓
POST /api/box-gacha/next (次BOXへ進む)
```

### 4.3 実装時の注意点

- フェーズ1を完了してからフェーズ2に進む（BOX進捗管理が基盤となるため）
- POST /api/box-gacha/drawは複雑なので、段階的に実装（まずコスト消費と報酬配布を既存実装で動作確認してから、抽選ロジックを追加）
- テストは各フェーズ完了後に実施（ユニットテスト、機能テスト、シナリオテスト）

---

## 5. テスト設計概要

### 5.1 ユニットテスト

**テスト対象:**
- BoxGachaLotteryService::lottery() - 抽選ロジックのテスト
- BoxGachaValidationService::validateXxx() - バリデーションロジックのテスト
- BoxGachaDrawService::updateProgress() - BOX進捗更新のテスト
- BoxGachaResetService::resetBox() - BOXリセットのテスト

**テストケース例:**
| テスト対象 | テストケース | 期待結果 |
|-----------|------------|---------|
| BoxGachaLotteryService::lottery() | 残りアイテム100個から10個抽選 | 正しく10個抽選され、重複なし |
| BoxGachaValidationService::validateRemainingItems() | 引く回数=10、残りアイテム=5 | 例外がthrowされる |
| BoxGachaDrawService::checkBoxEmpty() | 残りアイテム=0 | trueが返る |
| BoxGachaResetService::determineNextBoxNumber() | 現在BOX=3、総BOX数=3 | 無限ボックス番号が返る |

### 5.2 機能テスト

**テスト対象:**
- POST /api/box-gacha/draw
- POST /api/box-gacha/next
- GET /api/box-gacha/progress

**テストケース例:**
| API | テストケース | 期待結果 |
|-----|------------|---------|
| POST /api/box-gacha/draw | 正常なリクエスト（1回引き） | 200 OK、獲得アイテムが返る |
| POST /api/box-gacha/draw | 残りアイテム不足 | 400 Bad Request、エラーメッセージ |
| POST /api/box-gacha/draw | 引いた回数のズレ | 400 Bad Request、エラーメッセージ |
| POST /api/box-gacha/next | 正常なリクエスト | 200 OK、次のBOX番号が返る |
| GET /api/box-gacha/progress | 初回アクセス | 200 OK、初期化された進捗情報が返る |

### 5.3 シナリオテスト

**テストシナリオ例:**
1. BOX1を100回引く → BOXが空になる → 自動的にBOX2に移行
2. BOX1を50回引く → 手動リセット → BOX2に移行
3. 通常BOXを全て引き終わる → 無限ボックスに移行 → リセット → 無限ボックスで再初期化
4. 同時リクエスト（同じユーザーが同時にガシャを引く）→ トランザクションにより整合性が保たれる

---

## 6. マイグレーション計画

### 6.1 マイグレーションファイル一覧

**新規作成が必要なマイグレーション:**
- [ ] `YYYY_MM_DD_HHMMSS_create_opr_box_gachas_table.php` - opr_box_gachasテーブル作成（BOX固有設定）
- [ ] `YYYY_MM_DD_HHMMSS_create_usr_box_gachas_table.php` - usr_box_gachasテーブル作成（ユーザーBOX進捗と残りアイテム）

**既存テーブルの活用（マイグレーション不要）:**
- opr_gachas - BOXガシャの基本情報を格納（gacha_type='Box'として活用）
- opr_gacha_prizes - BOXラインナップを格納（group_idで各BOXを識別）
- usr_gachas - 累積引き回数を記録
- log_gacha_actions - BOXガシャのログを記録

### 6.2 マイグレーション実行順序

1. マスタテーブルの作成（opr_box_gachas）
2. ユーザーテーブルの作成（usr_box_gachas）

### 6.3 ロールバック方針

- 各マイグレーションにはdown()メソッドを実装し、DROP TABLEでロールバック
- ロールバック時、ユーザーデータは失われるため、本番環境でのロールバックは原則禁止
- マイグレーション実行前にDBバックアップを取得

---

## 7. 連携が必要なスキル・ツール

**Claude Code スキル:**
- `migration`: マイグレーションファイルの作成・実行
- `domain-layer`: ドメインレイヤーの実装パターン参照
- `api-request-validation`: リクエストパラメータのバリデーション実装
- `api-response`: レスポンス構造の実装
- `api-test-implementation`: テストコードの実装
- `sail-check-fixer`: コード品質チェック（phpcs, phpstan, deptrac）

**使用タイミング:**
- DB設計完了後: `migration` でマイグレーションファイル作成
- ドメイン実装時: `domain-layer` で実装パターン参照
- API実装時: `api-request-validation`, `api-response` で実装
- テスト実装時: `api-test-implementation` でテストコード作成
- PR作成前: `sail-check-fixer` でコード品質チェック

---

## 8. 実装時の判断が必要な事項

### 8.1 技術的な選択肢

**項目:** BOX残りアイテムのデータ構造
- **採用案:** JSON形式で残りアイテムを管理（remaining_prizes_json）
  - メリット:
    - 1テーブルに集約でき、DB設計がシンプル
    - レコード数が最小（ユーザー数×ガシャ数）
    - 柔軟な拡張性（JSON形式により将来的な仕様変更に対応しやすい）
  - デメリット:
    - JSONのエンコード/デコードのオーバーヘッド（軽微）
    - RDBMSの検索機能が活用できない（BOX進捗取得では必要ない）
  - 実装方針:
    - `remaining_prizes_json`フィールドに配列をJSON形式で保存
    - フォーマット: `[{"prizeId": "prize_001", "count": 5}, ...]`
    - 抽選時にデコード→更新→エンコードして保存

### 8.2 仕様の解釈

**項目:** BOX途中進行時の残りアイテムの扱い
- **採用案:** 破棄（残りアイテムを削除し、次のBOXのラインナップで再初期化）
  - 実装方法: remaining_prizes_jsonを次のBOXのラインナップで上書き
  - 理由: 一般的なBOXガシャの仕様に準拠
- 代替案: 持ち越し（残りアイテムを次のBOXに追加）
  - 実装方法: 既存のremaining_prizes_jsonと次のBOXのラインナップをマージ
- 確認先: ゲーム企画担当者
- 現時点: 破棄で実装、確認後に変更可能な設計とする

---

## 9. 補足情報

### 9.1 参考にすべき既存実装

- **GachaDrawUseCase**: トランザクション管理、コスト消費→報酬配布の順序、ログ記録の実装パターン
- **GachaService**: バリデーション、抽選ロジックの実装パターン
- **RewardDelegator**: 報酬配布処理の実装パターン
- **GachaMissionTriggerService**: ミッショントリガー送信の実装パターン

### 9.2 参考ドキュメント

- **APIコーディング規約**: @docs/01_project/coding-standards/api-coding-standards.md
  - 命名規則、アーキテクチャパターン、実装パターンの詳細
- **マスタデータ配信機構**: @docs/01_project/architecture/マスタデータ配信機構.md
  - マスタデータの配信フロー、S3連携、バージョン管理の仕組み

### 9.3 実装時のTips

- **トランザクション管理:** applyUserTransactionChanges()を使用し、エラー時は自動ロールバック
- **抽選ロジック:** 残りアイテムリストを配列化してからarray_rand()で抽選すると実装がシンプル
- **N+1問題:** 残りアイテムリストの取得はEagerLoadingを活用してN+1を回避
- **パフォーマンスチューニング:** BOXマスタデータはキャッシュに保存し、DB負荷を軽減

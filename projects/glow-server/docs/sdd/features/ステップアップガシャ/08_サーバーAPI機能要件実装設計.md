# サーバーAPI機能要件実装設計

## 1. ドキュメント情報
- 対象機能: ステップアップガシャ
- 作成日: 2025-12-16
- 参照ドキュメント:
  - 05_サーバーAPI要件書.md
  - 実装方針補足.txt
  - APIコーディング規約: @docs/01_project/coding-standards/api-coding-standards.md
  - マスタデータ配信機構: @docs/01_project/architecture/マスタデータ配信機構.md

## 2. 実装設計概要

### 2.1 実装方針

本機能は、既存のガシャシステムを拡張し、ステップアップガシャを実現します。

**基本方針:**
- **新規APIエンドポイントは追加しない** - 既存の `/api/gacha/draw/*` APIを拡張
- **新規Domainは追加しない** - 既存の `Domain\Gacha` を拡張
- **レスポンス構造の拡張** - `usrGacha` 内にステップアップガシャ用のパラメータを追加（ステップアップガシャ以外では空）
- **既存パターンの踏襲** - GachaService、GachaRepository等の既存実装パターンを維持
- **トランザクション制御** - 既存のガシャと同様のトランザクション管理

**実装方針補足.txtの重要ポイント:**
- リクエスト時のステップ数の整合性チェックはクライアント側で行い、サーバーは `usr_gachas` の状態を信頼
- `opr_gachas` の `prize_group_id`, `fixed_prize_group_id` をステップ共通IDとし、`opr_stepup_gacha_steps` で個別設定がない場合はこれを参照
- `/api/gacha/draw/ad` はステップアップガシャに対応しない

### 2.2 実装の全体像

#### 新規追加されるコンポーネント

**テーブル:**
- `opr_stepup_gachas` - ステップアップガシャの基本情報（最大ステップ数、最大周回数）
- `opr_stepup_gacha_steps` - ステップごとの詳細設定（コスト、排出回数、確定枠条件）
- `usr_gachas` - カラム追加: `current_step_number`, `loop_count`（ステップ進行状況）
- `log_gacha_actions` - カラム追加: `step_number`, `loop_count`（ステップ実行履歴）

**Enum:**
- `GachaType::STEPUP` - `opr_gachas.gacha_type` に追加

**Entity:**
- `OprStepupGachaEntity` - マスタデータEntity
- `OprStepupGachaStepEntity` - ステップ設定Entity

**Model:**
- `OprStepupGacha` - Eloquent Model
- `OprStepupGachaStep` - Eloquent Model
- `UsrGacha` - Eloquent Model（拡張: `current_step_number`, `loop_count` カラム追加）
- `LogGachaAction` - Eloquent Model（拡張: `step_number`, `loop_count` カラム追加）

**Repository:**
- `OprStepupGachaRepository` - マスタデータ取得
- `OprStepupGachaStepRepository` - ステップ設定取得
- `UsrGachaRepository` - ユーザー状態管理（拡張）
- `LogGachaActionRepository` - ログ記録（拡張）

**Service拡張:**
- `GachaService` - ステップアップガシャ対応のメソッド追加
  - `drawStepupGacha()` - ステップアップガシャ専用の抽選処理
  - `progressStepupGachaStep()` - ステップ進行処理
  - `getStepupGachaStatus()` - ステップ進行状況取得

#### 既存コンポーネントへの影響範囲

**UseCase拡張:**
- `GachaDrawUseCase::exec()` - ステップアップガシャ判定と処理分岐を追加
- `GachaHistoryUseCase::exec()` - ステップアップガシャ履歴の返却対応
- `GachaPrizeUseCase::exec()` - ステップアップガシャ賞品情報の返却対応
- `UpdateAndFetchUseCase::exec()` - レスポンスにステップアップガシャ情報を追加

**Response拡張:**
- `GachaDrawResultData` - `usrGacha` に `currentStepNumber`, `loopCount` フィールドを追加
- `UpdateAndFetchResultData` - 同様に拡張

**Schema拡張:**
- `glow-schema/Schema/Gacha.yml` - レスポンス定義の拡張

### 2.3 アーキテクチャ上の考慮点

#### Domain層の責務分離

既存のGachaDomainを拡張する形で実装し、以下の責務分離を維持します：

- **Delegator**: Controllerからの呼び出し窓口（戻り値は必ずarray）
- **UseCase**: ビジネスロジックの実行（トランザクション制御）
- **Service**: ドメインロジック（抽選、ステップ進行、検証）
- **Repository**: データアクセス層（CRUD操作）
- **Entity**: ドメインデータ構造（Eloquent Modelとの変換）

#### トランザクション設計

ステップアップガシャの実行は、以下の処理を1トランザクションで完結させます：

1. ステップ進行状況の取得と検証
2. コスト消費
3. 抽選処理（通常枠 + 確定枠）
4. 報酬配布
5. ステップ進行（`current_step_number` インクリメント、周回処理）
6. ガシャ履歴保存（`usr_gachas`, `log_gacha_actions` に `step_number`, `loop_count` を含めて記録）
7. ミッショントリガー送信

#### 確定枠抽選ロジック

既存の10連ガシャの確定枠ロジック（最後の1回確定）を拡張し、以下を実現：

- 任意の確定枠数（0〜10）に対応
- レアリティ条件付き抽選（SR以上、SSR以上等）
- 確定枠は常に最後のN回

例：10連で確定枠3体の場合
- 1〜7回目: 通常抽選
- 8〜10回目: 確定枠抽選（レアリティ条件を満たすアイテムから）

## 3. 機能要件別実装設計

### 3.1 マスタデータ管理

#### 要件 MD-1: ステップアップガシャの基本情報管理

##### 3.1.1 要件概要
- **要件ID:** REQ-MD-1
- **実現内容:** ステップアップガシャ全体の基本情報（最大ステップ数、最大周回数）をマスタデータで管理する

##### 3.1.2 API設計

**新規API追加 / 既存API改修:**
- [x] 既存API改修（マスタデータ取得は内部処理のみ、新規APIなし）

**対象エンドポイント:**
- N/A（マスタデータはS3経由でクライアントに配信、サーバーAPIでは参照のみ）

##### 3.1.3 ドメイン設計

**新規ドメイン追加 / 既存ドメイン改修:**
- [x] 既存ドメイン改修

**対象ドメイン:**
- ドメイン分類: Game
- ドメイン名: `Domain\Gacha` （既存ドメインを拡張）

**ファイル構成:**

*新規作成が必要なファイル:*
- [x] `api/app/Domain/Resource/Mst/Models/OprStepupGacha.php` - Eloquent Model
- [x] `api/app/Domain/Resource/Mst/Entities/OprStepupGachaEntity.php` - Entity
- [x] `api/app/Domain/Resource/Mst/Repositories/OprStepupGachaRepository.php` - Repository

*改修が必要な既存ファイル:*
- [ ] `api/app/Domain/Gacha/Enums/GachaType.php` - `STEPUP = 'StepUp'` を追加

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| OprStepupGacha | - | Eloquent Model | テーブルマッピング |
| OprStepupGachaEntity | toArray() | Entity変換 | Domain境界越えで使用 |
| OprStepupGachaRepository | findByOprGachaId() | ガシャID指定で取得 | 見つからない場合null |
| OprStepupGachaRepository | getByOprGachaId() | ガシャID指定で取得 | 見つからない場合例外 |

**Entity設計:**
- Entityタイプ: Plain Entity（マスタデータなのでキャッシュ不要）
- 主要プロパティ:
  - `opr_gacha_id`: 親ガシャID（`opr_gachas` への外部キー）
  - `max_step_number`: 最大ステップ数（1〜10）
  - `max_loop_count`: 最大周回数（NULLの場合は無限周回）
- 既存Entityとの関係: `OprGachaEntity` から参照される

##### 3.1.4 DB設計

**テーブル構造追加 / テーブル構造変更:**
- [x] テーブル構造追加（新規テーブル作成）
- [ ] テーブル構造変更（`opr_gachas.gacha_type` に `'StepUp'` を追加）

**新規テーブル作成の場合:**

*テーブル名:* `opr_stepup_gachas`

*CREATE TABLE文:*
```sql
CREATE TABLE opr_stepup_gachas (
    opr_gacha_id BIGINT UNSIGNED NOT NULL COMMENT '親ガシャID（opr_gachasへの外部キー）',
    max_step_number TINYINT UNSIGNED NOT NULL COMMENT '最大ステップ数（1-10）',
    max_loop_count INT UNSIGNED NULL COMMENT '最大周回数（NULLの場合は無限周回）',
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) COMMENT '作成日時',
    updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6) COMMENT '更新日時',
    PRIMARY KEY (opr_gacha_id),
    CONSTRAINT fk_opr_stepup_gachas_opr_gacha_id FOREIGN KEY (opr_gacha_id) REFERENCES opr_gachas(opr_gacha_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ステップアップガシャ基本情報';
```

*カラム説明:*
| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-----|------|-----------|------|
| opr_gacha_id | BIGINT UNSIGNED | NOT NULL | - | 親ガシャID（`opr_gachas` への外部キー、PRIMARY KEY） |
| max_step_number | TINYINT UNSIGNED | NOT NULL | - | 最大ステップ数（1〜10） |
| max_loop_count | INT UNSIGNED | NULL | - | 最大周回数（NULLの場合は無限周回） |
| created_at | DATETIME(6) | NOT NULL | CURRENT_TIMESTAMP(6) | 作成日時 |
| updated_at | DATETIME(6) | NOT NULL | CURRENT_TIMESTAMP(6) | 更新日時 |

**既存テーブル変更の場合:**

*テーブル名:* `opr_gachas`

*ALTER TABLE文:*
```sql
-- gacha_typeカラムのENUM定義に 'StepUp' を追加
ALTER TABLE opr_gachas
MODIFY COLUMN gacha_type ENUM('Normal', 'Tutorial', 'StepUp') NOT NULL DEFAULT 'Normal' COMMENT 'ガシャタイプ';
```

*変更内容説明:*
- カラム変更: `gacha_type` - ステップアップガシャタイプ `'StepUp'` を追加

##### 3.1.5 エラーハンドリング

**エラーコード定義:**

| エラーコード | エラー名 | 発生条件 | ユーザーへのメッセージ | 対処方法 |
|-------------|---------|---------|---------------------|---------|
| E_GACHA_001 | STEPUP_GACHA_NOT_FOUND | ステップアップガシャマスタデータが存在しない | ガシャ情報が見つかりません | `opr_stepup_gachas` の存在チェック |
| E_GACHA_002 | INVALID_STEP_NUMBER | `max_step_number` が範囲外（1〜10以外） | ガシャ設定に不備があります | マスタデータのバリデーション |

**エラーハンドリングの実装方針:**
- Repository層で見つからない場合は `null` を返す（`find*` メソッド）または例外をthrow（`get*` メソッド）
- Service層でバリデーションエラーをthrow
- エラーレスポンスは既存のガシャエラーと同じ形式

##### 3.1.6 実装上の注意事項

**パフォーマンス考慮点:**
- マスタデータはキャッシュ不要（頻繁に変更されないため）
- ガシャ実行時に毎回DBから取得（ただし、同一リクエスト内では使い回し）

**セキュリティ考慮点:**
- マスタデータの改ざん防止（管理ツール経由でのみ更新）
- `max_loop_count` のNULL許容（無限周回を表現）

**データ整合性:**
- `opr_gachas` に対応するレコードが存在すること（外部キー制約）
- `max_step_number` は1以上10以下であること

**既存実装との整合性:**
- `OprGachaRepository` と同様のパターンで実装
- Entityへの変換は `toEntity()` メソッドで統一

**マスタデータに関する考慮点:**
- S3経由でクライアントに配信される（`game/version` APIでパスとハッシュ値を返却）
- サーバーAPIでは `opr_stepup_gachas` テーブルからの読み取りのみ行う
- マスタデータの更新は管理ツール（admin）経由で実施

---

#### 要件 MD-2: ステップごとの詳細設定管理

##### 3.1.2 要件概要
- **要件ID:** REQ-MD-2
- **実現内容:** 各ステップの詳細設定（コスト、排出回数、確定枠条件等）をマスタデータで管理する

##### 3.1.2 API設計

**新規API追加 / 既存API改修:**
- [x] 既存API改修（マスタデータ取得は内部処理のみ）

**対象エンドポイント:**
- N/A（マスタデータはS3経由でクライアントに配信）

##### 3.1.3 ドメイン設計

**新規ドメイン追加 / 既存ドメイン改修:**
- [x] 既存ドメイン改修

**対象ドメイン:**
- ドメイン分類: Game
- ドメイン名: `Domain\Gacha`

**ファイル構成:**

*新規作成が必要なファイル:*
- [x] `api/app/Domain/Resource/Mst/Models/OprStepupGachaStep.php` - Eloquent Model
- [x] `api/app/Domain/Resource/Mst/Entities/OprStepupGachaStepEntity.php` - Entity
- [x] `api/app/Domain/Resource/Mst/Repositories/OprStepupGachaStepRepository.php` - Repository

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| OprStepupGachaStep | - | Eloquent Model | テーブルマッピング |
| OprStepupGachaStepEntity | toArray() | Entity変換 | Domain境界越えで使用 |
| OprStepupGachaStepRepository | getListByOprStepupGachaId() | ステップ一覧取得 | Collection<OprStepupGachaStep> |
| OprStepupGachaStepRepository | findByStepNumber() | ステップ番号指定で取得 | 見つからない場合null |
| OprStepupGachaStepRepository | getCurrentStepConfig() | 現在ステップの設定取得 | usr_gachasのcurrent_step_numberから現在ステップを取得して返す |

**Entity設計:**
- Entityタイプ: Plain Entity
- 主要プロパティ:
  - `opr_stepup_gacha_id`: ステップアップガシャID
  - `step_number`: ステップ番号（1〜10）
  - `cost_type`: コストの種類（Diamond, PaidDiamond, Item, Free）
  - `cost_id`: コストのID（Itemの場合にアイテムID）
  - `cost_num`: コストの数
  - `draw_count`: 排出回数（1, 10等）
  - `fixed_prize_count`: 確定枠数（0〜10）
  - `fixed_prize_rarity_threshold_type`: 確定枠レアリティ条件（SR以上、SSR以上等）
  - `prize_group_id`: 賞品グループID（NULLの場合は親の `opr_gachas.prize_group_id` を参照）
  - `fixed_prize_group_id`: 確定枠賞品グループID（NULLの場合は親の `opr_gachas.fixed_prize_group_id` を参照）
  - `is_first_free`: 初回のみ無料フラグ
- 既存Entityとの関係: `OprStepupGachaEntity` から参照される

##### 3.1.4 DB設計

**テーブル構造追加 / テーブル構造変更:**
- [x] テーブル構造追加（新規テーブル作成）

**新規テーブル作成の場合:**

*テーブル名:* `opr_stepup_gacha_steps`

*CREATE TABLE文:*
```sql
CREATE TABLE opr_stepup_gacha_steps (
    opr_stepup_gacha_id BIGINT UNSIGNED NOT NULL COMMENT 'ステップアップガシャID',
    step_number TINYINT UNSIGNED NOT NULL COMMENT 'ステップ番号（1-10）',
    cost_type VARCHAR(50) NOT NULL COMMENT 'コストの種類（Diamond, PaidDiamond, Item, Free）',
    cost_id BIGINT UNSIGNED NULL COMMENT 'コストのID（Itemの場合にアイテムID）',
    cost_num INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'コストの数',
    draw_count TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '排出回数（何連ガシャか：1, 10等）',
    fixed_prize_count TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '確定枠数（0-10）',
    fixed_prize_rarity_threshold_type VARCHAR(50) NULL COMMENT '確定枠レアリティ条件',
    prize_group_id BIGINT UNSIGNED NULL COMMENT '賞品グループID（NULLの場合は親のopr_gachas.prize_group_idを参照）',
    fixed_prize_group_id BIGINT UNSIGNED NULL COMMENT '確定枠賞品グループID（NULLの場合は親のopr_gachas.fixed_prize_group_idを参照）',
    is_first_free TINYINT(1) NOT NULL DEFAULT 0 COMMENT '初回のみ無料フラグ（0: 毎周回無料, 1: 初回のみ無料）',
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) COMMENT '作成日時',
    updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6) COMMENT '更新日時',
    PRIMARY KEY (opr_stepup_gacha_id, step_number),
    INDEX idx_step_number (step_number),
    CONSTRAINT fk_opr_stepup_gacha_steps_opr_stepup_gacha_id FOREIGN KEY (opr_stepup_gacha_id) REFERENCES opr_stepup_gachas(opr_gacha_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ステップアップガシャのステップごと設定';
```

*カラム説明:*
| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-----|------|-----------|------|
| opr_stepup_gacha_id | BIGINT UNSIGNED | NOT NULL | - | ステップアップガシャID（PRIMARY KEYの一部） |
| step_number | TINYINT UNSIGNED | NOT NULL | - | ステップ番号（1〜10、PRIMARY KEYの一部） |
| cost_type | VARCHAR(50) | NOT NULL | - | コストの種類（Diamond, PaidDiamond, Item, Free） |
| cost_id | BIGINT UNSIGNED | NULL | - | コストのID（Itemの場合にアイテムID） |
| cost_num | INT UNSIGNED | NOT NULL | 0 | コストの数 |
| draw_count | TINYINT UNSIGNED | NOT NULL | 1 | 排出回数（1連、10連等） |
| fixed_prize_count | TINYINT UNSIGNED | NOT NULL | 0 | 確定枠数（0〜10） |
| fixed_prize_rarity_threshold_type | VARCHAR(50) | NULL | - | 確定枠レアリティ条件 |
| prize_group_id | BIGINT UNSIGNED | NULL | - | 賞品グループID（NULLの場合は親を参照） |
| fixed_prize_group_id | BIGINT UNSIGNED | NULL | - | 確定枠賞品グループID（NULLの場合は親を参照） |
| is_first_free | TINYINT(1) | NOT NULL | 0 | 初回のみ無料フラグ |
| created_at | DATETIME(6) | NOT NULL | CURRENT_TIMESTAMP(6) | 作成日時 |
| updated_at | DATETIME(6) | NOT NULL | CURRENT_TIMESTAMP(6) | 更新日時 |

##### 3.1.5 エラーハンドリング

**エラーコード定義:**

| エラーコード | エラー名 | 発生条件 | ユーザーへのメッセージ | 対処方法 |
|-------------|---------|---------|---------------------|---------|
| E_GACHA_003 | STEPUP_STEP_CONFIG_NOT_FOUND | ステップ設定が存在しない | ガシャ設定に不備があります | `opr_stepup_gacha_steps` の存在チェック |
| E_GACHA_004 | INVALID_DRAW_COUNT | `draw_count` が不正（0以下） | ガシャ設定に不備があります | マスタデータのバリデーション |
| E_GACHA_005 | INVALID_FIXED_PRIZE_COUNT | `fixed_prize_count` が `draw_count` を超える | ガシャ設定に不備があります | マスタデータのバリデーション |

**エラーハンドリングの実装方針:**
- Repository層で設定が見つからない場合は例外をthrow
- Service層でコスト整合性をバリデーション
- マスタデータ設定の不備は管理ツール側で事前チェック

##### 3.1.6 実装上の注意事項

**パフォーマンス考慮点:**
- ステップ設定は一括取得してキャッシュ（同一リクエスト内で使い回し）
- N+1問題の回避（ステップ一覧取得時に全ステップ分を一度に取得）

**セキュリティ考慮点:**
- `cost_type = 'Free'` の場合、`cost_num = 0` であることを検証
- `is_first_free` フラグと周回数による無料判定

**データ整合性:**
- 全ステップ（1〜`max_step_number`）の設定が存在すること
- `prize_group_id` または親の `opr_gachas.prize_group_id` が設定されていること
- `fixed_prize_count > 0` の場合、`fixed_prize_group_id` または親の `opr_gachas.fixed_prize_group_id` が設定されていること

**既存実装との整合性:**
- `OprGachaPrizeRepository` と連携して賞品情報を取得
- `CostType` enumを活用

**マスタデータに関する考慮点:**
- ステップ共通の賞品グループIDは `opr_gachas` で管理
- ステップ個別の設定は `opr_stepup_gacha_steps` で管理
- NULLの場合は親を参照する仕組み

---

### 3.2 ユーザーデータ管理

#### 要件 UD-1: ステップ進行状況の管理

##### 3.2.1 要件概要
- **要件ID:** REQ-UD-1
- **実現内容:** ユーザーごとのステップアップガシャの進行状況（現在のステップ番号、周回数）を管理する

##### 3.2.2 API設計

**新規API追加 / 既存API改修:**
- [x] 既存API改修（`/api/game/update_and_fetch` レスポンスに追加）

**対象エンドポイント:**
- エンドポイント: `/api/game/update_and_fetch`
- HTTPメソッド: POST
- 認証: 必要

**レスポンス構造（JSON形式）の拡張:**
```json
{
  "result": true,
  "data": {
    "usrGacha": [
      {
        "oprGachaId": "12345",
        "count": 5,
        "playedAt": "2025-12-16T10:00:00Z",
        "stepupGacha": {
          "currentStepNumber": 3,
          "loopCount": 0,
          "maxStepNumber": 10,
          "maxLoopCount": null
        }
      }
    ]
  }
}
```

**レスポンスフィールド説明(追加部分):**
| フィールド名 | 型 | 説明 |
|-------------|-----|------|
| usrGacha[].currentStepNumber | integer or null | 現在のステップ番号（1〜10、ステップアップガシャ以外はnull） |
| usrGacha[].loopCount | integer or null | 現在の周回数（0以上、ステップアップガシャ以外はnull） |

##### 3.2.3 ドメイン設計

**新規ドメイン追加 / 既存ドメイン改修:**
- [x] 既存ドメイン改修

**対象ドメイン:**
- ドメイン分類: Game
- ドメイン名: `Domain\Gacha`

**ファイル構成:**

*改修が必要な既存ファイル:*
- [ ] `api/app/Domain/Gacha/Services/GachaService.php` - ステップ進行状況取得メソッド追加
- [ ] `api/app/Http/Responses/ResultData/UpdateAndFetchResultData.php` - レスポンス拡張

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| UsrGacha | - | Eloquent Model | テーブルマッピング（current_step_number、loop_count追加） |
| UsrGachaRepository | - | 既存メソッド拡張 | ステップアップガシャ情報も含めて取得・更新 |
| GachaService | getStepupGachaStatus() | ステップ進行状況取得 | update_and_fetch用 |
| GachaService | progressStepupGachaStep() | ステップ進行処理 | ステップ番号インクリメント、周回処理 |

**Entity設計:**
- Entityタイプ: 既存UsrGachaEntityの拡張（キャッシュ不要）
- 追加プロパティ:
  - `current_step_number`: 現在のステップ番号（NULL可）
  - `loop_count`: 周回数（NULL可）
  - `opr_stepup_gacha_id`: ステップアップガシャID
  - `current_step_number`: 現在のステップ番号（1〜10）
  - `loop_count`: 現在の周回数（0以上）
  - `created_at`: 初回実行日時
  - `updated_at`: 最終更新日時
- 既存Entityとの関係: `UsrGachaEntity` と並行して管理

##### 3.2.4 DB設計

**テーブル構造追加 / テーブル構造変更:**
- [x] テーブル構造追加（新規テーブル作成）

**既存テーブルへのカラム追加:**

*テーブル名:* `usr_gachas`

*ALTER TABLE文:*
```sql
ALTER TABLE usr_gachas
ADD COLUMN current_step_number TINYINT UNSIGNED NULL COMMENT '現在のステップ番号（1-10、ステップアップガシャのみ）',
ADD COLUMN loop_count INT UNSIGNED NULL COMMENT '現在の周回数（0以上、ステップアップガシャのみ）';
```

*追加カラム説明:*
| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-----|------|-----------|------|
| current_step_number | TINYINT UNSIGNED | NULL | NULL | 現在のステップ番号（ステップアップガシャの場合のみNULL以外） |
| loop_count | INT UNSIGNED | NULL | NULL | 現在の周回数（ステップアップガシャの場合のみNULL以外、0=初回） |

*補足:*
- ステップアップガシャ以外のガシャでは、これらのカラムはNULLのまま
- ステップアップガシャ初回実行時に `current_step_number = 1`, `loop_count = 0` を設定
- `usr_gachas` には累計回数とステップ進行状況の両方を記録

##### 3.2.5 エラーハンドリング

**エラーコード定義:**

| エラーコード | エラー名 | 発生条件 | ユーザーへのメッセージ | 対処方法 |
|-------------|---------|---------|---------------------|---------|
| E_GACHA_006 | STEPUP_STATUS_NOT_FOUND | ステップ進行状況が見つからない（内部エラー） | ガシャ情報の取得に失敗しました | 初回実行時に作成、以降は取得 |
| E_GACHA_007 | STEPUP_LOOP_LIMIT_EXCEEDED | 周回数上限に達している | このガシャは実行できません | 周回数上限チェック |

**エラーハンドリングの実装方針:**
- Repository層で初回実行時に自動作成
- Service層で周回数上限チェック
- エラー時は既存のガシャエラーと同じ形式で返却

##### 3.2.6 実装上の注意事項

**パフォーマンス考慮点:**
- ガシャ実行時に毎回DBから取得（同一トランザクション内で整合性を保つ）
- INDEXは `usr_user_id` を含むPRIMARY KEYで最適化

**セキュリティ考慮点:**
- `current_step_number` はサーバー側で管理し、クライアントからの値は参考程度
- 不正なステップ実行を防止（サーバー側の状態を信頼）

**データ整合性:**
- トランザクション内でステップ進行とガシャ実行を同時処理
- ロールバック時にステップ進行もロールバック

**既存実装との整合性:**
- `usr_gachas` に累計回数とステップ進行状況の両方を記録

---

### 3.3 ガシャ実行処理

#### 要件 GE-1: ステップアップガシャの実行

##### 3.3.1 要件概要
- **要件ID:** REQ-GE-1
- **実現内容:** ユーザーが現在のステップのガシャを実行し、抽選・報酬配布・ステップ進行を行う

##### 3.3.2 API設計

**新規API追加 / 既存API改修:**
- [x] 既存API改修

**対象エンドポイント:**
- エンドポイント: `/api/gacha/draw/diamond`, `/api/gacha/draw/paid_diamond`, `/api/gacha/draw/item`, `/api/gacha/draw/free`
- HTTPメソッド: POST
- 認証: 必要

**リクエストパラメータ（JSON形式）:**
```json
{
  "gacha_id": "12345",
  "cost_type": "Diamond",
  "cost_id": null,
  "cost_num": 300,
  "play_num": 10
}
```

**リクエストパラメータ説明:**
| パラメータ名 | 型 | 必須 | 説明 | バリデーション |
|-------------|-----|------|------|---------------|
| gacha_id | string | ○ | ガシャID | 存在チェック |
| cost_type | string | ○ | コストタイプ | Diamond, PaidDiamond, Item, Free |
| cost_id | string or null | △ | コストID（Itemの場合） | Itemの場合は必須 |
| cost_num | integer | ○ | コスト数 | 0以上 |
| play_num | integer | ○ | 実行回数（draw_count） | 1以上 |

**レスポンス構造（JSON形式）:**
```json
{
  "result": true,
  "data": {
    "rewards": [
      {
        "rewardType": "Unit",
        "rewardId": "10001",
        "rewardAmount": 1
      }
    ],
    "usrGacha": {
      "oprGachaId": "12345",
      "count": 6,
      "playedAt": "2025-12-16T10:30:00Z",
      "stepupGacha": {
        "currentStepNumber": 4,
        "loopCount": 0,
        "maxStepNumber": 10,
        "maxLoopCount": null
      }
    }
  }
}
```

**レスポンスフィールド説明:**
| フィールド名 | 型 | 説明 |
|-------------|-----|------|
| rewards | array | 排出された報酬リスト |
| usrGacha.currentStepNumber | integer or null | 実行後の現在ステップ番号 |
| usrGacha.loopCount | integer or null | 実行後の周回数 |

##### 3.3.3 ドメイン設計

**新規ドメイン追加 / 既存ドメイン改修:**
- [x] 既存ドメイン改修

**対象ドメイン:**
- ドメイン分類: Game
- ドメイン名: `Domain\Gacha`

**ファイル構成:**

*改修が必要な既存ファイル:*
- [ ] `api/app/Domain/Gacha/UseCases/GachaDrawUseCase.php` - ステップアップガシャ判定と処理分岐
- [ ] `api/app/Domain/Gacha/Services/GachaService.php` - ステップアップガシャ専用メソッド追加
- [ ] `api/app/Http/Controllers/Api/Gacha/DrawController.php` - Controller（変更なし、UseCaseを呼ぶだけ）

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| GachaDrawUseCase | exec() | ガシャ実行の統括 | ステップアップガシャ判定を追加 |
| GachaService | drawStepupGacha() | ステップアップガシャ専用の抽選処理 | 新規メソッド |
| GachaService | progressStepupGachaStep() | ステップ進行処理 | 新規メソッド |
| GachaService | validateStepupGachaExecution() | ステップアップガシャ実行前検証 | 新規メソッド |
| UsrGachaRepository | progressStep() | ステップ番号インクリメント | トランザクション内で実行、usr_gachasのcurrent_step_number更新 |

**処理フロー（GachaDrawUseCase::exec）:**

```php
public function exec(...): GachaDrawResultData
{
    // 1. ガシャマスタデータ取得
    $oprGacha = $this->gachaService->getOprGacha($oprGachaId);
    
    // 2. ガシャタイプ判定
    if ($oprGacha->getGachaType() === GachaType::STEPUP) {
        // ステップアップガシャの場合
        return $this->executeStepupGacha($usr, $oprGacha, $costType, $playNum, $costId, $costNum);
    } else {
        // 通常ガシャの場合（既存処理）
        return $this->executeNormalGacha($usr, $oprGacha, $costType, $playNum, $costId, $costNum);
    }
}

private function executeStepupGacha(...): GachaDrawResultData
{
    // トランザクション開始
    return DB::transaction(function () use (...) {
        // 1. ユーザーガシャ情報取得（ステップ進行状況含む）
        $usrGacha = $this->usrGachaRepository->findByUserAndGacha($userId, $oprGachaId);
        
        // 2. 現在ステップの設定取得
        $currentStepNumber = $usrGacha?->getCurrentStepNumber() ?? 1;
        $stepConfig = $this->oprStepupGachaStepRepository->findByStepNumber($oprGachaId, $currentStepNumber);
        
        // 3. バリデーション
        $this->gachaService->validateStepupGachaExecution($oprGacha, $usrGacha, $stepConfig, $costType, $costNum, $playNum);
        
        // 4. コスト消費
        $this->consumeCost($costType, $costId, $costNum, $stepConfig);
        
        // 5. 抽選処理
        $gachaResultData = $this->gachaService->drawStepupGacha($oprGacha, $stepConfig, $playNum);
        
        // 6. 報酬配布
        $rewards = $this->rewardDelegator->giveReward($userId, $gachaResultData->getRewards());
        
        // 7. ステップ進行（usr_gachasのcurrent_step_number、loop_count更新）
        $this->gachaService->progressStepupGachaStep($userId, $oprGachaId, $usrGacha);
        
        // 8. ガシャ履歴保存（log_gacha_actionsにstep_number、loop_count保存）
        $this->saveGachaHistory($userId, $oprGachaId, $gachaResultData, $costType, $playNum, $currentStepNumber, $usrGacha->getLoopCount());
        
        // 9. ミッショントリガー送信
        $this->gachaMissionTriggerService->send($userId, $oprGachaId, $playNum);
        
        return new GachaDrawResultData($rewards, $usrGacha);
    });
}
```

##### 3.3.4 DB設計

**テーブル構造追加 / テーブル構造変更:**
- [ ] 既存テーブルを使用（`usr_gachas`, `log_gacha_actions` 等）

##### 3.3.5 エラーハンドリング

**エラーコード定義:**

| エラーコード | エラー名 | 発生条件 | ユーザーへのメッセージ | 対処方法 |
|-------------|---------|---------|---------------------|---------|
| E_GACHA_008 | STEPUP_COST_MISMATCH | リクエストのコスト情報とマスタデータが不一致 | コスト情報が正しくありません | コスト整合性チェック |
| E_GACHA_009 | STEPUP_PLAY_NUM_MISMATCH | リクエストの実行回数とマスタデータが不一致 | 実行回数が正しくありません | 実行回数整合性チェック |
| E_GACHA_010 | STEPUP_LOOP_LIMIT_EXCEEDED | 周回数上限到達 | このガシャは実行できません | 周回数上限チェック |
| E_GACHA_011 | STEPUP_GACHA_EXPIRED | ガシャ期間外 | このガシャは現在利用できません | 期間チェック |

**エラーハンドリングの実装方針:**
- UseCase層でトランザクション制御、エラー時はロールバック
- Service層でバリデーションエラーをthrow
- エラーレスポンスは既存のガシャエラーと同じ形式

##### 3.3.6 実装上の注意事項

**パフォーマンス考慮点:**
- トランザクション時間を最小化（不要な処理は事前実行）
- N+1問題の回避（ステップ設定、賞品情報の一括取得）

**セキュリティ考慮点:**
- コスト整合性の厳密な検証（チート対策）
- サーバー側の状態（`usr_gachas`）を信頼し、クライアントの値は参考程度

**データ整合性:**
- トランザクション内で全処理を完結
- ロールバック時にすべての変更を取り消し

**既存実装との整合性:**
- `GachaDrawUseCase` の既存処理を維持
- `GachaService::draw` の既存ロジックを参考にステップアップガシャ専用メソッドを実装

---

#### 要件 GE-2: 確定枠抽選処理

##### 3.3.2 要件概要
- **要件ID:** REQ-GE-2
- **実現内容:** レアリティ条件付き確定枠の抽選処理を実装する

##### 3.3.2 API設計

**新規API追加 / 既存API改修:**
- [x] 内部処理（API変更なし）

##### 3.3.3 ドメイン設計

**改修が必要な既存ファイル:**
- [ ] `api/app/Domain/Gacha/Services/GachaService.php` - 確定枠抽選メソッド拡張

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| GachaService | drawWithFixedPrizes() | 確定枠付き抽選処理 | 既存メソッドを拡張 |
| GachaService | filterPrizesByRarity() | レアリティフィルタリング | 新規メソッド |
| GachaService | drawFixedPrize() | 確定枠1回分の抽選 | 新規メソッド |

**抽選ロジック:**

```php
public function drawStepupGacha(OprGachaEntity $oprGacha, OprStepupGachaStepEntity $stepConfig, int $drawCount): GachaResultData
{
    $fixedPrizeCount = $stepConfig->getFixedPrizeCount();
    $normalDrawCount = $drawCount - $fixedPrizeCount;
    
    $results = [];
    
    // 1. 通常枠抽選（最初のN回）
    for ($i = 0; $i < $normalDrawCount; $i++) {
        $prize = $this->drawNormalPrize($oprGacha, $stepConfig);
        $results[] = $prize;
    }
    
    // 2. 確定枠抽選（最後のM回）
    for ($i = 0; $i < $fixedPrizeCount; $i++) {
        $prize = $this->drawFixedPrize($oprGacha, $stepConfig);
        $results[] = $prize;
    }
    
    return new GachaResultData($results);
}

private function drawFixedPrize(OprGachaEntity $oprGacha, OprStepupGachaStepEntity $stepConfig): GachaPrize
{
    // 確定枠賞品グループID取得（ステップ個別 or ガシャ共通）
    $prizeGroupId = $stepConfig->getFixedPrizeGroupId() ?? $oprGacha->getFixedPrizeGroupId();
    
    // 賞品リスト取得
    $prizes = $this->oprGachaPrizeRepository->getListByPrizeGroupId($prizeGroupId);
    
    // レアリティフィルタリング
    $filteredPrizes = $this->filterPrizesByRarity($prizes, $stepConfig->getFixedPrizeRarityThresholdType());
    
    // 重み付き抽選
    return $this->lotteryFactory->lottery($filteredPrizes);
}

private function filterPrizesByRarity(Collection $prizes, ?string $rarityThresholdType): Collection
{
    if ($rarityThresholdType === null) {
        return $prizes; // フィルタなし
    }
    
    return $prizes->filter(function ($prize) use ($rarityThresholdType) {
        // レアリティ条件を満たすアイテムのみ
        return $this->meetsRarityCondition($prize, $rarityThresholdType);
    });
}
```

##### 3.3.5 エラーハンドリング

**エラーコード定義:**

| エラーコード | エラー名 | 発生条件 | ユーザーへのメッセージ | 対処方法 |
|-------------|---------|---------|---------------------|---------|
| E_GACHA_012 | FIXED_PRIZE_NOT_AVAILABLE | 確定枠対象アイテムが存在しない | ガシャ設定に不備があります | マスタデータ検証 |

**エラーハンドリングの実装方針:**
- フィルタリング後にアイテムが0件の場合は例外をthrow
- マスタデータ設定時に事前チェック推奨

##### 3.3.6 実装上の注意事項

**既存実装との整合性:**
- 既存の確定枠ロジック（10連の最後1回確定）を拡張
- `GachaService::draw` の既存パターンを踏襲

---

### 3.4 レスポンス管理

#### 要件 RS-1: ガシャ実行レスポンスの拡張

##### 3.4.1 要件概要
- **要件ID:** REQ-RS-1
- **実現内容:** ガシャ実行APIのレスポンスにステップアップガシャ用の情報を追加する

##### 3.4.2 API設計

（REQ-GE-1のレスポンス構造を参照）

##### 3.4.3 ドメイン設計

**改修が必要な既存ファイル:**
- [ ] `api/app/Http/Responses/ResultData/GachaDrawResultData.php` - レスポンス構造拡張
- [ ] `api/app/Http/Responses/Factories/GachaResponseFactory.php` - usrGachaのarray生成時にstepupGachaを追加

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| GachaDrawResultData | toArray() | レスポンス配列生成 | currentStepNumber/loopCountフィールドを追加 |
| GachaResponseFactory | createUsrGachaData() | usrGacha配列生成 | ステップアップガシャ情報を含める |

**実装例:**

```php
class GachaResponseFactory
{
    public function createUsrGachaData(UsrGachaEntity $usrGacha, ?OprStepupGachaEntity $oprStepupGacha): array
    {
        $data = [
            'oprGachaId' => $usrGacha->getOprGachaId(),
            'count' => $usrGacha->getCount(),
            'playedAt' => StringUtil::convertToISO8601($usrGacha->getPlayedAt()),
            'currentStepNumber' => $usrGacha->getCurrentStepNumber(),
            'loopCount' => $usrGacha->getLoopCount(),
        ];
        
        return $data;
    }
}
```
```

##### 3.4.5 エラーハンドリング

N/A（レスポンス生成時のエラーは想定しない）

##### 3.4.6 実装上の注意事項

**既存実装との整合性:**
- 既存のレスポンス構造を維持し、`stepupGacha` フィールドを追加のみ
- ステップアップガシャ以外では `stepupGacha: null`

---

### 3.5 ガシャ履歴・賞品情報

#### 要件 HI-1: ガシャ履歴の管理

##### 3.5.1 要件概要
- **要件ID:** REQ-HI-1
- **実現内容:** ステップアップガシャの実行履歴を記録し、`/api/gacha/history` APIで返却する

##### 3.5.2 API設計

**新規API追加 / 既存API改修:**
- [x] 既存API改修

**対象エンドポイント:**
- エンドポイント: `/api/gacha/history`
- HTTPメソッド: POST
- 認証: 必要

##### 3.5.3 ドメイン設計

**ファイル構成:**

*新規作成が必要なファイル:*
*改修が必要な既存ファイル:*
- [ ] `api/app/Domain/Gacha/Models/LogGachaAction.php` - ステップアップガシャカラム追加
- [ ] `api/app/Domain/Gacha/UseCases/GachaHistoryUseCase.php` - ステップアップガシャ履歴の返却対応
- [ ] `api/app/Domain/Gacha/Services/GachaLogService.php` - ステップアップガシャログ記録メソッド追加

##### 3.5.4 DB設計

**テーブル構造追加 / テーブル構造変更:**
- [x] テーブル構造変更（既存テーブルへのカラム追加）

**既存テーブルへのカラム追加:**

*テーブル名:* `log_gacha_actions`

*ALTER TABLE文:*
```sql
ALTER TABLE log_gacha_actions
ADD COLUMN step_number TINYINT UNSIGNED NULL COMMENT '実行したステップ番号（1-10、ステップアップガシャのみ）',
ADD COLUMN loop_count INT UNSIGNED NULL COMMENT '実行時の周回数（0以上、ステップアップガシャのみ）';
```

*追加カラム説明:*
| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-----|------|-----------|------|
| step_number | TINYINT UNSIGNED | NULL | NULL | 実行したステップ番号（ステップアップガシャの場合のみNULL以外） |
| loop_count | INT UNSIGNED | NULL | NULL | 実行時の周回数（ステップアップガシャの場合のみNULL以外） |

##### 3.5.6 実装上の注意事項

**既存実装との整合性:**
- `log_gacha_actions` にステップアップガシャの履歴も記録（`step_number`, `loop_count` を含む）
- 既存のログ記録パターンを踏襲

---

## 4. 実装の優先順位と依存関係

### 4.1 実装の段階分け

**フェーズ1: 基盤実装（マスタデータ・ユーザーデータ管理）**
1. REQ-MD-1: ステップアップガシャ基本情報管理（テーブル、Entity、Repository作成）
2. REQ-MD-2: ステップごとの詳細設定管理（テーブル、Entity、Repository作成）
3. REQ-UD-1: ステップ進行状況の管理（テーブル、Entity、Repository作成）
4. `GachaType::STEPUP` の追加

**フェーズ2: コア機能実装（ガシャ実行処理）**
1. REQ-GE-1: ステップアップガシャの実行（UseCase、Service拡張）
2. REQ-GE-2: 確定枠抽選処理（Service拡張）
3. REQ-HI-1: ガシャ履歴の管理（ログテーブル、Repository作成）

**フェーズ3: レスポンス拡張・周辺機能**
1. REQ-RS-1: ガシャ実行レスポンスの拡張（ResponseFactory拡張）
2. `/api/game/update_and_fetch` のレスポンス拡張
3. `/api/gacha/history` のステップアップガシャ対応
4. `/api/gacha/prize` のステップアップガシャ対応（ステップごとの賞品情報返却）

### 4.2 依存関係マップ

```
REQ-MD-1 (opr_stepup_gachas)
  ↓
REQ-MD-2 (opr_stepup_gacha_steps) ← depends on REQ-MD-1
  ↓
REQ-UD-1 (usr_gachasカラム追加) ← depends on REQ-MD-1
  ↓
REQ-GE-1 (ガシャ実行処理) ← depends on REQ-MD-1, REQ-MD-2, REQ-UD-1
  ├→ REQ-GE-2 (確定枠抽選) ← depends on REQ-GE-1
  └→ REQ-HI-1 (履歴管理) ← depends on REQ-GE-1
     ↓
REQ-RS-1 (レスポンス拡張) ← depends on REQ-GE-1, REQ-UD-1
```

### 4.3 実装時の注意点

- フェーズ1を完了してからフェーズ2に進む（テーブル作成→ロジック実装の順）
- マイグレーションファイルは依存関係順に作成・実行
- テストは各フェーズごとに実施（ユニットテスト→機能テスト）
- 既存のガシャ機能に影響を与えないよう、ステップアップガシャ判定を厳密に実装

---

## 5. テスト設計概要

### 5.1 ユニットテスト

**テスト対象:**
- Domain層の各Service、Repository
- ビジネスロジックの正常系・異常系

**テストケース例:**

| テスト対象 | テストケース | 期待結果 |
|-----------|------------|---------|
| OprStepupGachaRepository::findByOprGachaId() | 存在するガシャID | OprStepupGachaEntity取得 |
| OprStepupGachaRepository::findByOprGachaId() | 存在しないガシャID | null |
| UsrGachaRepository::progressStep() | 最終ステップ以外 | current_step_numberインクリメント |
| UsrGachaRepository::progressStep() | 最終ステップ | current_step_number=1, loop_count++ |
| GachaService::drawStepupGacha() | 10連、確定枠3体 | 最後の3体が確定枠条件を満たす |
| GachaService::validateStepupGachaExecution() | コスト不一致 | 例外throw |
| GachaService::validateStepupGachaExecution() | 周回数上限到達 | 例外throw |

**テストファイル:**
- `api/tests/Unit/Domain/Gacha/Services/GachaServiceStepupTest.php`
- `api/tests/Unit/Domain/Gacha/Repositories/OprStepupGachaRepositoryTest.php`
- `api/tests/Unit/Domain/Gacha/Repositories/UsrGachaRepositoryTest.php` - progressStepメソッド追加テスト

### 5.2 機能テスト

**テスト対象:**
- APIエンドポイント
- リクエスト/レスポンスの検証
- データベースの状態変化

**テストケース例:**

| API | テストケース | 期待結果 |
|-----|------------|---------|
| POST /api/gacha/draw/diamond | ステップアップガシャ初回実行 | 200 OK、usr_gachasのcurrent_step_number=2に設定 |
| POST /api/gacha/draw/diamond | ステップアップガシャ最終ステップ実行 | 200 OK、current_step_number=1, loop_count++ |
| POST /api/gacha/draw/diamond | 周回数上限到達後の実行 | 400 Bad Request、エラーメッセージ |
| POST /api/gacha/draw/diamond | コスト不足 | 400 Bad Request、エラーメッセージ |
| POST /api/gacha/draw/diamond | コスト整合性不一致 | 400 Bad Request、エラーメッセージ |
| POST /api/game/update_and_fetch | ステップアップガシャ進行中 | 200 OK、usrGacha[].stepupGacha含む |
| POST /api/gacha/history | ステップアップガシャ実行後 | 200 OK、履歴にステップ情報含む |

**テストファイル:**
- `api/tests/Feature/Api/Gacha/DrawStepupGachaTest.php`
- `api/tests/Feature/Api/Game/UpdateAndFetchStepupGachaTest.php`

### 5.3 シナリオテスト

**テストシナリオ例:**

**シナリオ1: 初回〜完走〜2周目への移行**
1. ユーザーがステップ1のガシャを実行
2. `usr_gachas` の `current_step_number=2, loop_count=0` が設定される
3. ステップ2〜9を順次実行
4. ステップ10（最終ステップ）を実行
5. `current_step_number=1, loop_count=1` に更新
6. ステップ1を再度実行（2周目開始）
7. データベースに正しく記録される

**シナリオ2: 確定枠抽選の検証**
1. 10連ガシャ、確定枠3体（SSR以上確定）のステップを実行
2. 抽選結果の最後の3体がSSR以上であることを検証
3. 最初の7体は通常抽選（SR以下も含む）

**シナリオ3: 周回数上限到達**
1. max_loop_count=2 のガシャで全ステップを2周実行
2. 3周目のステップ1実行をリクエスト
3. エラー（E_GACHA_010）が返却される

---

## 6. マイグレーション計画

### 6.1 マイグレーションファイル一覧

**新規作成が必要なマイグレーション:**
- [ ] `YYYY_MM_DD_HHMMSS_add_stepup_to_gacha_type.php` - `opr_gachas.gacha_type` に 'StepUp' を追加
- [ ] `YYYY_MM_DD_HHMMSS_create_opr_stepup_gachas_table.php` - `opr_stepup_gachas` テーブル作成
- [ ] `YYYY_MM_DD_HHMMSS_create_opr_stepup_gacha_steps_table.php` - `opr_stepup_gacha_steps` テーブル作成
- [ ] `YYYY_MM_DD_HHMMSS_add_stepup_columns_to_usr_gachas_table.php` - `usr_gachas` へカラム追加
- [ ] `YYYY_MM_DD_HHMMSS_add_stepup_columns_to_log_gacha_actions_table.php` - `log_gacha_actions` へカラム追加

### 6.2 マイグレーション実行順序

1. `add_stepup_to_gacha_type` - 既存テーブルへのENUM追加
2. `create_opr_stepup_gachas_table` - 基盤テーブル作成（`opr_gachas` に依存）
3. `create_opr_stepup_gacha_steps_table` - ステップ設定テーブル作成（`opr_stepup_gachas` に依存）
4. `add_stepup_columns_to_usr_gachas_table` - `usr_gachas` へのカラム追加
5. `add_stepup_columns_to_log_gacha_actions_table` - `log_gacha_actions` へのカラム追加

### 6.3 ロールバック方針

- 各マイグレーションには `down()` メソッドを実装する
- ロールバック時は逆順で実行（`drop table`, `alter table` の取り消し）
- 外部キー制約により、親テーブルのロールバックは子テーブル削除後に実行

**ロールバック例:**

```php
public function down(): void
{
    // log_gacha_actionsからカラム削除
    Schema::table('log_gacha_actions', function (Blueprint $table) {
        $table->dropColumn(['step_number', 'loop_count']);
    });
    
    // usr_gachasからカラム削除
    Schema::table('usr_gachas', function (Blueprint $table) {
        $table->dropColumn(['current_step_number', 'loop_count']);
    });
    
    // opr_stepup_gacha_steps削除
    Schema::dropIfExists('opr_stepup_gacha_steps');
    
    // opr_stepup_gachas削除
    Schema::dropIfExists('opr_stepup_gachas');
    
    // gacha_typeのENUM戻し
    DB::statement("ALTER TABLE opr_gachas MODIFY COLUMN gacha_type ENUM('Normal', 'Tutorial') NOT NULL DEFAULT 'Normal'");
}
```

---

## 7. 連携が必要なスキル・ツール

**Claude Code スキル:**
- `api-request-validation`: リクエストパラメータのバリデーション実装（コスト整合性チェック）
- `domain-layer`: ドメインレイヤーの実装パターン参照（GachaService拡張）
- `api-schema-reference`: glow-schema YAMLとの整合性確認（レスポンス構造）
- `api-response`: レスポンス構造の実装（stepupGachaフィールド追加）
- `migration`: マイグレーションファイルの作成・実行
- `api-test-implementation`: テストコードの実装

**使用タイミング:**
- テーブル設計時: `migration`
- ドメインロジック実装時: `domain-layer`
- API実装時: `api-request-validation`, `api-response`, `api-schema-reference`
- テスト実装時: `api-test-implementation`

---

## 8. 実装時の判断が必要な事項

### 8.1 技術的な選択肢

**項目: リクエストパラメータのステップ番号整合性チェック**
- 選択肢A: クライアントからステップ番号を受け取り、サーバー側の状態と照合する
  - メリット: クライアントとサーバーの同期を確認できる
  - デメリット: 実装コストが増加、リクエストパラメータが増える
- 選択肢B: クライアントからステップ番号を受け取らず、サーバー側の状態のみを信頼する
  - メリット: シンプル、不正防止が容易
  - デメリット: クライアントとサーバーの同期ずれが起きた場合の検知が遅れる
- **推奨: 選択肢B**
  - 理由: 実装方針補足.txtで「リクエスト時のステップ数の整合性チェックをしない」と明記されている
  - サーバー側の状態（`usr_gachas.current_step_number`）を絶対的な真実として扱う

**項目: データクリーンアップの実装方式**
- 選択肢A: バッチ処理でガシャ期間終了後に一括削除
  - メリット: リアルタイム処理への影響なし
  - デメリット: バッチ実装・運用コストが増加
- 選択肢B: ガシャアクセス時に期限判定し、期限切れの場合は削除
  - メリット: バッチ不要、実装がシンプル
  - デメリット: アクセス時に削除処理が走る
- **推奨: 選択肢B**
  - 理由: 既存のガシャシステムと同じ方式を採用（アクセス時判定）
  - パフォーマンスへの影響は軽微

### 8.2 仕様の解釈

**項目: 周回数上限到達後のUI表示**
- 解釈A: ガシャ一覧に表示されるが、実行不可状態
  - この場合の実装: APIはエラーを返却、クライアント側で実行不可表示
- 解釈B: ガシャ一覧から非表示
  - この場合の実装: `update_and_fetch` で周回上限到達のガシャは返却しない
- **確認先: クライアント側の実装方針に従う**
  - サーバー側は周回上限到達時にエラーを返却する実装とし、UI表示はクライアント側で判断

---

## 9. 補足情報

### 9.1 参考にすべき既存実装

**類似機能の実装例:**
- `Domain\Gacha\Services\GachaService::draw()` - 既存のガシャ抽選処理
- `Domain\Gacha\UseCases\GachaDrawUseCase::exec()` - 既存のガシャ実行UseCase
- `Domain\Gacha\Repositories\UsrGachaRepository` - ユーザーガシャ情報の管理パターン
- `Domain\Gacha\Services\GachaCacheService` - ガシャキャッシュ管理（ステップアップガシャでは不要だが参考になる）

**参考になるドメイン設計:**
- `Domain\Gacha` - 既存のガシャドメイン全体の構造
- `Domain\Resource\Mst\Repositories\OprGachaRepository` - マスタデータ取得パターン
- `Domain\Resource\Usr\Repositories\UsrUnitRepository` - ユーザーデータ管理パターン

**参考になるDB設計:**
- `opr_gachas` - オペレーションマスタテーブル
- `opr_gacha_prizes` - 賞品管理テーブル
- `usr_gachas` - ユーザーガシャ情報テーブル
- `log_gacha_actions` - ガシャ実行ログテーブル

### 9.2 参考ドキュメント

- **APIコーディング規約**: @docs/01_project/coding-standards/api-coding-standards.md
  - 命名規則: DB接頭辞付き変数（`$opr*`, `$usr*`, `$log*`）
  - アーキテクチャパターン: Delegator、UseCase、Service、Repository、Entity
  - return array禁止ルール（Delegatorの戻り値を除く）
  - Domain EntityとEloquent Modelの使い分け

- **マスタデータ配信機構**: @docs/01_project/architecture/マスタデータ配信機構.md
  - マスタデータはS3経由でクライアントに配信
  - `game/version` APIでS3パスとハッシュ値を返却
  - サーバーAPIではマスタデータの参照のみ行う
  - マスタデータの更新は管理ツール（admin）経由

- **glow-schema**: @glow-schema/Schema/Gacha.yml
  - ガシャAPIのリクエスト/レスポンス定義
  - `usrGacha` の既存構造を確認し、`stepupGacha` フィールドを追加

### 9.3 実装時のTips

**トランザクション制御:**
- ガシャ実行処理は必ずトランザクション内で完結させる
- `DB::transaction()` を使用し、例外発生時は自動ロールバック
- トランザクション時間を最小化するため、事前に取得可能なデータは先に取得

**N+1問題の回避:**
- ステップ設定は `getListByOprStepupGachaId()` で一括取得
- 賞品情報は `getListByPrizeGroupId()` で一括取得
- Eager Loadingを活用（`with()` メソッド）

**エラーハンドリング:**
- バリデーションエラーは早期にthrow（コスト消費前）
- ユーザー向けエラーメッセージは分かりやすく
- ログには詳細な情報を記録（デバッグ用）

**パフォーマンスチューニング:**
- INDEXを適切に設定（PRIMARY KEY、外部キー、検索条件）
- 不要なSELECT *を避け、必要なカラムのみ取得
- キャッシュは同一リクエスト内でのみ使用（トランザクション整合性のため）

**セキュリティ:**
- コスト整合性チェックは必須（チート対策）
- サーバー側の状態を絶対的な真実として扱う
- クライアントからの入力値は常に疑う

---

## 10. 実装開始の準備状況

### 10.1 実装可能な要件

すべての要件（REQ-MD-1 〜 REQ-HI-1）について、実装に必要な情報が揃っています：

- [x] テーブル設計完了
- [x] Entity/Model設計完了
- [x] Repository設計完了
- [x] Service/UseCase拡張方針確定
- [x] API設計（既存API拡張）確定
- [x] レスポンス構造設計完了
- [x] エラーハンドリング設計完了

### 10.2 実装順序の推奨

1. **マイグレーションファイル作成・実行**（フェーズ1）
   - `opr_gachas.gacha_type` に 'StepUp' 追加
   - `opr_stepup_gachas`, `opr_stepup_gacha_steps` 作成
   - `usr_gachas` にカラム追加（`current_step_number`, `loop_count`）
   - `log_gacha_actions` にカラム追加（`step_number`, `loop_count`）

2. **Enum追加**
   - `GachaType::STEPUP = 'StepUp'` 追加

3. **Model/Entity/Repository拡張**（フェーズ1）
   - `OprStepupGacha`, `OprStepupGachaStep` の作成
   - `UsrGacha`, `LogGachaAction` の拡張（カラム追加対応）
   - 対応するEntity、Repository作成・拡張

4. **Service拡張**（フェーズ2）
   - `GachaService::drawStepupGacha()` 実装
   - `GachaService::progressStepupGachaStep()` 実装
   - `GachaService::validateStepupGachaExecution()` 実装
   - `GachaService::filterPrizesByRarity()` 実装

5. **UseCase拡張**（フェーズ2）
   - `GachaDrawUseCase::exec()` にステップアップガシャ判定と処理分岐追加
   - `GachaHistoryUseCase::exec()` にステップアップガシャ履歴対応追加

6. **Response拡張**（フェーズ3）
   - `GachaResponseFactory::createUsrGachaData()` に `stepupGacha` フィールド追加
   - `UpdateAndFetchResultData` のレスポンス拡張

7. **テスト実装**
   - ユニットテスト（Repository、Service）
   - 機能テスト（API）
   - シナリオテスト

### 10.3 リスク管理

**高リスク項目:**
- 確定枠抽選ロジックの実装（既存ロジック拡張が必要）
  - 対策: 既存の10連確定ロジックを参考に慎重に実装、十分なテストケースを用意

**中リスク項目:**
- トランザクション制御（複数テーブルへの同時書き込み）
  - 対策: 既存のガシャ実行処理のトランザクションパターンを踏襲

**低リスク項目:**
- マイグレーション実行
  - 対策: 開発環境で十分にテスト後、本番環境に適用

---

**実装開始判断: 準備完了**

すべての設計が完了し、実装に必要な情報が揃っています。フェーズ1のマイグレーションファイル作成から実装を開始できます。


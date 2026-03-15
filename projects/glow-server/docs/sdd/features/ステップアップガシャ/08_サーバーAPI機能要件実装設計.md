# サーバーAPI機能要件実装設計

## 1. ドキュメント情報
- 対象機能: ステップアップガシャ
- 作成日: 2025-12-16
- 最終更新日: 2026-02-24
- 更新履歴:
  - 2025-12-16: 初版作成
  - 2026-01-07: おまけ報酬機能追加、loop_count仕様変更（1始まりに変更）
  - 2026-02-24: 実装完了に伴い、実装内容に基づきドキュメント全面改訂
- 参照ドキュメント:
  - 05_サーバーAPI要件書.md
  - 04_ゲーム体験仕様確認結果まとめ.md
  - 実装方針補足.txt
  - APIコーディング規約: @docs/01_project/coding-standards/api-coding-standards.md
  - マスタデータ配信機構: @docs/01_project/architecture/マスタデータ配信機構.md

## 2. 実装設計概要

### 2.1 実装方針

本機能は、既存のガシャシステムを拡張し、ステップアップガシャを実現します。

**基本方針:**
- **新規APIエンドポイントは追加しない** - 既存の `/api/gacha/draw/*` APIを拡張
- **新規Domainは追加しない** - 既存の `Domain\Gacha` を拡張
- **Factoryパターンによる分岐** - `GachaServiceFactory` でガシャタイプに応じた `DrawService` を選択（`StepupGachaDrawService` / `StandardGachaDrawService`）
- **専用Serviceクラスの分離** - ステップアップ固有ロジックは `StepupGachaService` に集約し、既存の `GachaService` への影響を最小化
- **レスポンス構造の拡張** - `usrGacha` 内に `currentStepNumber`, `loopCount` フィールドを直接追加（ステップアップガシャ以外ではnull）
- **既存パターンの踏襲** - GachaService、GachaRepository等の既存実装パターンを維持
- **トランザクション制御** - 既存のガシャと同様のトランザクション管理

**実装方針補足.txtの重要ポイント:**
- リクエスト時のステップ数はクライアントから `currentStepNumber` パラメータで送信され、サーバー側の `usr_gachas` の状態と照合（不一致時はエラー）
- `opr_gachas` の `prize_group_id`, `fixed_prize_group_id` をステップ共通IDとし、`opr_stepup_gacha_steps` で個別設定がない場合はこれを参照
- `/api/gacha/draw/ad` はステップアップガシャに対応しない
- ステップアップガシャでは天井（Upper）機能は対象外

### 2.2 実装の全体像

#### 新規追加されるコンポーネント

**テーブル:**
- `opr_stepup_gachas` - ステップアップガシャの基本情報（最大ステップ数、最大周回数）
- `opr_stepup_gacha_steps` - ステップごとの詳細設定（コスト、排出回数、確定枠条件）
- `opr_stepup_gacha_step_rewards` - ステップごとのおまけ報酬設定（追加報酬、周回数制御）
- `opr_stepup_gacha_steps_i18n` - ステップ設定の多言語対応（確定枠表示文言）
- `usr_gachas` - カラム追加: `current_step_number`, `loop_count`（ステップ進行状況）
- `log_gacha_actions` - カラム追加: `step_number`, `loop_count`（ステップ実行履歴）

**Enum:**
- `GachaType::STEPUP = 'Stepup'` - `opr_gachas.gacha_type` に追加

**Entity:**
- `OprStepupGachaEntity` - マスタデータEntity
- `OprStepupGachaStepEntity` - ステップ設定Entity
- `OprStepupGachaStepRewardEntity` - おまけ報酬設定Entity
- `StepupGachaState` - ステップアップガシャの初期化・バリデーション結果を保持するreadonly値オブジェクト
- `StepupGachaStepReward` - おまけ報酬の報酬Entity（`BaseReward`継承）

**Model:**
- `OprStepupGacha` - Eloquent Model
- `OprStepupGachaStep` - Eloquent Model
- `OprStepupGachaStepReward` - Eloquent Model
- `UsrGacha` - Eloquent Model（拡張: `current_step_number`, `loop_count` カラム追加、`UsrGachaInterface` に getter/setter追加）
- `LogGachaAction` - Eloquent Model（拡張: `step_number`, `loop_count` カラム追加、`ILogGachaAction` に setter追加）

**Repository:**
- `OprStepupGachaRepository` - マスタデータ取得
- `OprStepupGachaStepRepository` - ステップ設定取得
- `OprStepupGachaStepRewardRepository` - おまけ報酬取得

**Service:**
- `StepupGachaService` - ステップアップガシャ固有のドメインロジック（新規）
- `StepupGachaDrawService` - ステップアップガシャ専用の抽選処理（`GachaDrawService` を継承、新規）

**Factory:**
- `GachaServiceFactory` - ガシャタイプに応じた `DrawService` を返すファクトリ（新規）

#### 既存コンポーネントへの影響範囲

**UseCase拡張:**
- `GachaDrawUseCase::exec()` - `currentStepNumber` パラメータ追加、`GachaServiceFactory` による分岐、`stepRewards` のハンドリング追加
- `GachaPrizeUseCase::exec()` - `StepupGachaService` をDIし、ステップ別排出率情報を返却
- GachaHistoryUseCase - 変更なし（`GachaHistory` オブジェクト内にstep情報が含まれるため）

**Controller拡張:**
- `GachaController` - `drawFree`, `drawItem`, `drawDiamond`, `drawPaidDiamond` に `currentStepNumber` バリデーションとパラメータ追加（`drawAd` は対象外）

**Response拡張:**
- `GachaDrawResultData` - `stepRewards` フィールド追加
- `GachaPrizeResultData` - `stepupGachaPrizes` フィールド追加
- `StepupGachaPrizeInfoData` - ステップ別排出率情報のレスポンスデータ（新規）
- `ResponseDataFactory` - `addUsrGachaData()` に `currentStepNumber`, `loopCount` 追加、`addStepRewardsData()` メソッド追加
- `GachaResponseFactory` - `createDrawResponse()` に `addStepRewardsData()` 追加、`createPrizeResponse()` にステップ別排出率追加

**Service拡張:**
- `GachaService` - `addGachaHistory()` に `stepNumber`, `loopCount`, `stepRewards` パラメータ追加
- `GachaLogService` - `sendGachaLog()` に `stepNumber` パラメータ追加

**Entity拡張:**
- `GachaDrawRequest` - `currentStepNumber` プロパティ追加
- `GachaDrawResult` - `currentStepNumber`, `loopCount` プロパティ追加
- `GachaHistory` - `stepNumber`, `loopCount`, `stepRewards` プロパティ追加、`formatToResponse()` に `stepupInfo`, `stepRewards` を追加

**Schema拡張:**
- `glow-schema/Schema/Gacha.yml` - レスポンス定義の拡張

### 2.3 アーキテクチャ上の考慮点

#### Domain層の責務分離

既存のGachaDomainを拡張する形で実装し、以下の責務分離を維持します：

- **Delegator**: Controllerからの呼び出し窓口（戻り値は必ずarray）
- **UseCase**: ビジネスロジックの実行（トランザクション制御）
- **DrawService**: ガシャ抽選フロー制御（`GachaDrawService` を継承した `StepupGachaDrawService`）
- **StepupGachaService**: ステップアップ固有のドメインロジック（初期化・バリデーション・ステップ進行・コスト検証）
- **GachaService**: 共通ドメインロジック（コスト消費・抽選実行・報酬作成等）
- **Factory**: `GachaServiceFactory` でガシャタイプに応じた DrawService を選択
- **Repository**: データアクセス層（CRUD操作）
- **Entity**: ドメインデータ構造（Eloquent Modelとの変換）

#### ガシャタイプによる分岐（Factoryパターン）

```php
// GachaServiceFactory
return match ($gachaType) {
    GachaType::STEPUP->value => app()->make(StepupGachaDrawService::class),
    default => app()->make(StandardGachaDrawService::class),
};
```

UseCase側ではif-else分岐ではなく、Factoryパターンによるポリモーフィズムで処理を委譲します。

#### トランザクション設計

ステップアップガシャの実行は、以下の処理を1トランザクションで完結させます：

1. ステップ進行状況の取得と検証（`initializeAndValidate`）
2. コスト検証（`validateCost`）
3. 抽選処理（通常枠 + 確定枠）
4. ガシャ報酬配布（`rewardDelegator`）
5. おまけ報酬の追加（`addStepRewards`、ステップ進行前に実行）
6. ステップ進行（`progressStep` - `current_step_number` インクリメント、周回処理）
7. ミッショントリガー送信
8. ガシャ履歴保存（`addGachaHistory` - `step_number`, `loop_count`, `stepRewards` を含めて記録）
9. ユーザーデータ保存（`saveUsr`）
10. ログ送信（`sendGachaLog`）

#### 確定枠抽選ロジック

既存の10連ガシャの確定枠ロジック（最後の1回確定）を拡張し、以下を実現：

- 任意の確定枠数（0〜10）に対応
- レアリティ条件付き抽選（SR以上、SSR以上等）
- 確定枠は常に最後のN回
- 確定枠賞品グループIDはステップ設定→ガシャ設定の優先順位で取得

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

*新規作成ファイル:*
- [x] `api/app/Domain/Resource/Mst/Models/OprStepupGacha.php` - Eloquent Model
- [x] `api/app/Domain/Resource/Mst/Entities/OprStepupGachaEntity.php` - Entity
- [x] `api/app/Domain/Resource/Mst/Repositories/OprStepupGachaRepository.php` - Repository

*改修した既存ファイル:*
- [x] `api/app/Domain/Gacha/Enums/GachaType.php` - `STEPUP = 'Stepup'` を追加

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| OprStepupGacha | toEntity() | Entity変換 | OprStepupGachaEntity を返す |
| OprStepupGachaEntity | toArray() | Entity変換 | Domain境界越えで使用 |
| OprStepupGachaRepository | findByOprGachaId() | ガシャID指定で取得 | 見つからない場合null |
| OprStepupGachaRepository | getByOprGachaId() | ガシャID指定で取得 | isThrowError=trueで見つからない場合例外 |

**Entity設計:**
- Entityタイプ: Plain Entity（マスタデータなのでキャッシュ不要）
- 主要プロパティ:
  - `id`: string - ID（PRIMARY KEY）
  - `releaseKey`: int - リリースキー
  - `oprGachaId`: string - 親ガシャID（`opr_gachas` への外部キー）
  - `maxStepNumber`: int - 最大ステップ数（1〜10）
  - `maxLoopCount`: ?int - 最大周回数（NULLの場合は無限周回）
- 既存Entityとの関係: `OprGachaEntity` から参照される

##### 3.1.4 DB設計

**テーブル構造追加 / テーブル構造変更:**
- [x] テーブル構造追加（新規テーブル作成）
- [x] テーブル構造変更（`opr_gachas.gacha_type` に `'Stepup'` を追加）

**新規テーブル作成の場合:**

*テーブル名:* `opr_stepup_gachas`

*CREATE TABLE文:*
```sql
CREATE TABLE opr_stepup_gachas (
    id VARCHAR(255) NOT NULL COMMENT 'ID',
    release_key BIGINT NOT NULL DEFAULT 1 COMMENT 'リリースキー',
    opr_gacha_id VARCHAR(255) NOT NULL COMMENT 'opr_gachas.id（外部キー）',
    max_step_number TINYINT UNSIGNED NOT NULL COMMENT '最大ステップ数（1-10）',
    max_loop_count INT UNSIGNED NULL COMMENT '最大周回数（NULLの場合は無限周回）',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '作成日時',
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新日時',
    PRIMARY KEY (id),
    UNIQUE KEY opr_gacha_id_unique (opr_gacha_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ステップアップガシャ設定';
```

*カラム説明:*
| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-----|------|-----------|------|
| id | VARCHAR(255) | NOT NULL | - | ID（PRIMARY KEY） |
| release_key | BIGINT | NOT NULL | 1 | リリースキー |
| opr_gacha_id | VARCHAR(255) | NOT NULL | - | opr_gachas.id（外部キー、UNIQUE） |
| max_step_number | TINYINT UNSIGNED | NOT NULL | - | 最大ステップ数（1〜10） |
| max_loop_count | INT UNSIGNED | NULL | - | 最大周回数（NULLの場合は無限周回） |
| created_at | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | 作成日時 |
| updated_at | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | 更新日時 |

**既存テーブル変更の場合:**

*テーブル名:* `opr_gachas`

*ALTER TABLE文:*
```sql
-- gacha_typeカラムのENUM定義に 'Stepup' を追加
-- 初回マイグレーションでは 'StepUp' で追加後、2026_02_16のマイグレーションで 'Stepup' に統一
ALTER TABLE opr_gachas
MODIFY COLUMN gacha_type ENUM('Normal', 'Premium', 'Pickup', 'Free', 'Ticket', 'Festival', 'PaidOnly', 'Medal', 'Tutorial', 'Stepup') NOT NULL DEFAULT 'Normal' COMMENT 'ガシャタイプ';
```

*変更内容説明:*
- カラム変更: `gacha_type` - ステップアップガシャタイプ `'Stepup'` を追加

##### 3.1.5 エラーハンドリング

**エラーコード定義:**

| エラーコード | エラー名 | 発生条件 | ユーザーへのメッセージ | 対処方法 |
|-------------|---------|---------|---------------------|---------|
| E_GACHA_001 | STEPUP_GACHA_NOT_FOUND | ステップアップガシャマスタデータが存在しない | ガシャ情報が見つかりません | `opr_stepup_gachas` の存在チェック |
| E_GACHA_002 | INVALID_STEP_NUMBER | `max_step_number` が範囲外（1〜10以外） | ガシャ設定に不備があります | マスタデータのバリデーション |

**エラーハンドリングの実装方針:**
- Repository層で見つからない場合は `null` を返す（`find*` メソッド）または `isThrowError=true` 時に例外をthrow（`get*` メソッド）
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

*新規作成ファイル:*
- [x] `api/app/Domain/Resource/Mst/Models/OprStepupGachaStep.php` - Eloquent Model
- [x] `api/app/Domain/Resource/Mst/Entities/OprStepupGachaStepEntity.php` - Entity
- [x] `api/app/Domain/Resource/Mst/Repositories/OprStepupGachaStepRepository.php` - Repository

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| OprStepupGachaStep | toEntity() | Entity変換 | OprStepupGachaStepEntity を返す |
| OprStepupGachaStepEntity | toArray() | Entity変換 | Domain境界越えで使用 |
| OprStepupGachaStepRepository | getListByOprGachaId() | ステップ一覧取得 | Collection&lt;OprStepupGachaStepEntity&gt;、stepNumber昇順ソート |
| OprStepupGachaStepRepository | findByOprGachaIdStepNumber() | ガシャID＋ステップ番号指定で取得 | 見つからない場合null |
| OprStepupGachaStepRepository | getByOprGachaIdStepNumber() | ガシャID＋ステップ番号指定で取得 | isThrowError=trueで見つからない場合例外 |

**Entity設計:**
- Entityタイプ: Plain Entity
- 主要プロパティ:
  - `id`: string - ID（PRIMARY KEY）
  - `releaseKey`: int - リリースキー
  - `oprGachaId`: string - ガシャID（`opr_gachas.id`）
  - `stepNumber`: int - ステップ番号（1〜10）
  - `costType`: CostType(enum) - コストの種類（Diamond, PaidDiamond, Free, Item）
  - `costId`: ?string - コストのID（Itemの場合にアイテムID）
  - `costNum`: int - コストの数
  - `drawCount`: int - 排出回数（1, 10等）
  - `fixedPrizeCount`: int - 確定枠数（0〜10）
  - `fixedPrizeRarityThresholdType`: ?RarityType(enum) - 確定枠レアリティ条件（SR以上、SSR以上等）
  - `prizeGroupId`: ?string - 賞品グループID（NULLの場合は親の `opr_gachas.prize_group_id` を参照）
  - `fixedPrizeGroupId`: ?string - 確定枠賞品グループID（NULLの場合は親の `opr_gachas.fixed_prize_group_id` を参照）
  - `isFirstFree`: bool - 初回のみ無料フラグ
- 既存Entityとの関係: `OprStepupGachaEntity` から参照される

##### 3.1.4 DB設計

**テーブル構造追加 / テーブル構造変更:**
- [x] テーブル構造追加（新規テーブル作成）

**新規テーブル作成の場合:**

*テーブル名:* `opr_stepup_gacha_steps`

*CREATE TABLE文:*
```sql
CREATE TABLE opr_stepup_gacha_steps (
    id VARCHAR(255) NOT NULL COMMENT 'ID',
    release_key BIGINT NOT NULL DEFAULT 1 COMMENT 'リリースキー',
    opr_gacha_id VARCHAR(255) NOT NULL COMMENT 'opr_gachas.id（外部キー）',
    step_number TINYINT UNSIGNED NOT NULL COMMENT 'ステップ番号（1-10）',
    cost_type ENUM('Diamond','PaidDiamond','Free','Item') NOT NULL COMMENT 'コスト種別',
    cost_id VARCHAR(255) NULL COMMENT 'コストID（Itemの場合にアイテムID）',
    cost_num INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'コスト数',
    draw_count TINYINT UNSIGNED NOT NULL COMMENT '排出回数（何連ガシャか）',
    fixed_prize_count TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '確定枠数（0-10）',
    fixed_prize_rarity_threshold_type ENUM('N','R','SR','SSR','UR') NULL COMMENT '確定枠レアリティ条件',
    prize_group_id VARCHAR(255) NULL COMMENT '賞品グループID（NULLの場合は親のopr_gachas.prize_group_idを参照）',
    fixed_prize_group_id VARCHAR(255) NULL COMMENT '確定枠賞品グループID（NULLの場合は親のopr_gachas.fixed_prize_group_idを参照）',
    is_first_free BOOLEAN NOT NULL DEFAULT FALSE COMMENT '初回のみ無料フラグ',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '作成日時',
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新日時',
    PRIMARY KEY (id),
    UNIQUE KEY opr_gacha_id_step_number_unique (opr_gacha_id, step_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ステップアップガシャのステップごと設定';
```

*カラム説明:*
| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-----|------|-----------|------|
| id | VARCHAR(255) | NOT NULL | - | ID（PRIMARY KEY） |
| release_key | BIGINT | NOT NULL | 1 | リリースキー |
| opr_gacha_id | VARCHAR(255) | NOT NULL | - | opr_gachas.id（外部キー） |
| step_number | TINYINT UNSIGNED | NOT NULL | - | ステップ番号（1〜10） |
| cost_type | ENUM('Diamond','PaidDiamond','Free','Item') | NOT NULL | - | コスト種別 |
| cost_id | VARCHAR(255) | NULL | - | コストID（Itemの場合にアイテムID） |
| cost_num | INT UNSIGNED | NOT NULL | 0 | コスト数 |
| draw_count | TINYINT UNSIGNED | NOT NULL | - | 排出回数（1連、10連等） |
| fixed_prize_count | TINYINT UNSIGNED | NOT NULL | 0 | 確定枠数（0〜10） |
| fixed_prize_rarity_threshold_type | ENUM('N','R','SR','SSR','UR') | NULL | - | 確定枠レアリティ条件 |
| prize_group_id | VARCHAR(255) | NULL | - | 賞品グループID（NULLの場合は親のopr_gachas.prize_group_idを使用） |
| fixed_prize_group_id | VARCHAR(255) | NULL | - | 確定枠賞品グループID（NULLの場合は親のopr_gachas.fixed_prize_group_idを使用） |
| is_first_free | BOOLEAN | NOT NULL | FALSE | 初回のみ無料フラグ（FALSE: 周回時も同じコスト、TRUE: 初回のみ無料） |
| created_at | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | 作成日時 |
| updated_at | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | 更新日時 |

**多言語対応テーブル:**

*テーブル名:* `opr_stepup_gacha_steps_i18n`

*CREATE TABLE文:*
```sql
CREATE TABLE opr_stepup_gacha_steps_i18n (
    id VARCHAR(255) NOT NULL COMMENT 'ID',
    release_key BIGINT NOT NULL DEFAULT 1 COMMENT 'リリースキー',
    opr_stepup_gacha_step_id VARCHAR(255) NOT NULL COMMENT 'opr_stepup_gacha_steps.id（外部参照）',
    language ENUM('ja') NOT NULL COMMENT '言語情報',
    fixed_prize_description VARCHAR(255) NOT NULL COMMENT '確定枠の表示文言（排出物テキスト）',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '作成日時',
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新日時',
    PRIMARY KEY (id),
    UNIQUE KEY step_language_unique (opr_stepup_gacha_step_id, language)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ステップアップガシャステップ設定の多言語対応';
```

*カラム説明:*
| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-----|------|-----------|------|
| id | VARCHAR(255) | NOT NULL | - | ID（PRIMARY KEY） |
| release_key | BIGINT | NOT NULL | 1 | リリースキー |
| opr_stepup_gacha_step_id | VARCHAR(255) | NOT NULL | - | opr_stepup_gacha_steps.id |
| language | ENUM('ja') | NOT NULL | - | 言語情報 |
| fixed_prize_description | VARCHAR(255) | NOT NULL | - | 確定枠の表示文言 |
| created_at | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | 作成日時 |
| updated_at | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | 更新日時 |

##### 3.1.5 エラーハンドリング

**エラーコード定義:**

| エラーコード | エラー名 | 発生条件 | ユーザーへのメッセージ | 対処方法 |
|-------------|---------|---------|---------------------|---------|
| E_GACHA_003 | STEPUP_STEP_CONFIG_NOT_FOUND | ステップ設定が存在しない | ガシャ設定に不備があります | `opr_stepup_gacha_steps` の存在チェック |
| E_GACHA_004 | INVALID_DRAW_COUNT | `draw_count` が不正（0以下） | ガシャ設定に不備があります | マスタデータのバリデーション |
| E_GACHA_005 | INVALID_FIXED_PRIZE_COUNT | `fixed_prize_count` が `draw_count` を超える | ガシャ設定に不備があります | マスタデータのバリデーション |

**エラーハンドリングの実装方針:**
- Repository層で設定が見つからない場合は `isThrowError=true` で例外をthrow
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
- `CostType` enumを活用（Diamond, PaidDiamond, Free, Item, Ad）

**マスタデータに関する考慮点:**
- ステップ共通の賞品グループIDは `opr_gachas` で管理
- ステップ個別の設定は `opr_stepup_gacha_steps` で管理
- NULLの場合は親を参照する仕組み

---

#### 要件 MD-3: ステップごとのおまけ報酬管理

##### 3.1.7 要件概要
- **要件ID:** REQ-MD-3
- **実現内容:** 各ステップのおまけ報酬（ガシャ結果とは別に付与される追加報酬）をマスタデータで管理する

##### 3.1.8 API設計

**新規API追加 / 既存API改修:**
- [x] 既存API改修（マスタデータ取得は内部処理のみ）

**対象エンドポイント:**
- N/A（マスタデータはS3経由でクライアントに配信）

##### 3.1.9 ドメイン設計

**新規ドメイン追加 / 既存ドメイン改修:**
- [x] 既存ドメイン改修

**対象ドメイン:**
- ドメイン分類: Game
- ドメイン名: `Domain\Gacha`

**ファイル構成:**

*新規作成ファイル:*
- [x] `api/app/Domain/Resource/Mst/Models/OprStepupGachaStepReward.php` - Eloquent Model
- [x] `api/app/Domain/Resource/Mst/Entities/OprStepupGachaStepRewardEntity.php` - Entity
- [x] `api/app/Domain/Resource/Mst/Repositories/OprStepupGachaStepRewardRepository.php` - Repository
- [x] `api/app/Domain/Resource/Entities/Rewards/StepupGachaStepReward.php` - 報酬Entity（BaseReward継承）

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| OprStepupGachaStepReward | toEntity() | Entity変換 | OprStepupGachaStepRewardEntity を返す |
| OprStepupGachaStepRewardEntity | toArray() | Entity変換 | Domain境界越えで使用 |
| OprStepupGachaStepRewardRepository | getRewardsForStep() | ステップ・周回数に応じたおまけ報酬取得 | stepNumber一致 AND (loopCountTarget IS NULL OR loopCountTarget=loopCount) |
| StepupGachaStepReward | getRewardResponseData() | レスポンス用報酬データ返却 | BaseRewardの実装を使用、トリガーソースはGACHA_REWARD |

**Entity設計:**
- Entityタイプ: Plain Entity
- 主要プロパティ（OprStepupGachaStepRewardEntity）:
  - `id`: string - ID（PRIMARY KEY）
  - `releaseKey`: int - リリースキー
  - `oprGachaId`: string - ガシャID
  - `stepNumber`: int - ステップ番号（1〜10）
  - `loopCountTarget`: ?int - 対象周回数（NULL=全周回、1=1週目のみ、2=2週目のみ、0は無視）
  - `resourceType`: string - リソースタイプ（Exp, Coin, FreeDiamond, Item, Emblem, Unit）
  - `resourceId`: ?string - リソースID（ItemやUnitの場合は該当ID、Coin等の場合はNULL）
  - `resourceAmount`: int - 報酬数量
- 既存Entityとの関係: `OprStepupGachaEntity` から参照される

- StepupGachaStepRewardは `BaseReward` を継承し、`LogResourceTriggerSource::GACHA_REWARD` をトリガーソースとして使用

##### 3.1.10 DB設計

**テーブル構造追加 / テーブル構造変更:**
- [x] テーブル構造追加（新規テーブル作成）

**新規テーブル作成の場合:**

*テーブル名:* `opr_stepup_gacha_step_rewards`

*CREATE TABLE文:*
```sql
CREATE TABLE opr_stepup_gacha_step_rewards (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主キー',
    release_key BIGINT NOT NULL DEFAULT 1 COMMENT 'リリースキー',
    opr_gacha_id VARCHAR(255) NOT NULL COMMENT 'ガシャID（opr_gachas.id）',
    step_number TINYINT UNSIGNED NOT NULL COMMENT 'ステップ番号（1-10）',
    loop_count_target INT UNSIGNED NULL COMMENT '対象周回数（NULL=全周回、1=1週目、2=2週目、0は無視）',
    resource_type ENUM('Exp','Coin','FreeDiamond','Item','Emblem','Unit') NOT NULL COMMENT 'リソースタイプ',
    resource_id VARCHAR(255) NULL COMMENT 'リソースID（Itemの場合はopr_items.id、Unitの場合はopr_cards.id等、CoinやFreeDiamond等の場合はNULL）',
    resource_amount BIGINT UNSIGNED NOT NULL COMMENT '報酬数量',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '作成日時',
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新日時',
    PRIMARY KEY (id),
    INDEX idx_opr_gacha_id_step (opr_gacha_id, step_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ステップアップガシャのステップごとおまけ報酬';
```

*カラム説明:*
| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-----|------|-----------|------|
| id | BIGINT UNSIGNED | NOT NULL | AUTO_INCREMENT | 主キー |
| release_key | BIGINT | NOT NULL | 1 | リリースキー |
| opr_gacha_id | VARCHAR(255) | NOT NULL | - | ガシャID（opr_gachas.id） |
| step_number | TINYINT UNSIGNED | NOT NULL | - | ステップ番号（1〜10） |
| loop_count_target | INT UNSIGNED | NULL | - | 対象周回数（NULL=全周回、1=1週目、2=2週目、0は設定非推奨で無視） |
| resource_type | ENUM('Exp','Coin','FreeDiamond','Item','Emblem','Unit') | NOT NULL | - | リソースタイプ |
| resource_id | VARCHAR(255) | NULL | - | リソースID（ItemやUnitの場合は該当ID、CoinやFreeDiamond等の場合はNULL） |
| resource_amount | BIGINT UNSIGNED | NOT NULL | - | 報酬数量 |
| created_at | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | 作成日時 |
| updated_at | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | 更新日時 |

*loop_count_targetの仕様:*
- `NULL`: 全周回で付与される（周回数に関係なく毎回）
- `1`: 1週目（loop_count=1）のみ付与
- `2`: 2週目（loop_count=2）のみ付与
- `0`: どの周回でも一致しない（無視される、設定非推奨）

##### 3.1.11 エラーハンドリング

**エラーコード定義:**

| エラーコード | エラー名 | 発生条件 | ユーザーへのメッセージ | 対処方法 |
|-------------|---------|---------|---------------------|---------|
| E_GACHA_020 | STEP_REWARD_RESOURCE_INVALID | おまけ報酬のリソースが不正 | 報酬の付与に失敗しました | マスタデータのresource_type/resource_id検証 |

**エラーハンドリングの実装方針:**
- RewardDelegator経由で付与するため、既存の報酬付与エラーハンドリングを活用
- おまけ報酬付与失敗時はトランザクション全体をロールバック

##### 3.1.12 実装上の注意事項

**パフォーマンス考慮点:**
- ガシャ実行時に該当ステップ・周回数のおまけ報酬をクエリで取得
- 1つのステップに複数のおまけ報酬を設定可能（すべて付与）

**セキュリティ考慮点:**
- おまけ報酬はコンプガチャ規制に抵触しないよう、ガシャ抽選結果とは独立した報酬

**データ整合性:**
- `opr_gacha_id` と `step_number` の組み合わせが有効であること
- `resource_type` と `resource_id` が有効なリソースを指していること

**既存実装との整合性:**
- RewardDelegatorを使用して報酬付与（既存の報酬付与パターンと統一）

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
        "adPlayedAt": null,
        "playedAt": "2025-12-16T10:00:00Z",
        "adCount": 0,
        "adDailyCount": 0,
        "count": 5,
        "dailyCount": 1,
        "currentStepNumber": 3,
        "loopCount": 0,
        "expiresAt": "2026-01-16T00:00:00Z"
      }
    ]
  }
}
```

**レスポンスフィールド説明(追加部分):**
| フィールド名 | 型 | 説明 |
|-------------|-----|------|
| usrGacha[].currentStepNumber | integer or null | 現在のステップ番号（1〜10、ステップアップガシャ以外はnull） |
| usrGacha[].loopCount | integer or null | 現在の周回数（0以上、ステップアップガシャ以外はnull、0=初回周回中） |
| usrGacha[].expiresAt | string or null | ガシャの有効期限（ISO8601形式） |

*備考:*
- `currentStepNumber` と `loopCount` は `usrGacha` オブジェクトの直接のフィールドとして返される（ネストなし）
- ステップアップガシャ以外のガシャでも `currentStepNumber`, `loopCount` キーは含まれるが値はnull

##### 3.2.3 ドメイン設計

**新規ドメイン追加 / 既存ドメイン改修:**
- [x] 既存ドメイン改修

**対象ドメイン:**
- ドメイン分類: Game
- ドメイン名: `Domain\Gacha`

**ファイル構成:**

*改修した既存ファイル:*
- [x] `api/app/Domain/Gacha/Models/UsrGachaInterface.php` - `getCurrentStepNumber()`, `setCurrentStepNumber()`, `getLoopCount()`, `setLoopCount()` 追加
- [x] `api/app/Http/ResponseFactories/ResponseDataFactory.php` - `addUsrGachaData()` に `currentStepNumber`, `loopCount` フィールド追加

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| UsrGachaInterface | getCurrentStepNumber() | 現在ステップ番号取得 | ?int、ステップアップ以外はnull |
| UsrGachaInterface | setCurrentStepNumber() | 現在ステップ番号設定 | progressStep()で使用 |
| UsrGachaInterface | getLoopCount() | 周回数取得 | ?int、ステップアップ以外はnull |
| UsrGachaInterface | setLoopCount() | 周回数設定 | progressStep()で使用 |
| StepupGachaService | progressStep() | ステップ進行処理 | UsrGachaInterfaceのsetter経由で更新、save()は呼ばない |
| ResponseDataFactory | addUsrGachaData() | usrGachaレスポンス生成 | currentStepNumber, loopCount含む |

**Entity設計:**
- Entityタイプ: 既存UsrGachaInterfaceの拡張
- 追加プロパティ:
  - `current_step_number`: ?int - 現在のステップ番号（1〜10, NULL可）
  - `loop_count`: ?int - 現在の周回数（0以上, NULL可）
- 既存Entityとの関係: `UsrGachaInterface` にsetter/getterを追加

##### 3.2.4 DB設計

**テーブル構造追加 / テーブル構造変更:**
- [x] テーブル構造変更（既存テーブルへのカラム追加）

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
| loop_count | INT UNSIGNED | NULL | NULL | 現在の周回数（ステップアップガシャの場合のみNULL以外、0=初回周回中） |

*補足:*
- ステップアップガシャ以外のガシャでは、これらのカラムはNULLのまま
- ステップアップガシャ初回実行時は `initializeAndValidate()` 内で `current_step_number` と `loop_count` の初期値を設定（nullの場合は1と0として扱う）
- `loop_count` は0から始まる（0=初回周回中、最終ステップ完了で1に増加）
- `usr_gachas` には累計回数とステップ進行状況の両方を記録
- save()はUseCase層の `saveUsr()` で一括保存

##### 3.2.5 エラーハンドリング

**エラーコード定義:**

| エラーコード | エラー名 | 発生条件 | ユーザーへのメッセージ | 対処方法 |
|-------------|---------|---------|---------------------|---------|
| E_GACHA_006 | STEPUP_STATUS_NOT_FOUND | ステップ進行状況が見つからない（内部エラー） | ガシャ情報の取得に失敗しました | initializeAndValidateで初期化 |
| E_GACHA_007 | STEPUP_LOOP_LIMIT_EXCEEDED | 周回数上限に達している | このガシャは実行できません | 周回数上限チェック |

**エラーハンドリングの実装方針:**
- `initializeAndValidate()` で初回実行時にデフォルト値で初期化
- Service層で周回数上限チェック
- エラー時は既存のガシャエラーと同じ形式で返却

##### 3.2.6 実装上の注意事項

**パフォーマンス考慮点:**
- ガシャ実行時に毎回DBから取得（同一トランザクション内で整合性を保つ）
- INDEXは `usr_user_id` を含むPRIMARY KEYで最適化

**セキュリティ考慮点:**
- `current_step_number` はサーバー側で管理し、クライアントからの `currentStepNumber` は照合用（不一致時はエラー）
- 不正なステップ実行を防止（サーバー側の状態を信頼）

**データ整合性:**
- トランザクション内でステップ進行とガシャ実行を同時処理
- ロールバック時にステップ進行もロールバック
- save()はUseCase側で一括実行

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
- `/api/gacha/draw/ad` はステップアップガシャ非対応

**リクエストパラメータ（JSON形式）:**
```json
{
  "gacha_id": "12345",
  "cost_type": "Diamond",
  "cost_id": null,
  "cost_num": 300,
  "play_num": 10,
  "currentStepNumber": 3
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
| currentStepNumber | integer or null | × | 現在のステップ番号（クライアント側の状態） | nullable, integer。サーバー側の状態と照合 |

**レスポンス構造（JSON形式）:**
```json
{
  "result": true,
  "data": {
    "gachaResults": [
      {
        "reward": {
          "unreceivedRewardReasonType": "None",
          "resourceType": "Unit",
          "resourceId": "10001",
          "resourceAmount": 1,
          "preConversionResource": null
        }
      }
    ],
    "stepRewards": [
      {
        "reward": {
          "unreceivedRewardReasonType": "None",
          "resourceType": "Item",
          "resourceId": "20001",
          "resourceAmount": 10,
          "preConversionResource": null
        }
      }
    ],
    "usrUnits": [...],
    "usrItems": [...],
    "usrParameter": {
      "diamond": 7000,
      "paidDiamond": 500
    },
    "usrGachaUppers": [],
    "usrGacha": {
      "oprGachaId": "12345",
      "adPlayedAt": null,
      "playedAt": "2025-12-16T10:30:00Z",
      "adCount": 0,
      "adDailyCount": 0,
      "count": 6,
      "dailyCount": 1,
      "currentStepNumber": 4,
      "loopCount": 0,
      "expiresAt": "2026-01-16T00:00:00Z"
    }
  }
}
```

**レスポンスフィールド説明:**
| フィールド名 | 型 | 説明 |
|-------------|-----|------|
| gachaResults | array | ガシャ抽選結果の配列 |
| gachaResults[].reward | object | 報酬情報（BaseRewardのレスポンス形式） |
| stepRewards | array | おまけ報酬の配列（おまけ報酬がない場合はキー自体がレスポンスに含まれない） |
| stepRewards[].reward | object | おまけ報酬情報（BaseRewardのレスポンス形式） |
| usrUnits | array | 獲得したユニット情報の配列 |
| usrItems | array | 獲得したアイテム情報の配列（おまけ報酬のアイテムも含む） |
| usrParameter | object | 更新後のユーザーパラメータ（ダイヤモンド残高等） |
| usrGachaUppers | array | 天井カウント情報の配列（ステップアップガシャでは常に空配列） |
| usrGacha.oprGachaId | string | ガシャID |
| usrGacha.count | integer | 累計実行回数 |
| usrGacha.dailyCount | integer | 日次実行回数 |
| usrGacha.currentStepNumber | integer or null | 実行後の現在ステップ番号（ステップアップガシャの場合のみ値あり） |
| usrGacha.loopCount | integer or null | 実行後の周回数（ステップアップガシャの場合のみ値あり） |
| usrGacha.playedAt | string | 最終実行日時（ISO8601形式） |
| usrGacha.expiresAt | string or null | 有効期限（ISO8601形式） |

*備考:*
- `stepRewards` はおまけ報酬がある場合のみレスポンスに含まれる（空の場合はキー不在、空配列やnullではない）
- `usrGachaUppers` はステップアップガシャでは常に空配列（天井機能は対象外）
- `usrGacha.currentStepNumber` と `usrGacha.loopCount` はステップ進行後の値（進行後のUsrGachaInterfaceから取得）

##### 3.3.3 ドメイン設計

**新規ドメイン追加 / 既存ドメイン改修:**
- [x] 既存ドメイン改修

**対象ドメイン:**
- ドメイン分類: Game
- ドメイン名: `Domain\Gacha`

**ファイル構成:**

*新規作成ファイル:*
- [x] `api/app/Domain/Gacha/Services/StepupGachaService.php` - ステップアップ固有ドメインロジック
- [x] `api/app/Domain/Gacha/Services/Draw/StepupGachaDrawService.php` - ステップアップ専用DrawService（GachaDrawService継承）
- [x] `api/app/Domain/Gacha/Factories/GachaServiceFactory.php` - ガシャタイプに応じたDrawService選択
- [x] `api/app/Domain/Gacha/Entities/StepupGachaState.php` - 初期化・バリデーション結果のreadonly値オブジェクト

*改修した既存ファイル:*
- [x] `api/app/Domain/Gacha/UseCases/GachaDrawUseCase.php` - `currentStepNumber` パラメータ追加、Factory経由でDrawService取得、stepRewardsハンドリング
- [x] `api/app/Domain/Gacha/Entities/GachaDrawRequest.php` - `currentStepNumber` プロパティ追加
- [x] `api/app/Domain/Gacha/Entities/GachaDrawResult.php` - `currentStepNumber`, `loopCount` プロパティ追加
- [x] `api/app/Http/Controllers/GachaController.php` - draw系メソッドに `currentStepNumber` バリデーション・パラメータ追加

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| GachaServiceFactory | getGachaDrawService() | ガシャタイプに応じたDrawService返却 | STEPUP→StepupGachaDrawService、それ以外→StandardGachaDrawService |
| StepupGachaDrawService | draw() | ステップアップガシャの抽選フロー全体 | GachaDrawServiceを継承 |
| StepupGachaService | initializeAndValidate() | 初期化＆バリデーション | StepupGachaStateを返却 |
| StepupGachaService | validateCost() | コスト検証 | 初回無料→FREE→ステップ定義→フォールバック |
| StepupGachaService | validateAndResolvePlayNum() | playNum解決 | FREE時はdrawCountを使用 |
| StepupGachaService | getLotteryBox() | ステップ別抽選BOX取得 | 通常枠＋確定枠 |
| StepupGachaService | progressStep() | ステップ進行 | UsrGachaInterfaceのsetter経由、save()は呼ばない |
| StepupGachaService | addStepRewards() | おまけ報酬をRewardDelegatorに追加 | ステップ進行前に呼び出し |
| GachaDrawUseCase | exec() | ガシャ実行の統括 | Factory経由でDrawServiceを取得し委譲 |
| GachaController | drawDiamond/drawFree/... | コントローラ | currentStepNumberバリデーション追加 |

**StepupGachaState（readonly値オブジェクト）:**
```php
readonly class StepupGachaState
{
    public function __construct(
        private OprStepupGachaEntity $stepupGacha,
        private OprStepupGachaStepEntity $stepupGachaStep,
        private int $currentStepNumber,
        private int $loopCount
    ) {}

    public function getStepupGacha(): OprStepupGachaEntity
    public function getStepupGachaStep(): OprStepupGachaStepEntity
    public function getCurrentStepNumber(): int
    public function getLoopCount(): int
}
```

**処理フロー（StepupGachaDrawService::draw）:**

```php
public function draw(GachaDrawRequest $request): GachaDrawResult
{
    // 1. リクエストからパラメータ取得
    // 2. コストタイプの許可チェック
    $this->gachaService->validateCostType($oprGacha, $costType);
    // 3. ユーザーガシャ情報取得
    $usrGacha = $this->gachaService->getUsrGacha($usr, $oprGachaId);
    // 4. 有効期限チェック
    $this->gachaService->validateExpiration($oprGacha, $now);
    // 5. ステップアップ固有の初期化・バリデーション
    $state = $this->stepupGachaService->initializeAndValidate($usrGacha, $oprGachaId, $request->getCurrentStepNumber());
    // 6. コスト検証（初回無料→Free→ステップ定義→フォールバック）
    $this->stepupGachaService->validateCost($oprGacha, $state->getStepupGachaStep(), $costType, $costId, $costNum, $state->getLoopCount());
    // 7. playNum解決
    $resolvedPlayNum = $this->stepupGachaService->validateAndResolvePlayNum($state->getStepupGachaStep(), $playNum, $costType);
    // 8. 天井なし（ステップアップガシャは天井対象外）
    $oprGachaUppers = collect();
    // 9. ステップ別抽選BOX取得
    $lotteryBox = $this->stepupGachaService->getLotteryBox($oprGacha, $state->getStepupGachaStep());
    // 10. 引き回数チェック・実行回数インクリメント
    // 11. リソース消費設定（広告なし）
    // 12. 抽選実行（fixedPrizeCount付き、最低連数チェックスキップ）
    $gachaBoxes = $this->gachaService->executeLottery($lotteryBox, $resolvedPlayNum, $fixedPrizeCount, true);
    // 13. 報酬作成・ガシャ報酬をRewardDelegatorに追加
    // 14. おまけ報酬をRewardDelegatorに追加（ステップ進行前）
    $this->stepupGachaService->addStepRewards($oprGachaId, $state->getCurrentStepNumber(), $state->getLoopCount());
    // 15. ステップ進行（UsrGachaInterfaceのsetterで更新）
    $this->stepupGachaService->progressStep($usrGacha, $state->getStepupGacha());
    // 16. ミッショントリガー送信
    // 17. ログ作成（ステップ情報付き: setStepNumber, setLoopCount）
    // 18. ユーザーデータ保存 (saveUsr)
    // 19. ログ送信 (sendGachaLog)
    // 20. 結果返却（currentStepNumber, loopCountはステップ進行前の値）
    return new GachaDrawResult($gachaRewards, $usrGacha, $usrGachaUppers, $logGachaAction, $state->getCurrentStepNumber(), $state->getLoopCount());
}
```

**initializeAndValidateの処理フロー:**

```php
public function initializeAndValidate(UsrGachaInterface $usrGacha, string $oprGachaId, ?int $clientStepNumber = null): StepupGachaState
{
    // 1. ステップアップガシャ設定取得
    $stepupGacha = $this->oprStepupGachaRepository->getByOprGachaId($oprGachaId);
    // 2. 現在のステップ番号と周回数を取得（nullの場合は初期値: step=1, loopCount=0）
    $currentStepNumber = $usrGacha->getCurrentStepNumber() ?? 1;
    $loopCount = $usrGacha->getLoopCount() ?? 0;
    // 3. クライアント側のステップ番号と照合（不一致時はエラー）
    if ($clientStepNumber !== null && $clientStepNumber !== $currentStepNumber) {
        throw new GameException(...);
    }
    // 4. 周回上限チェック（maxLoopCount !== null && loopCount >= maxLoopCount）
    // 5. 現在のステップ設定取得
    $stepConfig = $this->oprStepupGachaStepRepository->getByOprGachaIdStepNumber($oprGachaId, $currentStepNumber);
    // 6. StepupGachaState返却
    return new StepupGachaState($stepupGacha, $stepConfig, $currentStepNumber, $loopCount);
}
```

**validateCostの処理フロー:**

```php
public function validateCost(OprGachaEntity $oprGacha, OprStepupGachaStepEntity $step, CostType $costType, ?string $costId, int $costNum, int $loopCount): void
{
    // 1. 初回無料判定（isFirstFree && loopCount === 0 → costTypeがFREEでなければエラー）
    // 2. ステップ定義がFREE → costTypeがFREEでなければエラー
    // 3. ステップ定義のコストタイプと一致 → validateBasicCost()で個別コスト検証
    // 4. フォールバック: opr_gacha_use_resources テーブルから代替コスト（補填チケット等）検索
}
```

**progressStepの処理フロー:**

```php
public function progressStep(UsrGachaInterface $usrGacha, OprStepupGachaEntity $stepupGacha): void
{
    $currentStepNumber = $usrGacha->getCurrentStepNumber() ?? 1;
    $loopCount = $usrGacha->getLoopCount() ?? 0;

    if ($currentStepNumber >= $stepupGacha->getMaxStepNumber()) {
        // 最終ステップ完了 → 周回: step=1, loopCount+1
        $newLoopCount = $loopCount + 1;
        if (!is_null($stepupGacha->getMaxLoopCount()) && $newLoopCount > $stepupGacha->getMaxLoopCount()) {
            throw new GameException(...);
        }
        $usrGacha->setCurrentStepNumber(1);
        $usrGacha->setLoopCount($newLoopCount);
    } else {
        // 次のステップへ: step+1
        $usrGacha->setCurrentStepNumber($currentStepNumber + 1);
    }
    // save()は呼ばない（UseCase側のsaveUsr()で保存される）
}
```

**GachaDrawUseCase::exec の処理フロー:**

```php
public function exec(
    CurrentUser $usr, string $oprGachaId, int $drewCount, int $playNum,
    ?string $costId, int $costNum, int $platform, string $billingPlatform,
    CostType $costType, ?int $currentStepNumber = null
): GachaDrawResultData
{
    // 1. ガシャマスタデータ取得
    $oprGacha = $this->gachaService->getOprGacha($oprGachaId);
    // 2. GachaDrawRequest作成（currentStepNumber含む）
    $request = new GachaDrawRequest(..., $currentStepNumber);
    // 3. Factoryパターンでガシャタイプに応じたDrawServiceを取得
    $drawService = $this->gachaServiceFactory->getGachaDrawService($oprGacha->getGachaType());
    // 4. トランザクション内でdraw実行
    $gachaDrawResult = DB::transaction(fn() => $drawService->draw($request));
    // 5. RewardDelegator経由で全報酬配布
    $this->rewardDelegator->sendRewards();
    // 6. stepRewards取得（StepupGachaStepRewardクラスのみ抽出）
    $stepRewards = $this->rewardDelegator->getSentRewards(StepupGachaStepReward::class);
    // 7. ガシャ履歴保存（step情報含む）
    $this->gachaService->addGachaHistory(..., $gachaDrawResult->getCurrentStepNumber(), $gachaDrawResult->getLoopCount(), $stepRewards->isNotEmpty() ? $stepRewards : null);
    // 8. レスポンスデータ生成
    return new GachaDrawResultData(..., $stepRewards, ..., $gachaDrawResult->getUsrGacha(), ...);
}
```

##### 3.3.4 DB設計

**テーブル構造追加 / テーブル構造変更:**
- [ ] 既存テーブルを使用（`usr_gachas`, `log_gacha_actions` 等）

##### 3.3.5 エラーハンドリング

**エラーコード定義:**

| エラーコード | エラー名 | 発生条件 | ユーザーへのメッセージ | 対処方法 |
|-------------|---------|---------|---------------------|---------|
| E_GACHA_008 | STEPUP_COST_MISMATCH | リクエストのコスト情報とステップ定義が不一致（フォールバックでも見つからない場合） | コスト情報が正しくありません | コスト整合性チェック |
| E_GACHA_009 | STEPUP_PLAY_NUM_MISMATCH | リクエストの実行回数とマスタデータが不一致 | 実行回数が正しくありません | 実行回数整合性チェック |
| E_GACHA_010 | STEPUP_LOOP_LIMIT_EXCEEDED | 周回数上限到達 | このガシャは実行できません | 周回数上限チェック |
| E_GACHA_011 | STEPUP_GACHA_EXPIRED | ガシャ期間外 | このガシャは現在利用できません | 期間チェック |
| E_GACHA_013 | STEPUP_CLIENT_STEP_MISMATCH | クライアントのcurrentStepNumberとサーバー側の状態が不一致 | ガシャ情報を更新してください | ステップ番号照合エラー |

**エラーハンドリングの実装方針:**
- UseCase層でトランザクション制御、エラー時はロールバック
- StepupGachaService層でバリデーションエラーをthrow
- エラーレスポンスは既存のガシャエラーと同じ形式

##### 3.3.6 実装上の注意事項

**パフォーマンス考慮点:**
- トランザクション時間を最小化（不要な処理は事前実行）
- N+1問題の回避（ステップ設定、賞品情報の一括取得）

**セキュリティ考慮点:**
- コスト整合性の厳密な検証（チート対策）
- サーバー側の状態（`usr_gachas`）を信頼し、クライアントの `currentStepNumber` は照合用
- 初回無料は `loopCount === 0` の場合のみ有効

**データ整合性:**
- トランザクション内で全処理を完結
- ロールバック時にすべての変更を取り消し

**既存実装との整合性:**
- `GachaDrawService` を継承した `StepupGachaDrawService` で実装
- `GachaServiceFactory` によるポリモーフィズムで分岐（UseCase側にif-else不要）
- `GachaService` の既存メソッド（`executeLottery`, `setConsumeResource` 等）を活用

---

#### 要件 GE-2: 確定枠抽選処理

##### 3.3.2 要件概要
- **要件ID:** REQ-GE-2
- **実現内容:** レアリティ条件付き確定枠の抽選処理を実装する

##### 3.3.2 API設計

**新規API追加 / 既存API改修:**
- [x] 内部処理（API変更なし）

##### 3.3.3 ドメイン設計

**実装ファイル:**
- [x] `api/app/Domain/Gacha/Services/StepupGachaService.php` - 確定枠抽選メソッド

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| StepupGachaService | getLotteryBox() | ステップ別抽選BOX取得（通常枠＋確定枠） | 通常抽選BOXと確定枠BOXを含むGachaLotteryBoxを返す |
| StepupGachaService | getFixedPrizeBox() | 確定枠BOX取得（private） | prizeGroupIdはステップ設定→ガシャ設定の優先順位 |
| StepupGachaService | filterPrizesByRarity() | レアリティフィルタリング（private） | N(1) < R(2) < SR(3) < SSR(4) < UR(5) のthreshold以上でフィルタ |

**抽選ロジック:**

```php
public function getLotteryBox(OprGachaEntity $oprGacha, OprStepupGachaStepEntity $step): GachaLotteryBox
{
    // 通常枠の賞品グループID取得（ステップ個別 or ガシャ共通）
    $prizeGroupId = $step->getPrizeGroupId() ?? $oprGacha->getPrizeGroupId();
    $normalBox = $this->oprGachaPrizeRepository->getListByPrizeGroupId($prizeGroupId);
    // 確定枠BOX取得
    $fixedBox = $this->getFixedPrizeBox($oprGacha, $step);
    return new GachaLotteryBox($normalBox, $fixedBox);
}

private function getFixedPrizeBox(OprGachaEntity $oprGacha, OprStepupGachaStepEntity $step): ?Collection
{
    if ($step->getFixedPrizeCount() === 0) return null;
    // 確定枠賞品グループID取得（ステップ個別 or ガシャ共通）
    $fixedPrizeGroupId = $step->getFixedPrizeGroupId() ?? $oprGacha->getFixedPrizeGroupId();
    $prizes = $this->oprGachaPrizeRepository->getListByPrizeGroupId($fixedPrizeGroupId);
    // レアリティフィルタリング
    $rarityThreshold = $step->getFixedPrizeRarityThresholdType();
    if ($rarityThreshold !== null) {
        $prizes = $this->filterPrizesByRarity($prizes, $rarityThreshold);
    }
    return $prizes;
}

private function filterPrizesByRarity(Collection $prizes, RarityType $rarityThresholdType): Collection
{
    // RarityType の数値順序: N(1) < R(2) < SR(3) < SSR(4) < UR(5)
    return $prizes->filter(fn($prize) => $prize->getRarity()->value >= $rarityThresholdType->value);
}
```

##### 3.3.5 エラーハンドリング

**エラーコード定義:**

| エラーコード | エラー名 | 発生条件 | ユーザーへのメッセージ | 対処方法 |
|-------------|---------|---------|---------------------|---------|
| E_GACHA_012 | FIXED_PRIZE_NOT_AVAILABLE | 確定枠対象アイテムが存在しない | ガシャ設定に不備があります | マスタデータ検証 |

##### 3.3.6 実装上の注意事項

**既存実装との整合性:**
- 既存の確定枠ロジック（10連の最後1回確定）を拡張
- `GachaService::executeLottery` に `fixedPrizeCount` を渡し、最低連数チェックスキップフラグを `true` に設定

---

#### 要件 GE-3: おまけ報酬の付与処理

##### 3.3.7 要件概要
- **要件ID:** REQ-GE-3（旧REQ-GE-5）
- **実現内容:** ステップ実行時におまけ報酬（ガシャ抽選結果とは別の追加報酬）を付与する

##### 3.3.8 API設計

**新規API追加 / 既存API改修:**
- [x] 既存API改修（ガシャ実行APIのレスポンスに `stepRewards` フィールドを追加）

##### 3.3.9 ドメイン設計

**新規作成ファイル:**
- [x] `api/app/Domain/Resource/Entities/Rewards/StepupGachaStepReward.php` - おまけ報酬Entity（BaseReward継承）

**改修した既存ファイル:**
- [x] `api/app/Domain/Gacha/Services/StepupGachaService.php` - `addStepRewards()`, `makeStepRewards()` メソッド
- [x] `api/app/Domain/Gacha/UseCases/GachaDrawUseCase.php` - stepRewards取得とレスポンス設定
- [x] `api/app/Http/Responses/ResultData/GachaDrawResultData.php` - `stepRewards` フィールド追加
- [x] `api/app/Http/ResponseFactories/ResponseDataFactory.php` - `addStepRewardsData()` メソッド追加
- [x] `api/app/Http/ResponseFactories/GachaResponseFactory.php` - `createDrawResponse()` に `addStepRewardsData()` 呼び出し追加

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| StepupGachaService | addStepRewards() | おまけ報酬をRewardDelegatorに追加 | ステップ進行前に呼び出し |
| StepupGachaService | makeStepRewards() | Entity→StepupGachaStepReward変換（private） | OprStepupGachaStepRewardEntity→StepupGachaStepReward |
| StepupGachaStepReward | getRewardResponseData() | レスポンス用報酬データ返却 | BaseRewardの実装を使用 |
| ResponseDataFactory | addStepRewardsData() | stepRewardsをレスポンスに追加 | 空の場合はキー自体を含めない |

**実装例:**

```php
// StepupGachaService
public function addStepRewards(string $oprGachaId, int $stepNumber, int $loopCount): void
{
    $stepRewardEntities = $this->stepRewardRepository->getRewardsForStep($oprGachaId, $stepNumber, $loopCount);
    if ($stepRewardEntities->isEmpty()) return;
    $stepRewards = $this->makeStepRewards($stepRewardEntities, $oprGachaId, $stepNumber, $loopCount);
    $this->rewardDelegator->addRewards($stepRewards);
}

// ResponseDataFactory
public function addStepRewardsData(array $result, Collection $stepRewards): array
{
    $response = [];
    foreach ($stepRewards as $reward) {
        $response[] = $reward->getRewardResponseData();
    }
    if (!empty($response)) {
        $result['stepRewards'] = $response;
    }
    return $result; // 空の場合はキー不在
}
```

##### 3.3.10 DB設計

（REQ-MD-3のDB設計を参照）

##### 3.3.11 エラーハンドリング

**エラーコード定義:**

| エラーコード | エラー名 | 発生条件 | ユーザーへのメッセージ | 対処方法 |
|-------------|---------|---------|---------------------|---------|
| E_GACHA_020 | STEP_REWARD_RESOURCE_INVALID | おまけ報酬のリソースが不正 | 報酬の付与に失敗しました | マスタデータのresource_type/resource_id検証 |
| E_GACHA_021 | STEP_REWARD_DISTRIBUTION_FAILED | おまけ報酬の付与処理に失敗 | 報酬の付与に失敗しました | トランザクションロールバック |

**エラーハンドリングの実装方針:**
- RewardDelegator経由で付与するため、既存の報酬付与エラーハンドリングを活用
- おまけ報酬付与失敗時はトランザクション全体をロールバック

##### 3.3.12 実装上の注意事項

**パフォーマンス考慮点:**
- おまけ報酬取得クエリ: `WHERE step_number=? AND (loop_count_target IS NULL OR loop_count_target=?)`

**セキュリティ考慮点:**
- おまけ報酬はガシャ抽選結果とは独立した報酬

**データ整合性:**
- おまけ報酬もガシャ実行と同一トランザクション内で付与
- `addStepRewards()` はステップ進行前に呼び出す（進行前のstepNumber/loopCountで報酬を決定）

**既存実装との整合性:**
- RewardDelegatorを使用して報酬付与
- `stepRewards` が空の場合はレスポンスにキー自体を含めない

---

### 3.4 レスポンス管理

#### 要件 RS-1: ガシャ実行レスポンスの拡張

##### 3.4.1 要件概要
- **要件ID:** REQ-RS-1
- **実現内容:** ガシャ実行APIのレスポンスにステップアップガシャ用の情報を追加する

##### 3.4.2 API設計

（REQ-GE-1のレスポンス構造を参照）

##### 3.4.3 ドメイン設計

**改修した既存ファイル:**
- [x] `api/app/Http/Responses/ResultData/GachaDrawResultData.php` - `stepRewards` フィールド追加
- [x] `api/app/Http/ResponseFactories/ResponseDataFactory.php` - `addUsrGachaData()` に `currentStepNumber`, `loopCount` 追加、`addStepRewardsData()` メソッド追加
- [x] `api/app/Http/ResponseFactories/GachaResponseFactory.php` - `createDrawResponse()` に `addStepRewardsData()` 呼び出し追加

*新規作成ファイル:*
- [x] `api/app/Http/Responses/Data/StepupGachaPrizeInfoData.php` - ステップ別排出率情報のレスポンスデータ

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| ResponseDataFactory | addUsrGachaData() | usrGacha配列生成 | currentStepNumber, loopCount, expiresAtを含める |
| ResponseDataFactory | addStepRewardsData() | stepRewards配列生成 | 空の場合はキー不在 |
| GachaResponseFactory | createDrawResponse() | drawレスポンス生成 | addStepRewardsData()呼び出しを含む |
| GachaResponseFactory | createPrizeResponse() | prizeレスポンス生成 | stepupGachaPrizesが非空なら追加 |
| StepupGachaPrizeInfoData | formatToResponse() | ステップ別排出率レスポンス | stepNumber, drawCount, fixedPrizeCount等 |

**レスポンス組み立て順序（createDrawResponse）:**
1. `addGachaResultData` — ガシャ報酬
2. `addStepRewardsData` — おまけ報酬（ステップアップ用）
3. `addUsrUnitData` — 変更されたユニット
4. `addUsrItemData` — 変更されたアイテム
5. `addUsrParameterData` — パラメータ
6. `addUsrGachaUpperData` — 天井情報
7. `addUsrGachaData` — ガシャ状態（`$isMulti=false`、キー名は`usrGacha`単数形）

**usrGachaのレスポンス構造:**
```php
$response[] = [
    'oprGachaId' => $usrGacha->getOprGachaId(),
    'adPlayedAt' => StringUtil::convertToISO8601($usrGacha->getAdPlayedAt()),
    'playedAt' => StringUtil::convertToISO8601($usrGacha->getPlayedAt()),
    'adCount' => $usrGacha->getAdCount(),
    'adDailyCount' => $usrGacha->getAdDailyCount(),
    'count' => $usrGacha->getCount(),
    'dailyCount' => $usrGacha->getDailyCount(),
    'currentStepNumber' => $usrGacha->getCurrentStepNumber(), // ステップアップ以外はnull
    'loopCount' => $usrGacha->getLoopCount(),                 // ステップアップ以外はnull
    'expiresAt' => StringUtil::convertToISO8601($expiresAt),
];
```

##### 3.4.5 エラーハンドリング

N/A（レスポンス生成時のエラーは想定しない）

##### 3.4.6 実装上の注意事項

**既存実装との整合性:**
- `currentStepNumber`, `loopCount` フィールドを `usrGacha` に直接追加（ネストなし）
- ステップアップガシャ以外では `currentStepNumber: null`, `loopCount: null`
- draw レスポンスでは `usrGacha` は単数形キー（`$isMulti = false`）
- `stepRewards` が空の場合はレスポンスにキー自体を含めない

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

**レスポンス構造（ステップアップガシャ部分）:**
```json
{
  "result": true,
  "data": {
    "gachaHistories": [
      {
        "oprGachaId": "12345",
        "costType": "Diamond",
        "costId": null,
        "costNum": 300,
        "drawCount": 10,
        "playedAt": "2025-12-16T10:30:00Z",
        "results": [
          {
            "sortOrder": 1,
            "reward": {
              "unreceivedRewardReasonType": "None",
              "resourceType": "Unit",
              "resourceId": "10001",
              "resourceAmount": 1,
              "preConversionResource": null
            }
          }
        ],
        "stepupInfo": {
          "stepNumber": 3,
          "loopCount": 0
        },
        "stepRewards": [
          {
            "unreceivedRewardReasonType": "None",
            "resourceType": "FreeDiamond",
            "resourceId": null,
            "resourceAmount": 100,
            "preConversionResource": null
          }
        ]
      }
    ]
  }
}
```

*備考:*
- `stepupInfo` は `stepNumber` と `loopCount` が両方非null の場合のみ設定（`hasStepupInfo()` で判定）。ステップアップガシャ以外ではnull
- `stepRewards` はおまけ報酬がある場合のみ設定。ステップアップガシャ以外またはおまけ報酬なしの場合はnull
- 後方互換性: `__unserialize()` で旧データ（stepNumber等がない）にも `?? null` でデフォルト値を設定

##### 3.5.3 ドメイン設計

**ファイル構成:**

*改修した既存ファイル:*
- [x] `api/app/Domain/Gacha/Models/ILogGachaAction.php` - `setStepNumber()`, `setLoopCount()` メソッド追加
- [x] `api/app/Domain/Gacha/Services/GachaService.php` - `addGachaHistory()` に `stepNumber`, `loopCount`, `stepRewards` パラメータ追加
- [x] `api/app/Domain/Gacha/Services/GachaLogService.php` - `sendGachaLog()` に `stepNumber` パラメータ追加
- [x] `api/app/Domain/Gacha/Entities/GachaHistory.php` - `stepNumber`, `loopCount`, `stepRewards` プロパティ追加、`formatToResponse()` に `stepupInfo`, `stepRewards` 追加

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| ILogGachaAction | setStepNumber() | ステップ番号設定 | ステップアップガシャのみ |
| ILogGachaAction | setLoopCount() | 周回数設定 | ステップアップガシャのみ |
| GachaService | addGachaHistory() | ガシャ履歴をキャッシュに保存 | stepNumber, loopCount, stepRewards含む |
| GachaLogService | sendGachaLog() | ガシャログ送信 | stepNumber含む |
| GachaHistory | formatToResponse() | 履歴レスポンス生成 | stepupInfoとstepRewardsを含む |
| GachaHistory | hasStepupInfo() | ステップアップ情報有無判定 | stepNumberとloopCountが両方非null |

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
| loop_count | INT UNSIGNED | NULL | NULL | 実行時の周回数（ステップアップガシャの場合のみNULL以外、0=初回周回中） |

##### 3.5.6 実装上の注意事項

**既存実装との整合性:**
- `log_gacha_actions` にステップアップガシャの履歴も記録（`step_number`, `loop_count` を含む）
- 既存のログ記録パターンを踏襲
- `GachaHistory` の `__unserialize()` で旧データとの後方互換性を確保

---

#### 要件 HI-2: ガシャ賞品情報のステップ対応

##### 3.5.7 要件概要
- **要件ID:** REQ-HI-2
- **実現内容:** `/api/gacha/prize` APIでステップごとの排出率情報を返却する

##### 3.5.8 ドメイン設計

**改修した既存ファイル:**
- [x] `api/app/Domain/Gacha/UseCases/GachaPrizeUseCase.php` - `StepupGachaService` をDI、ステップ別排出率取得

*新規作成ファイル:*
- [x] `api/app/Http/Responses/Data/StepupGachaPrizeInfoData.php` - レスポンスデータ
- [x] `api/app/Http/Responses/ResultData/GachaPrizeResultData.php` - `stepupGachaPrizes` フィールド追加

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| StepupGachaService | getPrizes() | 各ステップの排出率情報取得 | Collection返却 |
| GachaPrizeUseCase | exec() | 賞品情報取得の統括 | ステップアップの場合はstepupGachaPrizesをセット |
| StepupGachaPrizeInfoData | formatToResponse() | レスポンス生成 | stepNumber, drawCount, fixedPrizeCount, rarityProbabilities等 |
| GachaResponseFactory | createPrizeResponse() | 賞品レスポンス生成 | stepupGachaPrizesが非空なら追加、空ならnull |

---

## 4. 実装の優先順位と依存関係

### 4.1 実装の段階分け

**フェーズ1: 基盤実装（マスタデータ・ユーザーデータ管理）**
1. REQ-MD-1: ステップアップガシャ基本情報管理（テーブル、Entity、Repository作成）
2. REQ-MD-2: ステップごとの詳細設定管理（テーブル、Entity、Repository作成）
3. REQ-MD-3: ステップごとのおまけ報酬管理（テーブル、Entity、Repository作成）
4. REQ-UD-1: ステップ進行状況の管理（テーブル、Entity、Repository作成）
5. `GachaType::STEPUP = 'Stepup'` の追加

**フェーズ2: コア機能実装（ガシャ実行処理）**
1. REQ-GE-1: ステップアップガシャの実行（StepupGachaService、StepupGachaDrawService、GachaServiceFactory作成、UseCase拡張）
2. REQ-GE-2: 確定枠抽選処理（StepupGachaService内で実装）
3. REQ-GE-3: おまけ報酬の付与処理（StepupGachaService、StepupGachaStepReward、RewardDelegator連携）
4. REQ-HI-1: ガシャ履歴の管理（GachaHistory拡張、ログテーブルカラム追加）
5. REQ-HI-2: ガシャ賞品情報のステップ対応（GachaPrizeUseCase拡張）

**フェーズ3: レスポンス拡張・周辺機能**
1. REQ-RS-1: ガシャ実行レスポンスの拡張（ResponseDataFactory、GachaResponseFactory拡張）
2. `update_and_fetch` のレスポンス拡張（addUsrGachaDataにcurrentStepNumber, loopCount追加）
3. ガシャ履歴のステップアップガシャ対応（GachaHistory.formatToResponse拡張）
4. ガシャ賞品情報のステップアップガシャ対応（StepupGachaPrizeInfoData作成）
5. 多言語対応（`opr_stepup_gacha_steps_i18n` テーブル作成）

### 4.2 依存関係マップ

```
REQ-MD-1 (opr_stepup_gachas)
  ↓
REQ-MD-2 (opr_stepup_gacha_steps) ← depends on REQ-MD-1
  ↓
REQ-MD-3 (opr_stepup_gacha_step_rewards) ← depends on REQ-MD-1
  ↓
REQ-UD-1 (usr_gachasカラム追加) ← depends on REQ-MD-1
  ↓
REQ-GE-1 (ガシャ実行処理) ← depends on REQ-MD-1, REQ-MD-2, REQ-UD-1
  ├→ REQ-GE-2 (確定枠抽選) ← depends on REQ-GE-1
  ├→ REQ-GE-3 (おまけ報酬付与) ← depends on REQ-GE-1, REQ-MD-3
  ├→ REQ-HI-1 (履歴管理) ← depends on REQ-GE-1
  └→ REQ-HI-2 (賞品情報) ← depends on REQ-GE-1
     ↓
REQ-RS-1 (レスポンス拡張) ← depends on REQ-GE-1, REQ-GE-3, REQ-UD-1
```

### 4.3 実装時の注意点

- フェーズ1を完了してからフェーズ2に進む（テーブル作成→ロジック実装の順）
- マイグレーションファイルは依存関係順に作成・実行
- テストは各フェーズごとに実施（ユニットテスト→機能テスト）
- 既存のガシャ機能に影響を与えないよう、Factoryパターンで分岐を実装
- ステップアップガシャでは天井（Upper）機能は対象外（`$oprGachaUppers = collect()`）

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
| StepupGachaService::initializeAndValidate() | 初回実行（null状態） | currentStepNumber=1, loopCount=0 |
| StepupGachaService::initializeAndValidate() | clientStepNumber不一致 | 例外throw |
| StepupGachaService::initializeAndValidate() | 周回上限到達 | 例外throw |
| StepupGachaService::progressStep() | 最終ステップ以外 | currentStepNumber+1 |
| StepupGachaService::progressStep() | 最終ステップ | currentStepNumber=1, loopCount++ |
| StepupGachaService::progressStep() | 最終ステップ＋周回上限超過 | 例外throw |
| StepupGachaService::validateCost() | 初回無料（isFirstFree=true, loopCount=0, costType=Free） | 成功 |
| StepupGachaService::validateCost() | 初回無料で非Free | 例外throw |
| StepupGachaService::validateCost() | ステップ定義と一致 | 成功 |
| StepupGachaService::validateCost() | フォールバック（代替コスト） | opr_gacha_use_resources検索 |
| StepupGachaService::getLotteryBox() | 確定枠あり（SSR以上） | 通常枠BOX＋フィルタ済み確定枠BOX |
| StepupGachaService::addStepRewards() | おまけ報酬あり（loopCountTarget=NULL） | 全周回でおまけ追加 |
| StepupGachaService::addStepRewards() | おまけ報酬あり（loopCountTarget=1） | 1週目のみおまけ追加 |
| StepupGachaService::addStepRewards() | おまけ報酬なし | 何も追加しない |
| StepupGachaService::filterPrizesByRarity() | SR以上でフィルタ | SR, SSR, URのみ残る |

**テストファイル:**
- `api/tests/Unit/Domain/Gacha/Services/StepupGachaServiceTest.php`
- `api/tests/Unit/Domain/Gacha/Services/Draw/StepupGachaDrawServiceTest.php`
- `api/tests/Unit/Domain/Resource/Mst/Repositories/OprStepupGachaRepositoryTest.php`
- `api/tests/Unit/Domain/Resource/Mst/Repositories/OprStepupGachaStepRepositoryTest.php`
- `api/tests/Unit/Domain/Resource/Mst/Repositories/OprStepupGachaStepRewardRepositoryTest.php`

### 5.2 機能テスト

**テスト対象:**
- APIエンドポイント
- リクエスト/レスポンスの検証
- データベースの状態変化

**テストケース例:**

| API | テストケース | 期待結果 |
|-----|------------|---------|
| POST /api/gacha/draw/diamond | ステップアップガシャ初回実行 | 200 OK、usrGachaのcurrentStepNumber=2, loopCount=0 |
| POST /api/gacha/draw/diamond | ステップアップガシャ最終ステップ実行 | 200 OK、currentStepNumber=1, loopCount=1 |
| POST /api/gacha/draw/diamond | currentStepNumber照合不一致 | エラーレスポンス |
| POST /api/gacha/draw/diamond | 周回数上限到達後の実行 | エラーレスポンス |
| POST /api/gacha/draw/diamond | コスト不足 | エラーレスポンス |
| POST /api/gacha/draw/diamond | 初回無料ステップ | 200 OK、コスト消費なし |
| POST /api/gacha/draw/diamond | おまけ報酬あり | 200 OK、stepRewardsキーあり |
| POST /api/gacha/draw/diamond | おまけ報酬なし | 200 OK、stepRewardsキー不在 |
| POST /api/gacha/draw/free | ステップアップガシャFREEステップ | 200 OK |
| POST /api/game/update_and_fetch | ステップアップガシャ進行中 | 200 OK、usrGachaにcurrentStepNumber, loopCount含む |
| POST /api/gacha/history | ステップアップガシャ実行後 | 200 OK、stepupInfoとstepRewards含む |
| POST /api/gacha/prize | ステップアップガシャ | 200 OK、stepupGachaPrizes含む |

**テストファイル:**
- `api/tests/Feature/Api/Gacha/DrawStepupGachaTest.php`
- `api/tests/Feature/Api/Game/UpdateAndFetchStepupGachaTest.php`

### 5.3 シナリオテスト

**テストシナリオ例:**

**シナリオ1: 初回〜完走〜2周目への移行**
1. ユーザーがステップ1のガシャを実行（currentStepNumber=1指定）
2. `usr_gachas` の `current_step_number=2, loop_count=0` が設定される
3. ステップ2〜9を順次実行
4. ステップ10（最終ステップ）を実行
5. `current_step_number=1, loop_count=1` に更新
6. ステップ1を再度実行（2周目開始）
7. データベースに正しく記録される

**シナリオ2: 確定枠抽選の検証**
1. 10連ガシャ、確定枠3体（SSR以上確定）のステップを実行
2. 抽選結果の最後の3体がSSR以上であることを検証（filterPrizesByRarityでフィルタ）
3. 最初の7体は通常抽選（SR以下も含む）

**シナリオ3: 周回数上限到達**
1. max_loop_count=2 のガシャで全ステップを2周実行
2. 3周目のステップ1実行をリクエスト
3. initializeAndValidateでloopCount >= maxLoopCountのチェックでエラー

**シナリオ4: 初回無料ステップ**
1. is_first_free=trueのステップ1をcostType=Freeで実行（loopCount=0）
2. コスト消費なしで成功
3. 2周目（loopCount=1）で同じステップ1を実行する場合はcostType=Freeでは失敗

**シナリオ5: コスト検証フォールバック**
1. ステップ定義のcostType=Diamondだが、補填チケット（Item）で実行
2. validateCostのフォールバックでopr_gacha_use_resourcesを検索
3. 補填チケットが許可されていれば成功

---

## 6. マイグレーション計画

### 6.1 マイグレーションファイル一覧

**作成されたマイグレーション:**
- [x] `mst/2025_12_17_000001_alter_opr_gachas_add_stepup_to_gacha_type.php` - `opr_gachas.gacha_type` に 'StepUp' を追加
- [x] `mst/2025_12_17_000002_create_opr_stepup_gacha_tables.php` - `opr_stepup_gachas`, `opr_stepup_gacha_steps` テーブル作成
- [x] `mst/2025_12_17_000003_create_opr_stepup_gacha_step_rewards_table.php` - `opr_stepup_gacha_step_rewards` テーブル作成
- [x] `2025_12_17_000004_add_stepup_columns_to_usr_gachas_table.php` - `usr_gachas` へカラム追加（current_step_number: tinyInt nullable, loop_count: uint nullable）
- [x] `2025_12_17_000005_add_stepup_columns_to_log_gacha_actions_table.php` - `log_gacha_actions` へカラム追加（step_number: tinyInt nullable, loop_count: uint nullable）
- [x] `mst/2026_02_09_000000_create_opr_stepup_gacha_steps_i18n.php` - `opr_stepup_gacha_steps_i18n` テーブル作成（多言語対応: fixed_prize_description）
- [x] `mst/2026_02_16_000000_alter_opr_gachas_rename_stepup_gacha_type.php` - `gacha_type` enum値を 'StepUp' → 'Stepup' に統一

### 6.2 マイグレーション実行順序

1. `2025_12_17_000001` - 既存テーブルへのENUM追加（'StepUp'）
2. `2025_12_17_000002` - 基盤テーブル作成（`opr_stepup_gachas` と `opr_stepup_gacha_steps`）
3. `2025_12_17_000003` - おまけ報酬テーブル作成（`opr_stepup_gacha_step_rewards`）
4. `2025_12_17_000004` - `usr_gachas` へのカラム追加（`current_step_number`, `loop_count`）
5. `2025_12_17_000005` - `log_gacha_actions` へのカラム追加（`step_number`, `loop_count`）
6. `2026_02_09_000000` - 多言語対応テーブル作成（`opr_stepup_gacha_steps_i18n`）
7. `2026_02_16_000000` - gacha_type値のリネーム（'StepUp' → 'Stepup'）

### 6.3 ロールバック方針

- 各マイグレーションには `down()` メソッドを実装済み
- ロールバック時は逆順で実行（`drop table`, `alter table` の取り消し）
- 外部キー制約により、親テーブルのロールバックは子テーブル削除後に実行

---

## 7. 連携が必要なスキル・ツール

**Claude Code スキル:**
- `api-request-validation`: リクエストパラメータのバリデーション実装（コスト整合性チェック、currentStepNumber照合）
- `domain-layer`: ドメインレイヤーの実装パターン参照（StepupGachaService、StepupGachaDrawService）
- `api-schema-reference`: glow-schema YAMLとの整合性確認（レスポンス構造）
- `api-response`: レスポンス構造の実装（currentStepNumber、loopCount、stepRewardsフィールド追加）
- `migration`: マイグレーションファイルの作成・実行
- `api-test-implementation`: テストコードの実装

**使用タイミング:**
- テーブル設計時: `migration`
- ドメインロジック実装時: `domain-layer`
- API実装時: `api-request-validation`, `api-response`, `api-schema-reference`
- テスト実装時: `api-test-implementation`

---

## 8. 実装時の判断が必要な事項

### 8.1 技術的な選択肢（決定済み）

**項目: リクエストパラメータのステップ番号整合性チェック**
- **採用: クライアントからcurrentStepNumberを受け取り、サーバー側の状態と照合する**
  - 実装: `initializeAndValidate()` 内で `clientStepNumber !== null && clientStepNumber !== currentStepNumber` 時にエラー
  - クライアントからのcurrentStepNumberはnullable（送信しない場合は照合スキップ）
  - サーバー側の状態（`usr_gachas.current_step_number`）を絶対的な真実として扱う

**項目: ガシャタイプによる処理分岐方式**
- **採用: Factoryパターン**
  - `GachaServiceFactory` でガシャタイプに応じた `DrawService` を選択
  - UseCase側にif-else分岐不要、ポリモーフィズムで処理委譲
  - 新しいガシャタイプ追加時もFactory側の修正のみで対応可能

**項目: ステップアップ固有ロジックの配置**
- **採用: 専用Serviceクラス（StepupGachaService）に分離**
  - 既存の `GachaService` への影響を最小化
  - `StepupGachaDrawService` → `StepupGachaService` → 各Repository の階層で実装

**項目: データクリーンアップの実装方式**
- **採用: ガシャアクセス時に期限判定し、期限切れの場合は削除**
  - 理由: 既存のガシャシステムと同じ方式を採用（アクセス時判定）

### 8.2 仕様の解釈

**項目: 周回数上限到達後のUI表示**
- 解釈A: ガシャ一覧に表示されるが、実行不可状態
- 解釈B: ガシャ一覧から非表示
- **確認先: クライアント側の実装方針に従う**
  - サーバー側は周回上限到達時にエラーを返却する実装とし、UI表示はクライアント側で判断

---

## 9. 補足情報

### 9.1 参考にすべき既存実装

**類似機能の実装例:**
- `Domain\Gacha\Services\GachaService` - 既存のガシャ共通ロジック（executeLottery, setConsumeResource等）
- `Domain\Gacha\Services\Draw\StandardGachaDrawService` - 通常ガシャDrawService（StepupGachaDrawServiceの兄弟クラス）
- `Domain\Gacha\UseCases\GachaDrawUseCase` - ガシャ実行UseCase
- `Domain\Gacha\Factories\GachaServiceFactory` - DrawServiceの選択ロジック

**参考になるドメイン設計:**
- `Domain\Gacha` - 既存のガシャドメイン全体の構造
- `Domain\Resource\Mst\Repositories\OprGachaRepository` - マスタデータ取得パターン
- `Domain\Resource\Entities\Rewards\BaseReward` - 報酬Entityの基底クラス（StepupGachaStepRewardが継承）

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

### 9.3 実装時のTips

**トランザクション制御:**
- ガシャ実行処理は必ずトランザクション内で完結させる
- `DB::transaction()` を使用し、例外発生時は自動ロールバック
- UsrGachaInterfaceのsetter経由でデータ更新し、save()はUseCase層で一括実行

**N+1問題の回避:**
- ステップ設定は `getListByOprGachaId()` で一括取得
- 賞品情報は `getListByPrizeGroupId()` で一括取得

**エラーハンドリング:**
- バリデーションエラーは早期にthrow（コスト消費前）
- ユーザー向けエラーメッセージは分かりやすく

**セキュリティ:**
- コスト整合性チェックは必須（チート対策）
- サーバー側の状態を絶対的な真実として扱う
- 初回無料は loopCount === 0 の場合のみ有効

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
   - `opr_gachas.gacha_type` に 'Stepup' 追加
   - `opr_stepup_gachas`, `opr_stepup_gacha_steps` 作成
   - `opr_stepup_gacha_step_rewards` 作成
   - `usr_gachas` にカラム追加（`current_step_number`, `loop_count`）
   - `log_gacha_actions` にカラム追加（`step_number`, `loop_count`）

2. **Enum追加**
   - `GachaType::STEPUP = 'Stepup'` 追加

3. **Model/Entity/Repository拡張**（フェーズ1）
   - `OprStepupGacha`, `OprStepupGachaStep`, `OprStepupGachaStepReward` の作成
   - `UsrGacha`, `LogGachaAction` の拡張（カラム追加対応）
   - 対応するEntity、Repository作成・拡張

4. **Service作成**（フェーズ2）
   - `StepupGachaService` 実装（initializeAndValidate, validateCost, progressStep, getLotteryBox, addStepRewards等）
   - `StepupGachaDrawService` 実装（GachaDrawService継承）
   - `GachaServiceFactory` 実装

5. **UseCase拡張**（フェーズ2）
   - `GachaDrawUseCase::exec()` にcurrentStepNumberパラメータ追加、Factory経由でDrawService取得
   - `GachaPrizeUseCase::exec()` にステップ別排出率対応追加

6. **Response拡張**（フェーズ3）
   - `ResponseDataFactory::addUsrGachaData()` に `currentStepNumber`, `loopCount` 追加
   - `ResponseDataFactory::addStepRewardsData()` メソッド追加
   - `GachaResponseFactory` 拡張

7. **テスト実装**
   - ユニットテスト（Service、Repository）
   - 機能テスト（UseCase、API）
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

## 11. 実装完了状況

### 11.1 実装完了した要件

すべての要件（REQ-MD-1 〜 REQ-HI-2）について、実装が完了しています：

- [x] テーブル設計・マイグレーション完了
- [x] Entity/Model実装完了
- [x] Repository実装完了
- [x] StepupGachaService実装完了
- [x] StepupGachaDrawService実装完了
- [x] GachaServiceFactory実装完了
- [x] UseCase拡張完了（GachaDrawUseCase、GachaPrizeUseCase）
- [x] Controller拡張完了（currentStepNumberパラメータ追加）
- [x] レスポンス構造拡張完了
- [x] ガシャ履歴対応完了
- [x] 多言語対応完了（opr_stepup_gacha_steps_i18n）
- [x] gacha_type値統一完了（'StepUp' → 'Stepup'）

### 11.2 実装済みファイル一覧

**新規作成ファイル:**
- `api/app/Domain/Gacha/Enums/GachaType.php` - STEPUP追加
- `api/app/Domain/Gacha/Services/StepupGachaService.php` - 専用Service
- `api/app/Domain/Gacha/Services/Draw/StepupGachaDrawService.php` - 専用DrawService
- `api/app/Domain/Gacha/Factories/GachaServiceFactory.php` - Factory
- `api/app/Domain/Gacha/Entities/StepupGachaState.php` - 状態Entity
- `api/app/Domain/Resource/Mst/Models/OprStepupGacha.php` - Model
- `api/app/Domain/Resource/Mst/Models/OprStepupGachaStep.php` - Model
- `api/app/Domain/Resource/Mst/Models/OprStepupGachaStepReward.php` - Model
- `api/app/Domain/Resource/Mst/Entities/OprStepupGachaEntity.php` - Entity
- `api/app/Domain/Resource/Mst/Entities/OprStepupGachaStepEntity.php` - Entity
- `api/app/Domain/Resource/Mst/Entities/OprStepupGachaStepRewardEntity.php` - Entity
- `api/app/Domain/Resource/Mst/Repositories/OprStepupGachaRepository.php` - Repository
- `api/app/Domain/Resource/Mst/Repositories/OprStepupGachaStepRepository.php` - Repository
- `api/app/Domain/Resource/Mst/Repositories/OprStepupGachaStepRewardRepository.php` - Repository
- `api/app/Domain/Resource/Entities/Rewards/StepupGachaStepReward.php` - 報酬Entity
- `api/app/Http/Responses/Data/StepupGachaPrizeInfoData.php` - レスポンスデータ
- マイグレーション7ファイル

**改修した既存ファイル:**
- `api/app/Domain/Gacha/UseCases/GachaDrawUseCase.php`
- `api/app/Domain/Gacha/UseCases/GachaPrizeUseCase.php`
- `api/app/Domain/Gacha/Services/GachaService.php`
- `api/app/Domain/Gacha/Services/GachaLogService.php`
- `api/app/Domain/Gacha/Models/UsrGachaInterface.php`
- `api/app/Domain/Gacha/Models/ILogGachaAction.php`
- `api/app/Domain/Gacha/Entities/GachaDrawRequest.php`
- `api/app/Domain/Gacha/Entities/GachaDrawResult.php`
- `api/app/Domain/Gacha/Entities/GachaHistory.php`
- `api/app/Http/Controllers/GachaController.php`
- `api/app/Http/Responses/ResultData/GachaDrawResultData.php`
- `api/app/Http/Responses/ResultData/GachaPrizeResultData.php`
- `api/app/Http/ResponseFactories/ResponseDataFactory.php`
- `api/app/Http/ResponseFactories/GachaResponseFactory.php`

---

**実装状況: 完了**


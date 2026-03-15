# サーバーAPI機能要件実装設計

## 1. ドキュメント情報
- **対象機能:** キャラグレード5到達原画
- **作成日:** 2026-01-22
- **参照ドキュメント:**
  - 05_サーバーAPI要件書.md
  - 07_サーバーAPI設計書.md
  - APIコーディング規約: @docs/01_project/coding-standards/api-coding-standards.md
  - マスタデータ配信機構: @docs/01_project/architecture/マスタデータ配信機構.md

## 2. 実装設計概要

### 2.1 実装方針

#### 基本方針
- 既存のグレードアップ処理（UnitGradeUpService）に原画付与ロジックを追加する形で実装
- RewardDelegatorパターンを活用し、既存の報酬付与システムと整合性を保つ
- トランザクション管理は既存のUseCase層で実施
- 新規API追加ではなく、既存API（POST /unit/grade_up、GET /game/update_and_fetch）の拡張と新規エンドポイント（POST /unit/receive_grade_up_reward）の追加

#### 既存実装との整合性
- `RewardDelegator` → `ArtworkSendService` → `EncyclopediaDelegator::grantArtworksWithFragments` の既存フローを利用
- `UsrModelDiffGetService` により、レスポンスに変更されたユーザーデータ（原画、フラグメント）を自動的に含める
- ミッショントリガー送信は `EncyclopediaMissionTriggerService::sendFirstArtworkCompleteTrigger` を利用
- 重複原画のコイン変換は `ArtworkConvertService::convertDuplicatedArtworkToCoin` を利用

#### 実装時の重要な判断基準
- マスタデータ `mst_unit_grade_up_rewards` に設定がある場合のみ原画を付与
- 原画設定がないキャラのグレードアップは従来通りの処理のみ実行
- 既存ユーザー対応は新規エンドポイント `/unit/receive_grade_up_reward` で実施
- `usr_unit.last_reward_grade_level` フラグにより、演出表示制御と重複付与を防止

### 2.2 実装の全体像

#### 新規追加されるコンポーネント

**マスタデータテーブル:**
- `mst_unit_grade_up_rewards`: キャラと原画のマッピング情報

**ユーザーデータテーブル:**
- `usr_units` に `last_reward_grade_level` カラムを追加

**API:**
- `POST /unit/receive_grade_up_reward`: 既存ユーザー向けの報酬受け取りAPI（新規）

**Domain層:**
- `UnitGradeUpRewardService`: グレードアップ報酬に関するビジネスロジック（新規）
- `MstUnitGradeUpRewardRepository`: mst_unit_grade_up_rewardsへのアクセス（新規）

- `UnitReceiveGradeUpRewardUseCase`: 既存ユーザーへの報酬付与UseCase（新規）

#### 既存コンポーネントへの影響範囲

**変更が必要な既存ファイル:**
- `UnitGradeUpService`: グレードアップ時の原画付与ロジックを追加
- `UnitGradeUpUseCase`: （変更不要の可能性が高いが、レスポンス確認が必要）
- `UsrUnitModel`: `last_reward_grade_level` フィールドの追加
- `UsrUnitEntity`: `last_reward_grade_level` プロパティの追加
- `GameUpdateAndFetchController` および対応するUseCase: （変更不要、UsrModelDiffGetServiceが自動的に新フィールドを含める）

**影響を受けるAPI:**
- `POST /unit/grade_up`: レスポンスに原画情報が追加される（後方互換性あり）
- `GET /game/update_and_fetch`: ユニット情報に `last_reward_grade_level` フィールドが追加される（後方互換性あり）

#### アーキテクチャ上の考慮点

- **レイヤーの責務分離:**
  - Controller層: リクエストのバリデーションとレスポンス生成
  - UseCase層: トランザクション管理とユースケース全体の流れ制御
  - Service層: ビジネスロジックの実装
  - Repository層: データベースアクセス

- **Delegatorパターン:**
  - EncyclopediaDelegatorを通じて原画付与を実施
  - RewardDelegatorを通じて報酬システムと統合

- **トランザクション管理:**
  - グレードアップ、原画付与、ログ記録を同一トランザクション内で実行
  - UseCase層の `applyUserTransactionChanges()` で一括コミット

- **エラーハンドリング:**
  - GameException を使用して統一的なエラーハンドリング
  - マスタデータ不整合時の適切なエラーメッセージ

## 3. 機能要件別実装設計

### 3.1 マスタデータ管理

#### 要件 MST-1: キャラと報酬のマッピング情報管理

##### 3.1.1 要件概要
- **要件ID:** REQ-MST-1
- **実現内容:** キャラID、グレードレベル、報酬情報（resource_type, resource_id, resource_amount）の紐付け情報を管理し、クライアントがマスタデータとして参照できるようにする（現状はArtworkのみ対応）

##### 3.1.2 API設計

**新規API追加 / 既存API改修:**
- [x] 新規マスタテーブル追加（API変更なし、マスタデータ配信機構で自動配信）
- [ ] 既存API改修

**マスタデータ配信:**
- マスタデータ配信機構により、S3経由でクライアントに自動配信される
- game/version APIでマスタデータのハッシュ値とS3パスを返却
- サーバーAPIではマスタデータの参照のみ行う

##### 3.1.3 ドメイン設計

**新規ドメイン追加 / 既存ドメイン改修:**
- [x] 新規ドメイン追加

**対象ドメイン:**
- ドメイン分類: Resource / Mst
- ドメイン名: `Domain\Resource\Mst\MstUnitGradeUpReward`

**ファイル構成:**

*新規作成が必要なファイル:*
- [x] `api/app/Domain/Resource/Mst/Models/MstUnitGradeUpReward.php` - Eloquentモデル
- [x] `api/app/Domain/Resource/Mst/Repositories/MstUnitGradeUpRewardRepository.php` - リポジトリ
- [x] `api/app/Domain/Resource/Mst/Entities/MstUnitGradeUpRewardEntity.php` - エンティティ（必要に応じて）

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| MstUnitGradeUpReward | - | Eloquentモデル | mst_unit_grade_up_rewardsテーブルへのアクセス |
| MstUnitGradeUpRewardRepository | getByMstUnitIdAndGradeLevel() | 特定キャラ・グレードのマッピング取得 | グレードアップ時の原画付与判定で使用 |
| MstUnitGradeUpRewardRepository | existsByMstUnitIdAndGradeLevel() | 特定キャラ・グレードのマッピング存在チェック | - |

**Entity設計:**
- Entityタイプ: Eloquent Model（Entityは必要に応じて作成）
- 主要プロパティ: `id`, `mst_unit_id`, `resource_type`, `resource_id`, `resource_amount`, `grade_level`, `release_key`
- 既存Entityとの関係: `MstUnit` を参照、resource_typeに応じて異なるマスタを参照（現状: Artworkのみで `MstArtwork` を参照）

##### 3.1.4 DB設計

**テーブル構造追加 / テーブル構造変更:**
- [x] テーブル構造追加（新規テーブル作成）

**新規テーブル作成の場合:**

*テーブル名:* `mst_unit_grade_up_rewards`

*CREATE TABLE文:*
```sql
CREATE TABLE mst_unit_grade_up_rewards (
    id VARCHAR(255) NOT NULL COMMENT 'マスタID',
    mst_unit_id VARCHAR(255) NOT NULL COMMENT 'キャラID（mst_units.id）',
    resource_type VARCHAR(255) NOT NULL COMMENT '報酬タイプ（Artwork, Coin, Itemなど）',
    resource_id VARCHAR(255) NOT NULL COMMENT '報酬ID（resource_typeに応じたマスタID）',
    resource_amount INT UNSIGNED NOT NULL DEFAULT 1 COMMENT '報酬数量',
    grade_level INT UNSIGNED NOT NULL COMMENT '報酬獲得可能グレードレベル',
    release_key VARCHAR(255) NULL COMMENT 'リリース制御用キー',
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) COMMENT '作成日時',
    updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6) COMMENT '更新日時',
    PRIMARY KEY (id),
    UNIQUE KEY uk_mst_unit_id_grade_level (mst_unit_id, grade_level),
    INDEX idx_mst_unit_id (mst_unit_id),
    INDEX idx_grade_level (grade_level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='キャラグレードアップ報酬マスタ';
```

*カラム説明:*
| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-----|------|-----------|------|
| id | VARCHAR(255) | NOT NULL | - | マスタID（UUID） |
| mst_unit_id | VARCHAR(255) | NOT NULL | - | キャラID（mst_units.id） |
| resource_type | VARCHAR(255) | NOT NULL | - | 報酬タイプ（Artwork, Coin, Itemなど。現状はArtworkのみ） |
| resource_id | VARCHAR(255) | NOT NULL | - | 報酬ID（resource_typeに応じたマスタID。Artworkの場合はmst_artworks.id） |
| resource_amount | INT UNSIGNED | NOT NULL | 1 | 報酬数量 |
| grade_level | INT UNSIGNED | NOT NULL | - | 報酬獲得可能グレードレベル（リリース時は5） |
| release_key | VARCHAR(255) | NULL | NULL | リリース制御用キー |
| created_at | DATETIME(6) | NOT NULL | CURRENT_TIMESTAMP(6) | 作成日時 |
| updated_at | DATETIME(6) | NOT NULL | CURRENT_TIMESTAMP(6) | 更新日時 |

*制約説明:*
- `UNIQUE KEY uk_mst_unit_id_grade_level`: 同一キャラの同一グレードに複数の報酬が設定されることを防止
- `INDEX idx_mst_unit_id`: キャラIDによる検索を高速化
- `INDEX idx_grade_level`: グレードレベルによる検索を高速化

##### 3.1.5 エラーハンドリング

**エラーコード定義:**

| エラーコード | エラー名 | 発生条件 | ユーザーへのメッセージ | 対処方法 |
|-------------|---------|---------|---------------------|---------|
| ErrorCode::MST_NOT_FOUND | MST_NOT_FOUND | マスタデータが見つからない | マスターデータが見つかりません | マスタデータの存在チェック |

**エラーハンドリングの実装方針:**
- マスタデータが見つからない場合は報酬付与をスキップ（エラーにしない）
- release_keyによるフィルタリングを実施

##### 3.1.6 実装上の注意事項

**パフォーマンス考慮点:**
- マスタデータは頻繁に参照されるため、キャッシュを活用
- UNIQUE制約とINDEXにより検索パフォーマンスを確保

**セキュリティ考慮点:**
- マスタデータの改ざん防止（管理ツール経由でのみ更新）
- release_keyによるリリース制御

**データ整合性:**
- mst_unit_idとresource_idの外部キー制約（論理的）
- resource_idはresource_typeに応じたマスタIDを参照（現状: Artworkのみmst_artworks.idを参照）
- UNIQUE制約により重複設定を防止

**既存実装との整合性:**
- マスタデータ配信機構を利用し、他のマスタテーブルと同様にS3経由で配信
- game/version APIで自動的にハッシュ値とパスが返却される

**マスタデータに関する考慮点:**
- マスタデータ配信機構を参照し、S3経由でクライアントに配信
- サーバーAPIではmst_unit_grade_up_rewardsテーブルからの読み取りのみ実施
- マスタデータの更新は管理ツール経由で行う（本機能のスコープ外）

### 3.2 グレードアップ時の報酬付与

#### 要件 GRADEUP-1: グレードアップ時の報酬付与処理

##### 3.2.1 要件概要
- **要件ID:** REQ-GRADEUP-1
- **実現内容:** キャラがグレード5に到達した際、そのキャラに紐づく報酬を自動的に付与する（現状は原画のみ対応）

##### 3.2.2 API設計

**新規API追加 / 既存API改修:**
- [ ] 新規API追加
- [x] 既存API改修

**対象エンドポイント:**
- エンドポイント: `/unit/grade_up`
- HTTPメソッド: POST
- 認証: 必要

**リクエストパラメータ（JSON形式）:**
```json
{
  "usrUnitId": "usr_unit_12345"
}
```

**リクエストパラメータ説明:**
| パラメータ名 | 型 | 必須 | 説明 | バリデーション |
|-------------|-----|------|------|---------------|
| usrUnitId | string | ○ | グレードアップ対象のユニットID | 所持しているユニットであること |

**レスポンス構造（JSON形式）:**
```json
{
  "usrUnits": [
    {
      "id": "usr_unit_12345",
      "mstUnitId": "mst_unit_001",
      "level": 80,
      "gradeLevel": 5,
      "rankLevel": 5,
      "lastRewardGradeLevel": 5
    }
  ],
  "usrArtworks": [
    {
      "id": "usr_artwork_67890",
      "usrUserId": "usr_user_001",
      "mstArtworkId": "mst_artwork_001",
      "isNewEncyclopedia": 1
    }
  ],
  "usrArtworkFragments": [
    {
      "id": "usr_artwork_fragment_001",
      "usrUserId": "usr_user_001",
      "mstArtworkFragmentId": "mst_artwork_fragment_001",
      "amount": 1
    }
  ],
  "usrItems": [...],
}
```

**レスポンスフィールド説明:**
| フィールド名 | 型 | 説明 |
|-------------|-----|------|
| usrUnits | array | 更新されたユニット情報 |
| usrArtworks | array | 付与された原画情報（原画獲得時のみ） |
| usrArtworkFragments | array | 付与された原画フラグメント情報（原画獲得時のみ） |
| usrItems | array | 消費されたアイテム情報 |

##### 3.2.3 ドメイン設計

**新規ドメイン追加 / 既存ドメイン改修:**
- [x] 新規ドメイン追加
- [x] 既存ドメイン改修

**新規ドメイン:**
- ドメイン分類: Unit
- ドメイン名: `Domain\Unit\Services\UnitGradeUpRewardService`

**既存ドメイン改修:**
- ドメイン分類: Unit
- ドメイン名: `Domain\Unit\Services\UnitGradeUpService`

**ファイル構成:**

*新規作成が必要なファイル:*
- [x] `api/app/Domain/Unit/Services/UnitGradeUpRewardService.php` - グレードアップ報酬に関するビジネスロジック

*改修が必要な既存ファイル:*
- [x] `api/app/Domain/Unit/Services/UnitGradeUpService.php` - グレードアップ時に原画付与処理を呼び出す
- [x] `api/app/Domain/Unit/Models/UsrUnit.php` - `last_reward_grade_level` フィールドの追加
- [x] `api/app/Domain/Resource/Usr/Entities/UsrUnitEntity.php` - `last_reward_grade_level` プロパティの追加

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| UnitGradeUpService | gradeUp() | グレードアップ処理（既存） | 原画付与処理の呼び出しを追加 |
| UnitGradeUpRewardService | grantGradeUpReward() | グレードアップ報酬（原画）の付与処理 | 新規作成 |
| UnitGradeUpRewardService | checkAndGrantGradeUpReward() | 原画付与条件の確認と付与実行 | 新規作成 |
| MstUnitGradeUpRewardRepository | getByMstUnitIdAndGradeLevel() | マッピング情報の取得 | 新規作成 |
| LogUnitGradeUpRewardRepository | create() | ログ記録 | 新規作成 |

**Entity設計:**
- Entityタイプ: Eloquent Model / Entity
- 主要プロパティ（UsrUnit拡張）: `last_reward_grade_level`
- 既存Entityとの関係: RewardDelegator、EncyclopediaDelegatorを利用

##### 3.2.4 DB設計

**テーブル構造追加 / テーブル構造変更:**
- [x] テーブル構造変更（既存テーブル変更: usr_units）

**既存テーブル変更の場合:**

*テーブル名:* `usr_units`

*ALTER TABLE文:*
```sql
ALTER TABLE usr_units
ADD COLUMN last_reward_grade_level INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '最後に報酬を受け取ったグレードレベル(0:未受取、数値:受取済みグレードレベル)' AFTER rank_level;

ALTER TABLE usr_units
ADD INDEX idx_last_reward_grade_level (last_reward_grade_level);
```

*変更内容説明:*
- カラム追加: `last_reward_grade_level` - 最後に報酬を受け取ったグレードレベルを記録
- デフォルト値: `0`（既存レコードは全て未受け取り扱い）
- 判定方法: `last_reward_grade_level < grade_level` で未受け取りを判定

##### 3.2.5 エラーハンドリング

**エラーコード定義:**

| エラーコード | エラー名 | 発生条件 | ユーザーへのメッセージ | 対処方法 |
|-------------|---------|---------|---------------------|---------|
| ErrorCode::UNIT_NOT_FOUND | UNIT_NOT_FOUND | ユニットが見つからない | ユニットが見つかりません | ユニット存在チェック |
| ErrorCode::LACK_OF_RESOURCES | LACK_OF_RESOURCES | 消費リソース不足 | 消費リソースが不足しています | 既存のグレードアップバリデーション |
| ErrorCode::MST_NOT_FOUND | MST_NOT_FOUND | マスターデータが見つからない | マスターデータが見つかりません | マスタデータ存在チェック |

**エラーハンドリングの実装方針:**
- グレードアップ処理中のエラーは全体をロールバック
- 原画付与処理のエラーもグレードアップ全体をロールバック（同一トランザクション）
- マスタデータ不整合時は適切なエラーメッセージを返す

##### 3.2.6 実装上の注意事項

**パフォーマンス考慮点:**
- マスタデータ参照はキャッシュを活用
- 原画付与処理の追加によるレスポンスタイム増加を最小限に抑える（目標: +50ms以内）
- トランザクション内での処理を最適化

**セキュリティ考慮点:**
- 既存のグレードアップバリデーションを継続
- マスタデータの存在チェックによる不正防止
- 重複付与の防止（last_reward_grade_level値）

**データ整合性:**
- グレードアップ、原画付与、ログ記録を同一トランザクション内で実行
- エラー発生時の自動ロールバック
- last_reward_grade_level値の適切な更新

**既存実装との整合性:**
- RewardDelegatorパターンを使用し、既存の報酬付与システムと統合
- UsrModelDiffGetServiceにより、レスポンス形式は既存パターンと一貫
- ミッショントリガー送信は既存の仕組みを利用

#### 要件 GRADEUP-2: 報酬受け取りAPIの追加（既存ユーザー対応）

##### 3.2.1 要件概要
- **要件ID:** REQ-EXISTING-1
- **実現内容:** 既にグレード5のキャラを所持しているユーザーが報酬（原画）を受け取るためのAPI

##### 3.2.2 API設計

**新規API追加 / 既存API改修:**
- [x] 新規API追加
- [ ] 既存API改修

**対象エンドポイント:**
- エンドポイント: `/unit/receive_grade_up_reward`
- HTTPメソッド: POST
- 認証: 必要

**リクエストパラメータ（JSON形式）:**
```json
{
  "usrUnitId": "usr_unit_12345"
}
```

**リクエストパラメータ説明:**
| パラメータ名 | 型 | 必須 | 説明 | バリデーション |
|-------------|-----|------|------|---------------|
| usrUnitId | string | ○ | 報酬受け取り対象のユニットID | 所持しているユニットであること |

**レスポンス構造（JSON形式）:**
```json
{
  "usrUnits": [
    {
      "id": "usr_unit_12345",
      "mstUnitId": "mst_unit_001",
      "level": 80,
      "gradeLevel": 5,
      "rankLevel": 5,
      "lastRewardGradeLevel": 5
    }
  ],
  "usrArtworks": [
    {
      "id": "usr_artwork_67890",
      "usrUserId": "usr_user_001",
      "mstArtworkId": "mst_artwork_001",
      "isNewEncyclopedia": 1
    }
  ],
  "usrArtworkFragments": [...],
  "isNewGradeUpReward": true
}
```

**レスポンスフィールド説明:**
| フィールド名 | 型 | 説明 |
|-------------|-----|------|
| usrUnits | array | 更新されたユニット情報（原画付与時のみ） |
| usrArtworks | array | 付与された原画情報（原画付与時のみ） |
| usrArtworkFragments | array | 付与された原画フラグメント情報（原画付与時のみ） |
| isNewGradeUpReward | boolean | 演出表示フラグ（true: 演出表示、false: 表示不要） |

##### 3.2.3 ドメイン設計

**新規ドメイン追加 / 既存ドメイン改修:**
- [x] 新規ドメイン追加

**対象ドメイン:**
- ドメイン分類: Unit
- ドメイン名: `Domain\Unit\UseCases\UnitReceiveGradeUpRewardUseCase`

**ファイル構成:**

*新規作成が必要なファイル:*
- [x] `api/app/Domain/Unit/UseCases/UnitReceiveGradeUpRewardUseCase.php` - 報酬受け取りUseCase
- [x] `api/app/Domain/Unit/Delegators/UnitDelegator.php` - ユニット関連のDelegator（既存の場合は改修）
- [x] `api/app/Http/Controllers/Api/UnitReceiveGradeUpRewardController.php` - コントローラー
- [x] `api/app/Http/Responses/ResultData/UnitReceiveGradeUpRewardResultData.php` - レスポンスデータ

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| UnitReceiveGradeUpRewardController | __invoke() | リクエスト受付とバリデーション | 新規作成 |
| UnitReceiveGradeUpRewardUseCase | exec() | 報酬受け取り処理の実行 | 新規作成 |
| UnitGradeUpRewardService | checkAndGrantExistingUserReward() | 既存ユーザーへの報酬付与判定と実行 | 新規メソッド追加 |

**Entity設計:**
- Entityタイプ: ResultData
- 主要プロパティ: `usrUnits`, `usrArtworks`, `usrArtworkFragments`, `isNewGradeUpReward`

##### 3.2.4 DB設計

**テーブル構造追加 / テーブル構造変更:**
- [ ] テーブル構造追加
- [ ] テーブル構造変更（既にREQ-GRADEUP-1で対応済み）

##### 3.2.5 エラーハンドリング

**エラーコード定義:**

| エラーコード | エラー名 | 発生条件 | ユーザーへのメッセージ | 対処方法 |
|-------------|---------|---------|---------------------|---------|
| ErrorCode::UNIT_NOT_FOUND | UNIT_NOT_FOUND | ユニットが見つからない | ユニットが見つかりません | ユニット存在チェック |
| ErrorCode::INVALID_PARAMETER | INVALID_PARAMETER | 不正なパラメータ | パラメータが不正です | 条件チェック（エラーにせず成功レスポンス） |

**エラーハンドリングの実装方針:**
- 条件を満たさない場合もエラーにせず、成功レスポンスを返す（冪等性の保証）
- isNewGradeUpReward: false を返して演出表示しない

##### 3.2.6 実装上の注意事項

**パフォーマンス考慮点:**
- ユニット詳細画面表示時に呼び出されるため、レスポンスタイムを最小限に（目標: 50ms以内）
- マスタデータ参照はキャッシュを活用

**セキュリティ考慮点:**
- 冪等性の保証（既に受け取り済みの場合も安全に処理）
- 不正なリクエストを検出

**データ整合性:**
- トランザクション内で処理を実行
- last_reward_grade_level値の適切な更新

**既存実装との整合性:**
- UnitGradeUpRewardServiceを共通で使用
- レスポンス形式は既存パターンと一貫

## 4. 実装の優先順位と依存関係

### 4.1 実装の段階分け

**フェーズ1: 基盤実装（1〜2日）**
1. REQ-MST-1: マスタデータテーブルの作成
   - マイグレーションファイル作成
   - Eloquentモデル、リポジトリ作成
2. usr_unitsテーブルへのカラム追加
   - マイグレーションファイル作成
   - UsrUnitModel、UsrUnitEntityの更新
3. log_unit_grade_up_rewardsテーブルの作成
   - マイグレーションファイル作成
   - Eloquentモデル、リポジトリ作成

**フェーズ2: コア機能実装（2〜3日）**
1. REQ-GRADEUP-1: グレードアップ時の原画付与処理
   - UnitGradeUpRewardService作成
   - UnitGradeUpServiceへの統合
   - テスト実装
2. REQ-EXISTING-1: 既存ユーザー向け報酬受け取りAPI
   - UnitReceiveGradeUpRewardUseCase作成
   - コントローラー作成
   - テスト実装

**フェーズ3: 統合テストと調整（1〜2日）**
1. 統合テスト実装
2. パフォーマンステスト
3. エラーハンドリングの確認
4. ドキュメント更新

### 4.2 依存関係マップ

```
REQ-MST-1 (マスタデータ基盤)
  ↓
REQ-GRADEUP-1 (グレードアップ時の原画付与)
  ├→ usr_unitsテーブル変更
  └→ log_unit_grade_up_rewardsテーブル作成
       ↓
REQ-EXISTING-1 (既存ユーザー向けAPI)
```

### 4.3 実装時の注意点

- フェーズ1を完了してからフェーズ2に進む
- マスタデータテーブルが存在しないと、原画付与判定ができない
- usr_unitsテーブルへのカラム追加は、マイグレーション実行時にデフォルト値（1）が設定される
- 各フェーズ内でも依存関係を考慮した順序で実装する
- テストは各要件実装後に都度実施する

## 5. テスト設計概要

### 5.1 ユニットテスト

**テスト対象:**
- UnitGradeUpRewardService
- UnitGradeUpService（改修部分）
- MstUnitGradeUpRewardRepository
- LogUnitGradeUpRewardRepository

**テストケース例:**
| テスト対象 | テストケース | 期待結果 |
|-----------|------------|---------|
| UnitGradeUpRewardService::checkAndGrantGradeUpReward() | グレード5到達、原画設定あり | 原画が付与される |
| UnitGradeUpRewardService::checkAndGrantGradeUpReward() | グレード5到達、原画設定なし | 原画付与されない |
| UnitGradeUpRewardService::checkAndGrantGradeUpReward() | グレード4到達 | 原画付与されない |
| UnitGradeUpRewardService::checkAndGrantGradeUpReward() | 重複原画 | コインに変換される |
| MstUnitGradeUpRewardRepository::getByMstUnitIdAndGradeLevel() | 正常値を入力 | マッピング情報が返る |
| MstUnitGradeUpRewardRepository::getByMstUnitIdAndGradeLevel() | 存在しないマッピング | nullが返る |

### 5.2 機能テスト

**テスト対象:**
- POST /unit/grade_up（原画付与機能追加後）
- POST /unit/receive_grade_up_reward（新規API）
- GET /game/update_and_fetch（last_reward_grade_levelフィールド追加後）

**テストケース例:**
| API | テストケース | 期待結果 |
|-----|------------|---------|
| POST /unit/grade_up | グレード4→5、原画設定あり | 200 OK、原画が付与される |
| POST /unit/grade_up | グレード4→5、原画設定なし | 200 OK、従来通りの処理 |
| POST /unit/grade_up | グレード3→4 | 200 OK、従来通りの処理 |
| POST /unit/grade_up | 重複原画 | 200 OK、コインに変換される |
| POST /unit/receive_grade_up_reward | グレード5、未受け取り、原画設定あり | 200 OK、原画が付与される |
| POST /unit/receive_grade_up_reward | グレード5、受け取り済み | 200 OK、isNewGradeUpReward: false |
| POST /unit/receive_grade_up_reward | グレード4 | 200 OK、isNewGradeUpReward: false |
| GET /game/update_and_fetch | - | 200 OK、last_reward_grade_levelフィールドが含まれる |

### 5.3 シナリオテスト

**テストシナリオ例:**
1. ユーザーがグレード4→5にグレードアップする
2. 原画が自動的に付与される
3. ミッショントリガーが送信される
4. データベースにログが記録される
5. レスポンスに原画情報が含まれる

## 6. マイグレーション計画

### 6.1 マイグレーションファイル一覧

**新規作成が必要なマイグレーション:**
- [x] `YYYY_MM_DD_HHMMSS_create_mst_unit_grade_up_rewards_table.php` - mst_unit_grade_up_rewardsテーブル作成
- [x] `YYYY_MM_DD_HHMMSS_add_last_reward_grade_level_to_usr_units_table.php` - usr_unitsテーブルへのカラム追加
- [x] `YYYY_MM_DD_HHMMSS_create_log_unit_grade_up_rewards_table.php` - log_unit_grade_up_rewardsテーブル作成

### 6.2 マイグレーション実行順序

1. mst_unit_grade_up_rewardsテーブル作成（マスタデータ基盤）
2. log_unit_grade_up_rewardsテーブル作成（ログテーブル）
3. usr_unitsテーブルへのカラム追加（既存テーブル変更）

### 6.3 ロールバック方針

- 各マイグレーションにはdown()メソッドを実装する
- ロールバック時は逆順で実行（usr_units → log_unit_grade_up_rewards → mst_unit_grade_up_rewards）
- データ損失を防ぐため、本番環境でのロールバックは慎重に実施

## 7. 連携が必要なスキル・ツール

**Claude Code スキル:**
- `api-request-validation`: リクエストパラメータのバリデーション実装
- `domain-layer`: ドメインレイヤーの実装パターン参照
- `api-schema-reference`: glow-schema YAMLとの整合性確認
- `api-response`: レスポンス構造の実装
- `migration`: マイグレーションファイルの作成・実行
- `api-test-implementation`: テストコードの実装

**使用タイミング:**
- マスタデータ設計時: `migration`
- ドメイン実装時: `domain-layer`
- API実装時: `api-request-validation`, `api-response`, `api-schema-reference`
- テスト実装時: `api-test-implementation`

## 8. 実装時の判断が必要な事項

### 8.1 技術的な選択肢

**項目: last_reward_grade_level値のデフォルト値**
- 選択肢A: デフォルト値 `0`（未受け取り）
  - メリット: 新規ユーザーも既存ユーザーも同じフローで処理できる
  - デメリット: 既存レコードに対してUPDATE文の実行が必要
- 選択肢B: デフォルト値 `1`（受け取り済み）
  - メリット: 既存レコードへの影響なし、マイグレーションが軽量
  - デメリット: 新規グレードアップ時に `0` → `1` への更新が必要
- 推奨: **選択肢B（デフォルト値 `1`）**
  - 既存レコードへの影響を最小限にし、マイグレーション実行時の負荷を削減
  - 新規グレードアップ時のフラグ更新は通常の処理フロー内で実施可能

### 8.2 仕様の解釈

**項目: release_keyの運用方針**
- 解釈A: 機能全体のリリース制御に使用
  - この解釈の場合の実装: release_keyが特定の値の場合のみ原画付与を有効化
- 解釈B: キャラごとの段階的リリースに使用
  - この解釈の場合の実装: release_keyによりキャラ単位で原画設定を制御
- 確認先: プロジェクトマネージャー、プランナー

## 9. 補足情報

### 9.1 参考にすべき既存実装

**類似機能の実装例:**
- `UnitRankUpService`: ランクアップ時の処理パターン
- `UnitLevelUpService`: レベルアップ時の処理パターン
- `RewardDelegator` および `ArtworkSendService`: 報酬付与の実装パターン
- `EncyclopediaDelegator::grantArtworksWithFragments`: 原画とフラグメントの付与処理

**参考になるドメイン設計:**
- `Domain\Unit\Services`: ユニット関連のサービス層設計
- `Domain\Reward`: 報酬システムの設計パターン
- `Domain\Encyclopedia`: 図鑑（原画）システムの設計パターン

**参考になるDB設計:**
- `mst_unit_grade_ups`: グレードアップマスタの設計パターン
- `log_unit_grade_ups`: グレードアップログの設計パターン
- `usr_artworks`, `usr_artwork_fragments`: 原画データの設計パターン

### 9.2 参考ドキュメント

- **APIコーディング規約**: @docs/01_project/coding-standards/api-coding-standards.md
  - 命名規則、アーキテクチャパターン、実装パターンの詳細
- **マスタデータ配信機構**: @docs/01_project/architecture/マスタデータ配信機構.md
  - マスタデータの配信フロー、S3連携、バージョン管理の仕組み

### 9.3 実装時のTips

**トランザクション管理:**
- UseCase層の `applyUserTransactionChanges()` で一括コミット
- エラー発生時は自動的にロールバックされる
- 同一トランザクション内でグレードアップ、原画付与、ログ記録を実行

**RewardDelegatorの使い方:**
- `addReward()` でReward型のオブジェクトを追加
- `sendRewards()` でまとめて付与処理を実行
- UsrModelDiffGetServiceが自動的に変更差分を収集

**UsrModelDiffGetServiceの活用:**
- レスポンスに含めるデータは自動的に収集される
- 原画、フラグメント、アイテム、ユーザーサマリーなどの変更が自動的に反映される
- 明示的にレスポンスを構築する必要がない

**ミッショントリガー送信:**
- `EncyclopediaMissionTriggerService::sendFirstArtworkCompleteTrigger()` を呼び出す
- 原画が初完成の場合のみトリガー送信（重複時は送信不要）

**パフォーマンスチューニング:**
- マスタデータ参照はキャッシュを活用
- N+1問題を回避するため、必要なデータは一括取得
- トランザクション内での処理を最適化

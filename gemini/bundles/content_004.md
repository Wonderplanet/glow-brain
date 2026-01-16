# glow-brain-gemini 全ソースコード (Part 4)

生成日時: 2026-01-16 17:38:32

---

<!-- FILE: ./マスタデータ/リリース/202512020/作成手順/ミッションマスタデータ作成手順書.md -->
## ./マスタデータ/リリース/202512020/作成手順/ミッションマスタデータ作成手順書.md

```md
# ミッションマスタデータ作成手順書

本手順書は、運営仕様書からミッション関連のマスタデータCSVを作成する際の手順をまとめたものです。

## 目次

1. [概要](#概要)
2. [ミッションの種類とテーブル構成](#ミッションの種類とテーブル構成)
3. [作成手順](#作成手順)
4. [各テーブルの詳細仕様](#各テーブルの詳細仕様)
5. [criterion_type別の設定方法](#criterion_type別の設定方法)
6. [実例](#実例)

---

## 概要

### ミッションマスタデータの構成

GLOWのミッションシステムは、以下のテーブルで構成されています。

| テーブル名 | 役割 |
|-----------|------|
| **MstMissionEvent** | イベントミッションの本体 |
| **MstMissionEventI18n** | イベントミッションの多言語テキスト |
| **MstMissionEventDependency** | イベントミッションの依存関係(段階的解放) |
| **MstMissionEventDailyBonus** | ログインボーナス(日別報酬) |
| **MstMissionEventDailyBonusSchedule** | ログインボーナスのスケジュール |
| **MstMissionAchievement** | アチーブメントミッション(恒常ミッション) |
| **MstMissionAchievementI18n** | アチーブメントミッションの多言語テキスト |
| **MstMissionAchievementDependency** | アチーブメントミッションの依存関係 |
| **MstMissionLimitedTerm** | 期間限定ミッション |
| **MstMissionLimitedTermI18n** | 期間限定ミッションの多言語テキスト |
| **MstMissionReward** | ミッション報酬の定義 |

---

## ミッションの種類とテーブル構成

### 1. イベントミッション (MstMissionEvent)

**特徴:**
- イベント期間中のみ有効
- mst_event_idと紐づく
- 段階的解放(dependency)が可能

**使用テーブル:**
- `MstMissionEvent` (本体)
- `MstMissionEventI18n` (説明文)
- `MstMissionEventDependency` (依存関係)
- `MstMissionReward` (報酬)

### 2. アチーブメントミッション (MstMissionAchievement)

**特徴:**
- 恒常ミッション
- 期限なし(またはstart_at/end_atで制御)
- 達成型の目標設定

**使用テーブル:**
- `MstMissionAchievement` (本体)
- `MstMissionAchievementI18n` (説明文)
- `MstMissionAchievementDependency` (依存関係)
- `MstMissionReward` (報酬)

### 3. 期間限定ミッション (MstMissionLimitedTerm)

**特徴:**
- 短期間の期間限定ミッション
- start_at/end_atで期間を明示的に指定
- イベントとは独立

**使用テーブル:**
- `MstMissionLimitedTerm` (本体)
- `MstMissionLimitedTermI18n` (説明文)
- `MstMissionReward` (報酬)

### 4. ログインボーナス (MstMissionEventDailyBonus)

**特徴:**
- 日別のログインボーナス
- イベント期間中のログイン日数に応じて報酬獲得
- スケジュールで期間を制御

**使用テーブル:**
- `MstMissionEventDailyBonus` (日別報酬)
- `MstMissionEventDailyBonusSchedule` (スケジュール)
- `MstMissionReward` (報酬)

---

## 作成手順

### STEP 1: 運営仕様書の確認

#### 1.1 ミッション仕様シートの確認

運営仕様書の「ミッション」シートを開き、以下の情報を確認します。

**確認項目:**
- 施策名称
- 開催期間
- ミッション内容(達成条件)
- 報酬内容

#### 1.2 ログインボーナス仕様の確認

「施策」シートの「ログインボーナス」セクションを確認します。

**確認項目:**
- ログイン日数と報酬の対応表
- 開催期間

### STEP 2: ミッション種別の判定

仕様書の情報から、どのテーブルに該当するかを判定します。

| 判定条件 | 使用テーブル |
|---------|-------------|
| イベントに紐づくミッション | MstMissionEvent |
| 恒常ミッション(期限なし) | MstMissionAchievement |
| 短期間の期間限定ミッション | MstMissionLimitedTerm |
| ログインボーナス | MstMissionEventDailyBonus |

### STEP 3: 各CSVファイルの作成

#### 3.1 IDの命名規則

**イベントミッション:**
```
event_{イベントID}_{連番}
例: event_osh_00001_1, event_osh_00001_2
```

**アチーブメントミッション:**
```
achievement_{カテゴリ番号}_{連番}
例: achievement_2_101, achievement_2_102
```

**期間限定ミッション:**
```
limited_term_{連番}
例: limited_term_33, limited_term_34
```

**ログインボーナス:**
```
event_{イベントID}_daily_bonus_{日数(2桁)}
例: event_osh_00001_daily_bonus_01
```

#### 3.2 報酬グループIDの命名規則

報酬グループIDは、ミッションIDに対応させます。

```
{ミッションの種別}_{識別子}_{連番}
例: osh_00001_event_reward_1
    achievement_2_101
    osh_00001_limited_term_1
```

### STEP 4: criterion_typeとcriterion_valueの設定

ミッション達成条件を設定します。詳細は[criterion_type別の設定方法](#criterion_type別の設定方法)を参照。

**基本的な流れ:**

1. ミッション内容から適切な`criterion_type`を選択
2. `criterion_type`に応じて`criterion_value`を設定
3. 達成回数を`criterion_count`に設定

### STEP 5: 報酬の設定

`MstMissionReward.csv`に報酬を設定します。

**設定項目:**
- `group_id`: 報酬グループID
- `resource_type`: 報酬の種類(後述の利用可能な値を参照)
- `resource_id`: リソースの具体的なID(アイテムIDなど)
- `resource_amount`: 報酬の数量

#### 5.1 resource_typeに設定できる値

ミッション報酬の`resource_type`には、以下の値のみ設定できます。

**利用可能なresource_type:**

| resource_type | 日本語名 | resource_id | 説明 |
|--------------|---------|-------------|------|
| `FreeDiamond` | 無償プリズム | 不要(空文字) | 無償のダイヤ(プリズム) |
| `Coin` | コイン | 不要(空文字) | ゲーム内通貨 |
| `Exp` | 経験値 | 不要(空文字) | ユニット経験値 |
| `Item` | アイテム | **必須** | アイテムマスタのID |
| `Emblem` | エンブレム | **必須** | エンブレムマスタのID |
| `Unit` | キャラ | **必須** | ユニットマスタのID |

**重要な注意事項:**
- 上記以外の値(例: `PaidDiamond`, `Stamina`, `Artwork`)は設定できません
- テーブル定義で`enum('Exp','Coin','FreeDiamond','Item','Emblem','Unit')`として制約されています
- `resource_id`が**必須**なのは: `Item`, `Emblem`, `Unit`
- `resource_id`が**不要**(空文字を設定)なのは: `FreeDiamond`, `Coin`, `Exp`

**参照:**
- 実装: `projects/glow-server/api/app/Domain/Resource/Enums/RewardType.php`
- テーブル定義: `projects/glow-server/api/database/schema/exports/master_tables_schema.json`

#### 5.2 報酬リソースの存在チェック

報酬を設定する際は、**必ず指定したリソースが実際に存在するか確認**してください。存在しないリソースIDを指定すると、ゲーム内でエラーが発生します。

**チェック対象:**
- `resource_type`が`Item`, `Emblem`, `Unit`の場合は必須
- `resource_type`が`FreeDiamond`, `Coin`, `Exp`の場合はチェック不要(システムリソース)

**確認先:**

リソースは2箇所に存在する可能性があります。両方を確認する必要があります。

1. **新規マスタデータ**: `マスタデータ/リリース/{release_key}/tables/`
   - 今回のリリースで追加される新しいリソース

2. **既存マスタデータ**: `projects/glow-masterdata/`
   - 過去のリリースで既に存在するリソース
   - こちらのファイルには既存の全データが含まれています

**resource_typeとマスタデータの対応表:**

| resource_type | 確認すべきマスタデータ | ファイル名 | 備考 |
|--------------|---------------------|-----------|------|
| `Item` | MstItem | `MstItem.csv` | resource_id必須 |
| `Emblem` | MstEmblem | `MstEmblem.csv` | resource_id必須 |
| `Unit` | MstUnit | `MstUnit.csv` | resource_id必須 |
| `FreeDiamond` | (チェック不要) | - | システムリソース |
| `Coin` | (チェック不要) | - | システムリソース |
| `Exp` | (チェック不要) | - | システムリソース |

**確認手順:**

1. 報酬のresource_typeを確認
2. 上記の対応表から確認すべきマスタデータファイルを特定
3. 新規マスタデータを確認
   ```bash
   # 例: アイテムID "ticket_glo_10001" が存在するか確認
   grep "ticket_glo_10001" マスタデータ/リリース/202512020/tables/MstItem.csv
   ```
4. 新規マスタデータに無い場合、既存マスタデータを確認
   ```bash
   # 既存マスタデータで確認
   grep "ticket_glo_10001" projects/glow-masterdata/MstItem.csv
   ```
5. どちらにも存在しない場合、resource_idが間違っているか、該当リソースのマスタデータを先に作成する必要があります

**実例:**

報酬に`ticket_glo_10001`(賀正ガシャチケット)を設定する場合:

```csv
ENABLE,id,release_key,group_id,resource_type,resource_id,resource_amount,sort_order
e,mission_reward_531,202512020,osh_00001_event_reward_1,Item,ticket_glo_10001,2,1
```

この場合の確認手順:
1. `resource_type`が`Item`なので、`MstItem.csv`を確認
2. 新規マスタデータを確認:
   ```bash
   grep "ticket_glo_10001" マスタデータ/リリース/202512020/tables/MstItem.csv
   ```
3. 見つからない場合、既存マスタデータを確認:
   ```bash
   grep "ticket_glo_10001" projects/glow-masterdata/MstItem.csv
   ```
4. どちらかに存在すればOK

### STEP 6: 依存関係の設定(必要な場合)

段階的に解放するミッションの場合、依存関係を設定します。

**MstMissionEventDependency:**
- `group_id`: グループ化するミッションのID
- `mst_mission_event_id`: 依存するミッションのID
- `unlock_order`: 解放順序

### STEP 7: 多言語テキストの設定

各ミッションの説明文を設定します。

**I18nテーブル:**
- `mst_mission_event_id`: 対象ミッションID
- `language`: 言語コード(ja, en, etc.)
- `description`: ミッション説明文

---

## 各テーブルの詳細仕様

### MstMissionEvent

イベントミッションの本体テーブル。

| カラム名 | 型 | 必須 | 説明 | 設定例 |
|---------|---|------|------|--------|
| ENABLE | string | ○ | 有効フラグ | `e` (有効) |
| id | string | ○ | ミッションID | `event_osh_00001_1` |
| release_key | string | ○ | リリースキー | `202512020` |
| mst_event_id | string | ○ | イベントID | `event_osh_00001` |
| criterion_type | string | ○ | 達成条件の種類 | `StageClearCount` |
| criterion_value | string | △ | 達成条件の値 | `gasho_001` (ガチャID) |
| criterion_count | integer | ○ | 達成に必要な回数 | `10` |
| unlock_criterion_type | string | - | 解放条件の種類 | `__NULL__` |
| unlock_criterion_value | string | - | 解放条件の値 | `` |
| unlock_criterion_count | integer | - | 解放条件の回数 | `0` |
| group_key | string | - | グループキー | `` (空文字) |
| mst_mission_reward_group_id | string | ○ | 報酬グループID | `osh_00001_event_reward_1` |
| sort_order | integer | ○ | 表示順序 | `1` |
| destination_scene | string | ○ | 遷移先シーン | `Event`, `Gacha`, `QuestSelect` |

**ENABLE値:**
- `e`: 有効
- `d`: 無効

**destination_scene値:**
- `Event`: イベント画面
- `Gacha`: ガチャ画面
- `QuestSelect`: クエスト選択画面
- `AdventBattle`: 降臨バトル画面

**unlock_criterion_type/value/count:**
- 通常は使用しない
- 解放条件が必要な場合のみ設定
- ほとんどの場合は`__NULL__`と空文字と`0`

### MstMissionEventI18n

イベントミッションの多言語テキスト。

| カラム名 | 型 | 必須 | 説明 | 設定例 |
|---------|---|------|------|--------|
| ENABLE | string | ○ | 有効フラグ | `e` |
| release_key | string | ○ | リリースキー | `202512020` |
| id | string | ○ | I18nID | `event_osh_00001_1_ja` |
| mst_mission_event_id | string | ○ | ミッションID | `event_osh_00001_1` |
| language | string | ○ | 言語コード | `ja` |
| description | string | ○ | ミッション説明文 | `ステージを5回クリアしよう` |

**ID命名規則:**
```
{mst_mission_event_id}_{language}
例: event_osh_00001_1_ja
```

### MstMissionEventDependency

イベントミッションの依存関係(段階的解放)。

| カラム名 | 型 | 必須 | 説明 | 設定例 |
|---------|---|------|------|--------|
| ENABLE | string | ○ | 有効フラグ | `e` |
| id | string | ○ | 依存関係ID | `190` |
| release_key | string | ○ | リリースキー | `202512020` |
| group_id | string | ○ | グループID | `event_osh_00001_1` |
| mst_mission_event_id | string | ○ | ミッションID | `event_osh_00001_1` |
| unlock_order | integer | ○ | 解放順序 | `1` |

**使い方:**
- 同じ`group_id`を持つミッションが段階的に解放される
- `unlock_order`が小さい順に解放
- 前のミッションをクリアすると次が解放される

**実例:**
```csv
id,release_key,group_id,mst_mission_event_id,unlock_order
190,202512020,event_osh_00001_1,event_osh_00001_1,1
191,202512020,event_osh_00001_1,event_osh_00001_2,2
192,202512020,event_osh_00001_1,event_osh_00001_3,3
```
この場合:
1. `event_osh_00001_1`をクリア → `event_osh_00001_2`が解放
2. `event_osh_00001_2`をクリア → `event_osh_00001_3`が解放

### MstMissionEventDailyBonus

ログインボーナスの日別報酬。

| カラム名 | 型 | 必須 | 説明 | 設定例 |
|---------|---|------|------|--------|
| ENABLE | string | ○ | 有効フラグ | `e` |
| id | string | ○ | ボーナスID | `event_osh_00001_daily_bonus_01` |
| release_key | string | ○ | リリースキー | `202512020` |
| mst_mission_event_daily_bonus_schedule_id | string | ○ | スケジュールID | `event_osh_00001_daily_bonus` |
| login_day_count | integer | ○ | ログイン日数 | `1` |
| mst_mission_reward_group_id | string | ○ | 報酬グループID | `event_osh_00001_daily_bonus_01` |
| sort_order | integer | ○ | 表示順序 | `1` |

### MstMissionEventDailyBonusSchedule

ログインボーナスのスケジュール。

| カラム名 | 型 | 必須 | 説明 | 設定例 |
|---------|---|------|------|--------|
| ENABLE | string | ○ | 有効フラグ | `e` |
| id | string | ○ | スケジュールID | `event_osh_00001_daily_bonus` |
| release_key | string | ○ | リリースキー | `202512020` |
| mst_event_id | string | ○ | イベントID | `event_osh_00001` |
| start_at | datetime | ○ | 開始日時 | `2026-01-01 00:00:00` |
| end_at | datetime | ○ | 終了日時 | `2026-01-16 03:59:59` |

### MstMissionAchievement

アチーブメントミッション(恒常ミッション)。

| カラム名 | 型 | 必須 | 説明 | 設定例 |
|---------|---|------|------|--------|
| ENABLE | string | ○ | 有効フラグ | `e` |
| id | string | ○ | ミッションID | `achievement_2_101` |
| release_key | string | ○ | リリースキー | `202512020` |
| criterion_type | string | ○ | 達成条件の種類 | `SpecificQuestClear` |
| criterion_value | string | △ | 達成条件の値 | `quest_main_osh_normal_17` |
| criterion_count | integer | ○ | 達成に必要な回数 | `1` |
| unlock_criterion_type | string | - | 解放条件の種類 | `__NULL__` |
| unlock_criterion_value | string | - | 解放条件の値 | `` |
| unlock_criterion_count | integer | - | 解放条件の回数 | `0` |
| group_key | string | - | グループキー | `` |
| mst_mission_reward_group_id | string | ○ | 報酬グループID | `achievement_2_101` |
| sort_order | integer | ○ | 表示順序 | `101` |
| destination_scene | string | ○ | 遷移先シーン | `QuestSelect` |

**MstMissionEventとの違い:**
- `mst_event_id`が無い
- 期限が無い(または別途start_at/end_atで制御)
- 恒常的な達成目標

### MstMissionLimitedTerm

期間限定ミッション。

| カラム名 | 型 | 必須 | 説明 | 設定例 |
|---------|---|------|------|--------|
| ENABLE | string | ○ | 有効フラグ | `e` |
| id | string | ○ | ミッションID | `limited_term_33` |
| release_key | string | ○ | リリースキー | `202512020` |
| progress_group_key | string | ○ | 進捗グループキー | `group9` |
| criterion_type | string | ○ | 達成条件の種類 | `AdventBattleChallengeCount` |
| criterion_value | string | △ | 達成条件の値 | `` |
| criterion_count | integer | ○ | 達成に必要な回数 | `5` |
| mission_category | string | ○ | ミッションカテゴリ | `AdventBattle` |
| mst_mission_reward_group_id | string | ○ | 報酬グループID | `osh_00001_limited_term_1` |
| sort_order | integer | ○ | 表示順序 | `1` |
| destination_scene | string | ○ | 遷移先シーン | `AdventBattle` |
| start_at | datetime | ○ | 開始日時 | `2026-01-09 15:00:00` |
| end_at | datetime | ○ | 終了日時 | `2026-01-13 14:59:59` |

**progress_group_key:**
- 同じグループ内で進捗を共有する場合に使用
- 通常は各ミッション固有の値を設定

**mission_category値:**
- `AdventBattle`: 降臨バトル
- `Event`: イベント
- など(カテゴリに応じて設定)

### MstMissionReward

ミッション報酬の定義。全ミッション種別で共通使用。

| カラム名 | 型 | 必須 | 説明 | 設定例 |
|---------|---|------|------|--------|
| ENABLE | string | ○ | 有効フラグ | `e` |
| id | string | ○ | 報酬ID | `mission_reward_441` |
| release_key | string | ○ | リリースキー | `202512020` |
| group_id | string | ○ | 報酬グループID | `event_osh_00001_daily_bonus_01` |
| resource_type | string | ○ | リソースタイプ(Enum値) | `Item`, `FreeDiamond`, `Coin`, `Emblem`, `Unit`, `Exp` |
| resource_id | string | △ | リソースID | `ticket_osh_10000` |
| resource_amount | integer | ○ | 報酬数量 | `1` |
| sort_order | integer | ○ | 表示順序 | `1` |

**resource_type値(利用可能な値のみ):**
- `FreeDiamond`: プリズム(無償ダイヤ)
- `Coin`: コイン
- `Exp`: 経験値
- `Item`: アイテム
- `Emblem`: エンブレム
- `Unit`: ユニット(キャラ)

**重要:** 上記以外の値(例: `PaidDiamond`, `Stamina`, `Artwork`)は設定できません。テーブル定義でEnum制約されています。

**resource_id:**
- `resource_type`が`FreeDiamond`, `Coin`, `Exp`の場合は空文字(不要)
- `resource_type`が`Item`, `Emblem`, `Unit`の場合は対応するマスタデータのID(必須)

---

## criterion_type別の設定方法

### 基本構造

各`criterion_type`は対応する`Criterion`クラスで定義されています。
クラスのphpdocに詳細な説明があります。

**参照先:**
- サーバー実装: `projects/glow-server/api/app/Domain/Mission/Entities/Criteria/`
- 日本語説明: `projects/glow-server/admin/app/Constants/MissionCriterionType.php`

### 主要なcriterion_type一覧

#### ステージ・クエスト関連

##### StageClearCount
**説明:** 通算ステージクリア回数がY回に到達

**設定:**
- `criterion_type`: `StageClearCount`
- `criterion_value`: `null` (空文字)
- `criterion_count`: 達成回数

**実例:**
```csv
criterion_type,criterion_value,criterion_count,説明
StageClearCount,,5,ステージを5回クリアしよう
StageClearCount,,10,ステージを10回クリアしよう
```

##### SpecificStageClearCount
**説明:** 指定ステージXをY回クリア

**設定:**
- `criterion_type`: `SpecificStageClearCount`
- `criterion_value`: `mst_stages.id`
- `criterion_count`: 達成回数

**実例:**
```csv
criterion_type,criterion_value,criterion_count,説明
SpecificStageClearCount,event_glo1_1day_00001,1,デイリークエスト「開運!ジャンブル運試し」を1回クリアしよう
```

##### SpecificQuestClear
**説明:** 指定したクエストをクリアする

**設定:**
- `criterion_type`: `SpecificQuestClear`
- `criterion_value`: `mst_quests.id`
- `criterion_count`: `1` (実際には無視されAPIで1として扱われる)

**実例:**
```csv
criterion_type,criterion_value,criterion_count,説明
SpecificQuestClear,quest_event_osh1_charaget01,1,収集クエスト「芸能界へ！」をクリアしよう
SpecificQuestClear,quest_main_osh_normal_17,1,メインクエスト「【推しの子】」の難易度ノーマルをクリアしよう
```

##### QuestClearCount
**説明:** 通算クエストクリア回数がY回に到達

**設定:**
- `criterion_type`: `QuestClearCount`
- `criterion_value`: `null` (空文字)
- `criterion_count`: 達成回数

##### SpecificStageChallengeCount
**説明:** 指定ステージXにY回挑戦する

**設定:**
- `criterion_type`: `SpecificStageChallengeCount`
- `criterion_value`: `mst_stages.id`
- `criterion_count`: 達成回数

#### ユニット編成関連

##### SpecificUnitStageClearCount
**説明:** 指定したユニットを編成して指定したステージをY回クリア

**設定:**
- `criterion_type`: `SpecificUnitStageClearCount`
- `criterion_value`: `<mst_units.id>.<mst_stages.id>`(ドットで連結)
- `criterion_count`: 達成回数

**実例:**
```csv
criterion_type,criterion_value,criterion_count,説明
SpecificUnitStageClearCount,chara_osh_00601.event_osh1_1day_00001,1,【汗が輝いてるよ!】ぴえヨンを編成に入れて「ファンと推し合戦！」を1回クリア
SpecificUnitStageClearCount,chara_osh_00601.event_osh1_charaget01_00003,5,【ナイスバルク!】ぴえヨンを編成に入れて「芸能界へ！」3話を5回クリア
```

**注意:**
- ユニットIDとステージIDの間は**ドット(.)で連結**
- カンマ等ではない

##### SpecificUnitStageChallengeCount
**説明:** 指定したユニットを編成して指定したステージにY回挑戦

**設定:**
- `criterion_type`: `SpecificUnitStageChallengeCount`
- `criterion_value`: `<mst_units.id>.<mst_stages.id>`(ドットで連結)
- `criterion_count`: 達成回数

**実例:**
```csv
criterion_type,criterion_value,criterion_count,説明
SpecificUnitStageChallengeCount,chara_osh_00601.event_osh1_challenge01_00001,1,【上腕二頭筋ナイス!】ぴえヨンを編成に入れて「推しの子になってやる」1話を1回挑戦
```

#### ガチャ関連

##### SpecificGachaDrawCount
**説明:** 指定ガシャXをY回引く

**設定:**
- `criterion_type`: `SpecificGachaDrawCount`
- `criterion_value`: `opr_gachas.id`
- `criterion_count`: 達成回数

**実例:**
```csv
criterion_type,criterion_value,criterion_count,説明
SpecificGachaDrawCount,gasho_001,10,賀正ガシャ2026を10回引こう
SpecificGachaDrawCount,gasho_001,20,賀正ガシャ2026を20回引こう
```

##### GachaDrawCount
**説明:** 通算でガチャをY回引く

**設定:**
- `criterion_type`: `GachaDrawCount`
- `criterion_value`: `null` (空文字)
- `criterion_count`: 達成回数

#### ログイン関連

##### LoginCount
**説明:** 通算ログインがY日に到達

**設定:**
- `criterion_type`: `LoginCount`
- `criterion_value`: `null` (空文字)
- `criterion_count`: 達成日数

##### LoginContinueCount
**説明:** 連続ログインがY日目に到達

**設定:**
- `criterion_type`: `LoginContinueCount`
- `criterion_value`: `null` (空文字)
- `criterion_count`: 達成日数

#### ユニット関連

##### UnitLevel
**説明:** 全ユニットの内でいずれかがLv.Yに到達

**設定:**
- `criterion_type`: `UnitLevel`
- `criterion_value`: `null` (空文字)
- `criterion_count`: 達成レベル

##### SpecificUnitLevel
**説明:** 指定ユニットがLv.Yに到達

**設定:**
- `criterion_type`: `SpecificUnitLevel`
- `criterion_value`: `mst_units.id`
- `criterion_count`: 達成レベル

##### UnitAcquiredCount
**説明:** ユニットをY体入手しよう

**設定:**
- `criterion_type`: `UnitAcquiredCount`
- `criterion_value`: `null` (空文字)
- `criterion_count`: 達成体数

##### SpecificUnitAcquiredCount
**説明:** 指定したユニットを入手する

**設定:**
- `criterion_type`: `SpecificUnitAcquiredCount`
- `criterion_value`: `mst_units.id`
- `criterion_count`: 達成体数(通常1)

##### SpecificUnitRankUpCount
**説明:** 指定したユニットのランクアップ回数がY回以上

**設定:**
- `criterion_type`: `SpecificUnitRankUpCount`
- `criterion_value`: `mst_units.id`
- `criterion_count`: 達成回数

##### SpecificUnitGradeUpCount
**説明:** 指定したユニットのグレードアップ回数がY回以上

**設定:**
- `criterion_type`: `SpecificUnitGradeUpCount`
- `criterion_value`: `mst_units.id`
- `criterion_count`: 達成回数

#### 敵・図鑑関連

##### DefeatEnemyCount
**説明:** インゲームで敵をY体撃破

**設定:**
- `criterion_type`: `DefeatEnemyCount`
- `criterion_value`: `null` (空文字)
- `criterion_count`: 達成体数

##### DefeatBossEnemyCount
**説明:** インゲームで強敵をY体撃破

**設定:**
- `criterion_type`: `DefeatBossEnemyCount`
- `criterion_value`: `null` (空文字)
- `criterion_count`: 達成体数

##### EnemyDiscoveryCount
**説明:** インゲームで敵キャラをY体発見

**設定:**
- `criterion_type`: `EnemyDiscoveryCount`
- `criterion_value`: `null` (空文字)
- `criterion_count`: 達成体数

##### SpecificEnemyDiscoveryCount
**説明:** インゲームで指定敵キャラXをY体発見

**設定:**
- `criterion_type`: `SpecificEnemyDiscoveryCount`
- `criterion_value`: `mst_enemy_characters.id`
- `criterion_count`: 達成体数

##### SpecificSeriesEnemyDiscoveryCount
**説明:** 指定作品Xの敵キャラをY体発見

**設定:**
- `criterion_type`: `SpecificSeriesEnemyDiscoveryCount`
- `criterion_value`: `mst_series.id`
- `criterion_count`: 達成体数

##### ArtworkCompletedCount
**説明:** アートワークをY個完成させる

**設定:**
- `criterion_type`: `ArtworkCompletedCount`
- `criterion_value`: `null` (空文字)
- `criterion_count`: 達成個数

##### SpecificArtworkCompletedCount
**説明:** 指定アートワークXを完成させる

**設定:**
- `criterion_type`: `SpecificArtworkCompletedCount`
- `criterion_value`: `mst_artworks.id`
- `criterion_count`: 達成個数(通常1)

##### SpecificSeriesArtworkCompletedCount
**説明:** 指定作品XのアートワークをY個完成させる

**設定:**
- `criterion_type`: `SpecificSeriesArtworkCompletedCount`
- `criterion_value`: `mst_series.id`
- `criterion_count`: 達成個数

##### EmblemAcquiredCount
**説明:** エンブレムをY個獲得する

**設定:**
- `criterion_type`: `EmblemAcquiredCount`
- `criterion_value`: `null` (空文字)
- `criterion_count`: 達成個数

##### SpecificEmblemAcquiredCount
**説明:** 指定エンブレムXを獲得する

**設定:**
- `criterion_type`: `SpecificEmblemAcquiredCount`
- `criterion_value`: `mst_emblems.id`
- `criterion_count`: 達成個数(通常1)

##### SpecificSeriesEmblemAcquiredCount
**説明:** 指定作品XのエンブレムをY個獲得する

**設定:**
- `criterion_type`: `SpecificSeriesEmblemAcquiredCount`
- `criterion_value`: `mst_series.id`
- `criterion_count`: 達成個数

#### その他

##### UserLevel
**説明:** ユーザーレベルがYに到達

**設定:**
- `criterion_type`: `UserLevel`
- `criterion_value`: `null` (空文字)
- `criterion_count`: 達成レベル

##### CoinCollect
**説明:** コインをX枚所持する

**設定:**
- `criterion_type`: `CoinCollect`
- `criterion_value`: `null` (空文字)
- `criterion_count`: 達成枚数

##### CoinUsedCount
**説明:** コインをX枚使用した

**設定:**
- `criterion_type`: `CoinUsedCount`
- `criterion_value`: `null` (空文字)
- `criterion_count`: 達成枚数

##### SpecificItemCollect
**説明:** 指定アイテムをX個集める

**設定:**
- `criterion_type`: `SpecificItemCollect`
- `criterion_value`: `mst_items.id`
- `criterion_count`: 達成個数

##### OutpostEnhanceCount
**説明:** ゲートをX回以上強化

**設定:**
- `criterion_type`: `OutpostEnhanceCount`
- `criterion_value`: `null` (空文字)
- `criterion_count`: 達成回数

##### SpecificOutpostEnhanceLevel
**説明:** 指定したゲート強化項目がLvYに到達する

**設定:**
- `criterion_type`: `SpecificOutpostEnhanceLevel`
- `criterion_value`: `mst_outpost_parameters.id`
- `criterion_count`: 達成レベル

##### ReviewCompleted
**説明:** ストアレビューを記載

**設定:**
- `criterion_type`: `ReviewCompleted`
- `criterion_value`: `null` (空文字)
- `criterion_count`: `1`

##### FollowCompleted
**説明:** 公式X(エックス)をフォローする

**設定:**
- `criterion_type`: `FollowCompleted`
- `criterion_value`: `null` (空文字)
- `criterion_count`: `1`

##### AccountCompleted
**説明:** アカウント連携を行う

**設定:**
- `criterion_type`: `AccountCompleted`
- `criterion_value`: `null` (空文字)
- `criterion_count`: `1`

##### IaaCount
**説明:** 広告視聴をY回する

**設定:**
- `criterion_type`: `IaaCount`
- `criterion_value`: `null` (空文字)
- `criterion_count`: 達成回数

##### AccessWeb
**説明:** Webアクセスでミッションクリア

**設定:**
- `criterion_type`: `AccessWeb`
- `criterion_value`: `null` (空文字)
- `criterion_count`: `1`

##### IdleIncentiveCount
**説明:** 探索をY回する

**設定:**
- `criterion_type`: `IdleIncentiveCount`
- `criterion_value`: `null` (空文字)
- `criterion_count`: 達成回数

##### IdleIncentiveQuickCount
**説明:** クイック探索をY回する

**設定:**
- `criterion_type`: `IdleIncentiveQuickCount`
- `criterion_value`: `null` (空文字)
- `criterion_count`: 達成回数

##### AdventBattleChallengeCount
**説明:** 降臨バトルにY回挑戦する

**設定:**
- `criterion_type`: `AdventBattleChallengeCount`
- `criterion_value`: `null` (空文字)
- `criterion_count`: 達成回数

**実例:**
```csv
criterion_type,criterion_value,criterion_count,説明
AdventBattleChallengeCount,,5,降臨バトル「ファーストライブ」に5回挑戦しよう！
```

##### AdventBattleTotalScore
**説明:** 降臨バトルで合計スコアがY点に到達

**設定:**
- `criterion_type`: `AdventBattleTotalScore`
- `criterion_value`: `mst_advent_battles.id`
- `criterion_count`: 達成スコア

##### AdventBattleScore
**説明:** 降臨バトルでスコアがY点に到達

**設定:**
- `criterion_type`: `AdventBattleScore`
- `criterion_value`: `mst_advent_battles.id`
- `criterion_count`: 達成スコア

##### PvpChallengeCount
**説明:** PVPにY回挑戦する

**設定:**
- `criterion_type`: `PvpChallengeCount`
- `criterion_value`: `null` (空文字)
- `criterion_count`: 達成回数

##### PvpWinCount
**説明:** PVPでY回勝利する

**設定:**
- `criterion_type`: `PvpWinCount`
- `criterion_value`: `null` (空文字)
- `criterion_count`: 達成回数

##### MissionClearCount
**説明:** ミッションをY個クリアする

**設定:**
- `criterion_type`: `MissionClearCount`
- `criterion_value`: `null` (空文字)
- `criterion_count`: 達成個数

##### SpecificMissionClearCount
**説明:** 指定したミッショングループXの内でY個クリアする

**設定:**
- `criterion_type`: `SpecificMissionClearCount`
- `criterion_value`: ミッショングループID
- `criterion_count`: 達成個数

##### MissionBonusPoint
**説明:** ミッションボーナスポイントをY個集める

**設定:**
- `criterion_type`: `MissionBonusPoint`
- `criterion_value`: `null` (空文字)
- `criterion_count`: 達成ポイント

---

## 実例

### 実例1: イベントミッション(いいジャン祭ミッション)

#### 運営仕様書の記載

```
◆いいジャン祭ミッション

施策名称: 【推しの子】いいジャン祭 特別ミッション
開催期間: 1/1(木) 00:00 〜 2/2(月) 10:59

ミッション内容 | プリズム | 賀正ガシャチケット | スペシャルガシャチケット
--------------|---------|--------------------|------------------------
ステージを5回クリアしよう | - | 2 | -
ステージを10回クリアしよう | - | 3 | -
ステージを30回クリアしよう | 50 | - | -
賀正ガシャ2026を10回引こう | - | - | 1
```

#### 作成するCSV

**MstMissionEvent.csv:**
```csv
ENABLE,id,release_key,mst_event_id,criterion_type,criterion_value,criterion_count,unlock_criterion_type,unlock_criterion_value,unlock_criterion_count,group_key,mst_mission_reward_group_id,sort_order,destination_scene
e,event_osh_00001_1,202512020,event_osh_00001,StageClearCount,,5,__NULL__,,0,,osh_00001_event_reward_1,1,Event
e,event_osh_00001_2,202512020,event_osh_00001,StageClearCount,,10,__NULL__,,0,,osh_00001_event_reward_2,2,Event
e,event_osh_00001_5,202512020,event_osh_00001,StageClearCount,,30,__NULL__,,0,,osh_00001_event_reward_5,5,Event
e,event_osh_00001_28,202512020,event_osh_00001,SpecificGachaDrawCount,gasho_001,10,__NULL__,,0,,osh_00001_event_reward_28,28,Gacha
```

**MstMissionEventI18n.csv:**
```csv
ENABLE,release_key,id,mst_mission_event_id,language,description
e,202512020,event_osh_00001_1_ja,event_osh_00001_1,ja,ステージを5回クリアしよう
e,202512020,event_osh_00001_2_ja,event_osh_00001_2,ja,ステージを10回クリアしよう
e,202512020,event_osh_00001_5_ja,event_osh_00001_5,ja,ステージを30回クリアしよう
e,202512020,event_osh_00001_28_ja,event_osh_00001_28,ja,賀正ガシャ2026を10回引こう
```

**MstMissionReward.csv:**
```csv
ENABLE,id,release_key,group_id,resource_type,resource_id,resource_amount,sort_order
e,mission_reward_531,202512020,osh_00001_event_reward_1,Item,ticket_glo_10001,2,1
e,mission_reward_532,202512020,osh_00001_event_reward_2,Item,ticket_glo_10001,3,1
e,mission_reward_535,202512020,osh_00001_event_reward_5,FreeDiamond,,50,1
e,mission_reward_558,202512020,osh_00001_event_reward_28,Item,ticket_glo_00002,1,1
```

**MstMissionEventDependency.csv:**
```csv
ENABLE,id,release_key,group_id,mst_mission_event_id,unlock_order
e,190,202512020,event_osh_00001_1,event_osh_00001_1,1
e,191,202512020,event_osh_00001_1,event_osh_00001_2,2
e,194,202512020,event_osh_00001_1,event_osh_00001_5,5
```

### 実例2: ユニット編成ミッション(ぴえヨンミッション)

#### 運営仕様書の記載

```
◆ぴえヨンミッション

施策名称: ぴえヨン特別ブートミッション
開催期間: 1/1(木) 00:00 〜 2/2(月) 10:59

ミッション内容 | コイン | 筋肉推し!エンブレム
--------------|---------|-----------------
【汗が輝いてるよ!】ぴえヨンを編成に入れて「ファンと推し合戦！」を1回クリア | 1000 | -
【バルクきてるよ!】ぴえヨンを編成に入れて「ファンと推し合戦！」を3回クリア | 1000 | -
【新年号は筋肉です】ぴえヨンを編成に入れて「芸能界へ！」3話を100回クリア | - | 1
```

#### 作成するCSV

**MstMissionEvent.csv:**
```csv
ENABLE,id,release_key,mst_event_id,criterion_type,criterion_value,criterion_count,unlock_criterion_type,unlock_criterion_value,unlock_criterion_count,group_key,mst_mission_reward_group_id,sort_order,destination_scene
e,event_osh_00001_33,202512020,event_osh_00001,SpecificUnitStageClearCount,chara_osh_00601.event_osh1_1day_00001,1,__NULL__,,0,,osh_00001_event_reward_33,33,Event
e,event_osh_00001_34,202512020,event_osh_00001,SpecificUnitStageClearCount,chara_osh_00601.event_osh1_1day_00001,3,__NULL__,,0,,osh_00001_event_reward_34,34,Event
e,event_osh_00001_41,202512020,event_osh_00001,SpecificUnitStageClearCount,chara_osh_00601.event_osh1_charaget01_00003,100,__NULL__,,0,,osh_00001_event_reward_41,41,Event
```

**ポイント:**
- `criterion_value`は`<ユニットID>.<ステージID>`の形式
- `chara_osh_00601`: ぴえヨンのユニットID
- `event_osh1_1day_00001`: デイリークエスト「ファンと推し合戦！」のステージID

**MstMissionEventI18n.csv:**
```csv
ENABLE,release_key,id,mst_mission_event_id,language,description
e,202512020,event_osh_00001_33_ja,event_osh_00001_33,ja,【汗が輝いてるよ!】ぴえヨンを編成に入れて「ファンと推し合戦！」を1回クリア
e,202512020,event_osh_00001_34_ja,event_osh_00001_34,ja,【バルクきてるよ!】ぴえヨンを編成に入れて「ファンと推し合戦！」を3回クリア
e,202512020,event_osh_00001_41_ja,event_osh_00001_41,ja,【新年号は筋肉です】ぴえヨンを編成に入れて「芸能界へ！」3話を100回クリア
```

**MstMissionReward.csv:**
```csv
ENABLE,id,release_key,group_id,resource_type,resource_id,resource_amount,sort_order
e,mission_reward_563,202512020,osh_00001_event_reward_33,Coin,,1000,1
e,mission_reward_564,202512020,osh_00001_event_reward_34,Coin,,1000,1
e,mission_reward_571,202512020,osh_00001_event_reward_41,Emblem,emblem_event_osh_00008,1,1
```

**MstMissionEventDependency.csv:**
```csv
ENABLE,id,release_key,group_id,mst_mission_event_id,unlock_order
e,222,202512020,event_osh_00001_33,event_osh_00001_33,1
e,223,202512020,event_osh_00001_33,event_osh_00001_34,2
```

### 実例3: ログインボーナス

#### 運営仕様書の記載(施策シート)

```
◆ログインボーナス

施策名称: 【推しの子】いいジャン祭 特別ログインボーナス
開催期間: 1/1 0:00 〜 1/16 3:59

ログイン日数 | 報酬
------------|------
1日目 | 【推しの子】SSR確定ガシャチケット x1
2日目 | プリズム x150
3日目 | いいジャン祭メダル【赤】 x200
```

#### 作成するCSV

**MstMissionEventDailyBonusSchedule.csv:**
```csv
ENABLE,id,release_key,mst_event_id,start_at,end_at
e,event_osh_00001_daily_bonus,202512020,event_osh_00001,2026-01-01 00:00:00,2026-01-16 03:59:59
```

**MstMissionEventDailyBonus.csv:**
```csv
ENABLE,id,release_key,mst_mission_event_daily_bonus_schedule_id,login_day_count,mst_mission_reward_group_id,sort_order
e,event_osh_00001_daily_bonus_01,202512020,event_osh_00001_daily_bonus,1,event_osh_00001_daily_bonus_01,1
e,event_osh_00001_daily_bonus_02,202512020,event_osh_00001_daily_bonus,2,event_osh_00001_daily_bonus_02,1
e,event_osh_00001_daily_bonus_03,202512020,event_osh_00001_daily_bonus,3,event_osh_00001_daily_bonus_03,1
```

**MstMissionReward.csv:**
```csv
ENABLE,id,release_key,group_id,resource_type,resource_id,resource_amount,sort_order
e,mission_reward_441,202512020,event_osh_00001_daily_bonus_01,Item,ticket_osh_10000,1,1
e,mission_reward_442,202512020,event_osh_00001_daily_bonus_02,FreeDiamond,,150,1
e,mission_reward_443,202512020,event_osh_00001_daily_bonus_03,Item,item_glo_00001,200,1
```

### 実例4: アチーブメントミッション

#### 運営仕様書の記載

```
◆メインクエスト追加の恒常ミッション

施策名称: メインクエスト追加の恒常ミッション
開催期間: 1/1(木) 00:00 〜 無期限

ミッション内容 | プリズム
--------------|---------
メインクエスト「【推しの子】」の難易度ノーマルをクリアしよう | 50
メインクエスト「【推しの子】」の難易度ハードをクリアしよう | 50
メインクエスト「【推しの子】」の難易度エクストラをクリアしよう | 50
```

#### 作成するCSV

**MstMissionAchievement.csv:**
```csv
ENABLE,id,release_key,criterion_type,criterion_value,criterion_count,unlock_criterion_type,unlock_criterion_value,unlock_criterion_count,group_key,mst_mission_reward_group_id,sort_order,destination_scene
e,achievement_2_101,202512020,SpecificQuestClear,quest_main_osh_normal_17,1,__NULL__,,0,,achievement_2_101,101,QuestSelect
e,achievement_2_102,202512020,SpecificQuestClear,quest_main_osh_hard_17,1,__NULL__,,0,,achievement_2_102,102,QuestSelect
e,achievement_2_103,202512020,SpecificQuestClear,quest_main_osh_veryhard_17,1,__NULL__,,0,,achievement_2_103,103,QuestSelect
```

**MstMissionAchievementI18n.csv:**
```csv
ENABLE,release_key,id,mst_mission_achievement_id,language,description
e,202512020,achievement_2_101_ja,achievement_2_101,ja,メインクエスト「【推しの子】」の難易度ノーマルをクリアしよう
e,202512020,achievement_2_102_ja,achievement_2_102,ja,メインクエスト「【推しの子】」の難易度ハードをクリアしよう
e,202512020,achievement_2_103_ja,achievement_2_103,ja,メインクエスト「【推しの子】」の難易度エクストラをクリアしよう
```

**MstMissionReward.csv:**
```csv
ENABLE,id,release_key,group_id,resource_type,resource_id,resource_amount,sort_order
e,mission_reward_460,202512020,achievement_2_101,FreeDiamond,,50,1
e,mission_reward_461,202512020,achievement_2_102,FreeDiamond,,50,1
e,mission_reward_462,202512020,achievement_2_103,FreeDiamond,,50,1
```

### 実例5: 期間限定ミッション(降臨バトルミッション)

#### 運営仕様書の記載

```
降臨バトルミッション

開催期間: 1/9 15:00 〜 1/13 14:59

ミッション内容 | コイン | プリズム
--------------|---------|---------
降臨バトル「ファーストライブ」に5回挑戦しよう！ | 2000 | -
降臨バトル「ファーストライブ」に10回挑戦しよう！ | - | 20
```

#### 作成するCSV

**MstMissionLimitedTerm.csv:**
```csv
ENABLE,id,release_key,progress_group_key,criterion_type,criterion_value,criterion_count,mission_category,mst_mission_reward_group_id,sort_order,destination_scene,start_at,end_at
e,limited_term_33,202512020,group9,AdventBattleChallengeCount,,5,AdventBattle,osh_00001_limited_term_1,1,AdventBattle,2026-01-09 15:00:00,2026-01-13 14:59:59
e,limited_term_34,202512020,group9,AdventBattleChallengeCount,,10,AdventBattle,osh_00001_limited_term_2,2,AdventBattle,2026-01-09 15:00:00,2026-01-13 14:59:59
```

**MstMissionLimitedTermI18n.csv:**
(※実際のデータでは明示的に作成されていないが、必要に応じて作成)

**MstMissionReward.csv:**
```csv
ENABLE,id,release_key,group_id,resource_type,resource_id,resource_amount,sort_order
e,mission_reward_527,202512020,osh_00001_limited_term_1,Coin,,2000,1
e,mission_reward_528,202512020,osh_00001_limited_term_2,FreeDiamond,,20,1
```

---

## チェックリスト

作成完了後、以下の項目を確認してください。

### 共通

- [ ] ENABLEは全て`e`になっているか
- [ ] release_keyは正しいリリースキーになっているか
- [ ] IDに重複はないか
- [ ] sort_orderは適切に設定されているか
- [ ] destination_sceneは適切に設定されているか

### ミッション本体

- [ ] criterion_typeは正しいか
- [ ] criterion_valueは該当するcriterion_typeの仕様に従っているか
- [ ] criterion_countは仕様書の値と一致しているか
- [ ] unlock_criterion系は基本的に`__NULL__`, `空文字`, `0`になっているか
- [ ] mst_mission_reward_group_idは対応する報酬グループが存在するか

### I18nテーブル

- [ ] 各ミッションに対応するI18nレコードが存在するか
- [ ] descriptionは仕様書の文言と一致しているか
- [ ] IDは`{mission_id}_{language}`の形式になっているか

### 報酬テーブル

- [ ] 各ミッションに対応する報酬レコードが存在するか
- [ ] resource_typeは利用可能な値か(`FreeDiamond`, `Coin`, `Exp`, `Item`, `Emblem`, `Unit`のいずれか)
- [ ] resource_typeが`Item`, `Emblem`, `Unit`の場合、resource_idが設定されているか
- [ ] resource_typeが`FreeDiamond`, `Coin`, `Exp`の場合、resource_idが空文字になっているか
- [ ] resource_idはresource_typeに応じて適切に設定されているか
- [ ] resource_amountは仕様書の値と一致しているか
- [ ] resource_idで指定したリソースが実際に存在するか確認したか(Item, Emblem, Unitの場合のみ)
  - [ ] 新規マスタデータ(`マスタデータ/リリース/{release_key}/tables/`)を確認
  - [ ] 既存マスタデータ(`projects/glow-masterdata/`)を確認
  - [ ] `Item`の場合: `MstItem.csv`に存在するか
  - [ ] `Emblem`の場合: `MstEmblem.csv`に存在するか
  - [ ] `Unit`の場合: `MstUnit.csv`に存在するか

### 依存関係テーブル(使用する場合)

- [ ] group_idで段階的解放のグループ化がされているか
- [ ] unlock_orderは連番になっているか
- [ ] 依存関係の順序は意図通りか

### ログインボーナス(使用する場合)

- [ ] スケジュールテーブルに期間が設定されているか
- [ ] login_day_countは1から始まる連番になっているか
- [ ] 日数と報酬の対応は仕様書と一致しているか

---

## トラブルシューティング

### criterion_valueに何を設定すべきか分からない

1. サーバー実装のCriterionクラスを確認する
   - パス: `projects/glow-server/api/app/Domain/Mission/Entities/Criteria/`
   - ファイル名: `{CriterionType}Criterion.php`
2. クラスのphpdocコメントに説明がある
3. 本手順書の[criterion_type別の設定方法](#criterion_type別の設定方法)も参照

### 段階的解放の設定方法が分からない

MstMissionEventDependencyを使用します。

**手順:**
1. 同じ`group_id`を持つレコードを作成
2. `unlock_order`を1から順に設定
3. 前のミッションをクリアすると次が解放される

### 報酬が複数ある場合の設定方法

MstMissionRewardに複数レコードを作成し、同じ`group_id`を設定します。

**例:**
```csv
ENABLE,id,release_key,group_id,resource_type,resource_id,resource_amount,sort_order
e,mission_reward_001,202512020,reward_group_1,FreeDiamond,,100,1
e,mission_reward_002,202512020,reward_group_1,Coin,,5000,2
e,mission_reward_003,202512020,reward_group_1,Item,ticket_001,3,3
```

### ユニットとステージの組み合わせミッションの設定

`SpecificUnitStageClearCount`または`SpecificUnitStageChallengeCount`を使用します。

**重要:**
- `criterion_value`は`<ユニットID>.<ステージID>`の形式
- ドット(.)で連結する
- カンマ等ではない

**例:**
```csv
criterion_type,criterion_value,criterion_count
SpecificUnitStageClearCount,chara_osh_00601.event_osh1_1day_00001,5
```

### 報酬リソースが見つからない

報酬に設定したresource_idが見つからない場合の対処法。

**問題:**
- MstMissionRewardで指定したresource_id(例: `ticket_glo_10001`)が、新規・既存どちらのマスタデータにも存在しない

**原因と対処法:**

#### 1. resource_idの入力ミス
**確認:**
- 仕様書の記載と一致しているか
- タイポや全角/半角の間違いがないか

**対処:**
- 正しいIDに修正する

#### 2. 該当リソースのマスタデータがまだ作成されていない
**確認:**
- 新規アイテム/エンブレム/ユニットなのに、対応するマスタデータCSVが作成されていない

**対処:**
1. 該当リソースのマスタデータを先に作成する
   - アイテムの場合: `MstItem.csv`, `MstItemI18n.csv`を作成
   - エンブレムの場合: `MstEmblem.csv`, `MstEmblemI18n.csv`を作成
   - ユニットの場合: `MstUnit.csv`, `MstUnitI18n.csv`等を作成
2. ミッション報酬を設定する

#### 3. 既存リソースだが確認場所が間違っている
**確認:**
- 既存マスタデータ(`projects/glow-masterdata/`)を確認したか

**対処:**
```bash
# 全CSVファイルから検索
grep -r "ticket_glo_10001" projects/glow-masterdata/*.csv

# 特定のマスタデータで検索
grep "ticket_glo_10001" projects/glow-masterdata/MstItem.csv
```

#### 4. resource_typeとマスタデータの対応が間違っている
**確認:**
- `resource_type`が`Item`なのに`MstEmblem.csv`を確認していないか

**対処:**
- [STEP 5: 報酬の設定](#step-5-報酬の設定)の対応表を確認
- 正しいマスタデータファイルを確認する

**実例:**

報酬設定:
```csv
resource_type,resource_id
Item,emblem_event_osh_00008
```

この場合、resource_typeが`Item`なのにresource_idが`emblem_`で始まっている。

**対処:**
```csv
# 正しい設定
resource_type,resource_id
Emblem,emblem_event_osh_00008
```

#### 5. 利用できないresource_typeを指定している
**確認:**
- `PaidDiamond`, `Stamina`, `Artwork`などを指定していないか

**対処:**
- ミッション報酬で利用できるresource_typeは以下のみです:
  - `FreeDiamond`, `Coin`, `Exp`, `Item`, `Emblem`, `Unit`
- テーブル定義でEnum制約されているため、これ以外は使用できません
- `PaidDiamond`(有償ダイヤ)などはミッション報酬としては設定できません

**実例:**

```csv
# 誤った設定
resource_type,resource_id,resource_amount
PaidDiamond,,100

# 正しい設定(無償ダイヤを使用)
resource_type,resource_id,resource_amount
FreeDiamond,,100
```

---

## 参考資料

### サーバー実装

**ミッション達成条件:**
- MissionCriterionType Enum: `projects/glow-server/api/app/Domain/Mission/Enums/MissionCriterionType.php`
- Criterionクラス: `projects/glow-server/api/app/Domain/Mission/Entities/Criteria/`
- 日本語説明: `projects/glow-server/admin/app/Constants/MissionCriterionType.php`

**報酬リソース:**
- RewardType Enum: `projects/glow-server/api/app/Domain/Resource/Enums/RewardType.php`
  - resource_typeの利用可能な値とresource_idの必須/不要が定義されています

**テーブル定義:**
- DBスキーマ: `projects/glow-server/api/database/schema/exports/master_tables_schema.json`
  - mst_mission_rewardsのresource_type Enum制約を確認できます

### 実例

- リリースキー202512020のマスタデータ: `マスタデータ/リリース/202512020/tables/`
- 運営仕様書: `マスタデータ/リリース/202512020/仕様/`

### 既存マスタデータ

- 既存リソース確認先: `projects/glow-masterdata/`
  - 過去のリリースで既に存在するアイテム・エンブレム・ユニット等

---

## 更新履歴

- 2026-01-16: 初版作成
- 2026-01-16: 報酬リソースの存在チェック手順を追加
- 2026-01-16: resource_typeの利用可能な値を実装・テーブル定義に基づいて正確に修正
```

---


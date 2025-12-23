# ステージ・クエスト・インゲーム関連DBテーブル詳細分析

> 最終更新: 2025-12-23
>
> このドキュメントは、glow-serverとglow-clientにおけるステージ、クエスト、インゲーム関連のDBテーブルの役割、使用箇所、データ変更時の影響を包括的にまとめたものです。
>
> **DDL参照**: `@projects/glow-server/api/database/schema/master_tables_ddl.sql`

---

## 目次

1. [mst_stages](#1-mst_stages)
2. [mst_quests](#2-mst_quests)
3. [mst_stage_rewards](#3-mst_stage_rewards)
4. [mst_stage_event_rewards](#4-mst_stage_event_rewards)
5. [mst_stage_end_conditions](#5-mst_stage_end_conditions)
6. [mst_in_games](#6-mst_in_games)
7. [mst_enemy_stage_parameters](#7-mst_enemy_stage_parameters)
8. [mst_stage_clear_time_rewards](#8-mst_stage_clear_time_rewards)
9. [mst_stage_enhance_reward_params](#9-mst_stage_enhance_reward_params)
10. [mst_stage_event_settings](#10-mst_stage_event_settings)
11. [mst_stage_tips](#11-mst_stage_tips)
12. [mst_quest_bonus_units](#12-mst_quest_bonus_units)
13. [mst_quest_event_bonus_schedules](#13-mst_quest_event_bonus_schedules)
14. [テーブル依存関係図](#テーブル依存関係図)
15. [データ変更時の影響度マトリクス](#データ変更時の影響度マトリクス)

---

## 1. mst_stages

### テーブル概要
ステージの基本設定を管理するマスターテーブル。クエストに属し、インゲーム設定を参照する。

### 主要カラム
- `id`: ステージID（UUID）
- `mst_quest_id`: 所属するクエストID
- `mst_in_game_id`: インゲーム設定ID
- `stage_number`: ステージ番号
- `recommended_level`: おすすめレベル
- `cost_stamina`: 消費スタミナ
- `exp`: 獲得EXP
- `coin`: 獲得コイン
- `prev_mst_stage_id`: 解放条件のステージID
- `auto_lap_type`: スタミナブーストタイプ（AfterClear/Initial）
- `max_auto_lap_count`: 最大スタミナブースト周回指定可能数
- `start_at`/`end_at`: ステージ公開期間
- `asset_key`: アセットキー

### glow-serverでの使用箇所

#### Model
- **場所**: `api/app/Domain/Resource/Mst/Models/MstStage.php`
- **主要関連**: MstQuest（belongsTo）、MstInGame（belongsTo）、MstStageReward（hasMany）

#### Repository
- **場所**: `api/app/Domain/Resource/Mst/Repositories/MstStageRepository.php`
- **主要メソッド**:
  - `getById(string $id)`: ID指定で取得
  - `getByMstQuestId(string $mstQuestId)`: クエストID指定で関連ステージを取得
  - `getStagePeriod(string $id, CarbonImmutable $now)`: 期間チェック付き取得
  - `getStageGracePeriod(string $id, CarbonImmutable $now)`: 猶予期間考慮の期間チェック付き取得

#### Service
- **場所**: `api/app/Domain/Stage/Services/StageService.php`
- **主要用途**:
  - スタミナコスト計算
  - ステージ開始可否検証（`validateCanAutoLap`）
  - キャンペーン効果の報酬適用

#### Controller & UseCase
- **Controller**: `api/app/Http/Controllers/StageController.php`
- **UseCase**: `api/app/Domain/Stage/UseCases/StageStartUseCase.php` / `StageEndUseCase.php`
- **APIエンドポイント**:
  - `POST /api/stage/start`: ステージ開始
  - `POST /api/stage/end`: ステージ終了
  - `POST /api/stage/continue_diamond`: ダイアモンド継続
  - `POST /api/stage/continue_ad`: 広告視聴継続
  - `POST /api/stage/abort`: ステージ中止

### glow-clientでの使用箇所

#### Model
- **場所**: `Assets/GLOW/Scripts/Runtime/Core/Domain/Models/MasterData/MstStageModel.cs`
- **主要プロパティ**:
  - `MstQuestId`: クエストへの参照
  - `MstInGameId`: インゲーム設定への参照
  - `StageConsumeStamina`: 消費スタミナ
  - `MobEnemyHpCoef`/`BossEnemyHpCoef`: 敵パラメータ係数
  - `AutoLapType`: オートラップタイプ

#### Repository
- **インターフェース**: `Assets/GLOW/Scripts/Runtime/Core/Domain/Definitions/Repositories/MasterData/IMstStageDataRepository.cs`
- **実装**: `Assets/GLOW/Scripts/Runtime/Core/Data/Repositories/MasterData/MasterDataRepository.cs`

#### UseCase
- **ホーム画面**: `HomeStartStageUseCase.cs`, `HomeStageInfoUseCases.cs`, `HomeStageSelectUseCases.cs`
- **インゲーム**: `StageQuestInitializer.cs`
- **バトル結果**: `StageVictoryResultModelFactory.cs`

### データ変更による影響（ゲーム体験の観点）

| カラム名 | ゲーム体験への影響 | 影響度 |
|---------|------------------|-------|
| `id` | ステージが完全に参照できなくなり、プレイ不可になる。全関連データ（報酬、終了条件等）との紐付けが切れる | 🔴 致命的 |
| `mst_quest_id` | ステージが別のクエストに移動し、クエスト選択画面での表示位置が変わる。ストーリー順序が崩れる | 🔴 高 |
| `cost_stamina` | ステージ挑戦に必要なスタミナが変わる。増やすとプレイ回数が減り、減らすと周回が容易になる | 🔴 高（経済バランス） |
| `exp` | クリア時の獲得経験値が変わる。プレイヤーのレベルアップ速度に直結 | 🔴 高（経済バランス） |
| `coin` | クリア時の獲得コインが変わる。ユーザーの収入に直結 | 🔴 高（経済バランス） |
| `start_at`/`end_at` | ステージの表示/非表示が切り替わる。期間外はプレイ不可になり、ユーザーがアクセスできなくなる | 🔴 高 |
| `prev_mst_stage_id` | ステージのアンロック条件が変わる。ストーリー進行の順序が変わり、プレイ可能なタイミングが変わる | 🔴 高 |
| `recommended_level` | ステージ選択画面の推奨レベル表示が変わる。プレイヤーの判断材料になるが、実際のゲームプレイには影響しない | 🟡 中 |
| `auto_lap_type` | スタミナブースト（周回機能）の利用可否が変わる。`Initial`なら最初から周回可能、`AfterClear`ならクリア後のみ周回可能 | 🟡 中 |
| `max_auto_lap_count` | 一度に周回できる最大回数が変わる。効率的な周回プレイに影響 | 🟡 中 |
| `sort_order` | ステージ一覧の表示順序が変わる。ユーザーの視認性に影響 | 🟢 低 |
| `asset_key` | ステージのビジュアル（背景画像等）が変わる。見た目のみの変更でゲームプレイには影響しない | 🟢 低 |
| `mst_artwork_fragment_drop_group_id` | ドロップするアートワークフラグメントのグループが変わる。収集要素に影響 | 🟡 中 |
| `mst_stage_tips_group_id` | ステージヒントの表示内容が変わる。補助情報のみで直接的な影響は少ない | 🟢 低 |

### 重要なビジネスロジック

1. **期間管理**: `start_at` / `end_at` による開催期間チェック（猶予期間対応）
2. **スタミナ消費**: `cost_stamina` にキャンペーン倍率を適用
3. **周回処理**: `auto_lap_type` / `max_auto_lap_count` による制限
4. **ストーリー進行**: `prev_mst_stage_id` による前ステージクリア検証
5. **敵強度制御**: mst_in_gamesの敵パラメータ係数で敵のHP・攻撃・速度を調整

---

## 2. mst_quests

### テーブル概要
クエストの基本設定を管理するマスターテーブル。複数のステージを含む。

### 主要カラム
- `id`: クエストID（UUID）
- `quest_type`: クエストの種類（Normal/Event/Enhance/Tutorial）
- `mst_event_id`: イベントID
- `mst_series_id`: シリーズID
- `sort_order`: ソート順序
- `asset_key`: アセットキー
- `start_date`/`end_date`: 開催期間
- `quest_group`: 同クエストとして表示をまとめるグループ
- `difficulty`: 難易度（Normal/Hard/Extra）

### glow-serverでの使用箇所

#### Model
- **場所**: `api/app/Domain/Resource/Mst/Models/MstQuest.php`
- **主要関連**: MstStage（hasMany）、MstEvent（belongsTo）

#### Repository
- **場所**: `api/app/Domain/Resource/Mst/Repositories/MstQuestRepository.php`
- **主要メソッド**:
  - `getById(string $id)`: ID指定で取得
  - `getQuestPeriod(string $id, CarbonImmutable $now)`: 期間チェック付き取得
  - `getActivesByQuestType(string $questType, CarbonImmutable $now)`: クエストタイプ別取得（期間内のみ）

#### UseCase
- **場所**: `api/app/Domain/Stage/UseCases/StageStartUseCase.php`
  - クエストタイプに応じてサービスを分岐（Normal/Event/Enhance/Tutorial）

### glow-clientでの使用箇所

#### Model
- **場所**: `Assets/GLOW/Scripts/Runtime/Core/Domain/Models/MasterData/MstQuestModel.cs`
- **主要プロパティ**:
  - `QuestType`: クエスト種別（Normal, Event, Enhance等）
  - `Difficulty`: 難易度（Normal, Hard, Extra等）

#### Repository
- **インターフェース**: `Assets/GLOW/Scripts/Runtime/Core/Domain/Definitions/Repositories/MasterData/IMstQuestDataRepository.cs`

#### UseCase
- **場所**: `HomeStageInfoUseCases.cs` - ステージ詳細情報取得時にクエストタイプを参照
- **場所**: `InGameSpecialRuleModelFactory.cs` - クエストタイプごとの特殊ルール適用判定

### データ変更による影響（ゲーム体験の観点）

| カラム名 | ゲーム体験への影響 | 影響度 |
|---------|------------------|-------|
| `id` | クエストが完全に参照できなくなり、所属する全ステージがプレイ不可になる | 🔴 致命的 |
| `quest_type` | クエストの種別が変わり、ゲームロジックが根本的に変わる。Normal→Eventに変えると特殊ルールや報酬計算が変わる。Enhanceにするとスコアアタック形式になる | 🔴 高 |
| `start_date`/`end_date` | クエストの表示/非表示が切り替わる。期間外は所属する全ステージがプレイ不可になる | 🔴 高 |
| `mst_event_id` | イベントとの紐付けが変わる。イベント画面での表示位置が変わる | 🟡 中 |
| `mst_series_id` | シリーズとの紐付けが変わる。シリーズごとのフィルタリングに影響 | 🟡 中 |
| `difficulty` | クエスト選択画面で表示される難易度が変わる。Normal/Hard/Extraの選択肢に影響。プレイヤーの選択基準になる | 🟡 中 |
| `sort_order` | クエスト一覧の表示順序が変わる。ユーザーの視認性に影響 | 🟢 低 |
| `asset_key` | クエストのビジュアル（アイコン画像等）が変わる。見た目のみの変更 | 🟢 低 |
| `quest_group` | 同じグループのクエストとしてまとめて表示される。UI上のグルーピングに影響 | 🟢 低 |

### 重要なビジネスロジック

1. **クエストタイプ分岐**: `quest_type` により開始・終了処理を分岐
   - `Normal`: メインストーリークエスト
   - `Event`: イベントクエスト（期間制限あり）
   - `Enhance`: 強化クエスト（スコアアタック）
   - `Tutorial`: チュートリアル
2. **期間管理**: `start_date` / `end_date` による開催期間チェック
3. **ステージ階層**: クエストに属する複数ステージの管理

---

## 3. mst_stage_rewards

### テーブル概要
通常クエストのステージ報酬を管理するテーブル。報酬カテゴリー（初回/毎回/ランダム）と確率を設定。

### 主要カラム
- `id`: 報酬レコードID（UUID）
- `mst_stage_id`: ステージID
- `reward_category`: 報酬カテゴリー（Always/FirstClear/Random）
- `resource_type`: 報酬タイプ（Exp/Coin/FreeDiamond/Item/Emblem/Unit）
- `resource_id`: 報酬ID
- `resource_amount`: 報酬数
- `percentage`: 出現比重（ドロップ率）
- `sort_order`: ソート順序

### glow-serverでの使用箇所

#### Model
- **場所**: `api/app/Domain/Resource/Mst/Models/MstStageReward.php`

#### Repository
- **場所**: `api/app/Domain/Resource/Mst/Repositories/MstStageRewardRepository.php`
- **主要メソッド**:
  - `getFirstClearRewardsByMstStageId(string $mstStageId)`: 初回クリア報酬を取得
  - `getAlwaysRewardsByMstStageId(string $mstStageId)`: 毎回獲得報酬を取得
  - `getRandomRewardsByMstStageId(string $mstStageId)`: ランダム報酬を取得

#### Service
- **場所**: `api/app/Domain/Stage/Services/StageService.php`
- **主要用途**:
  - `lotteryPercentageStageReward()`: ドロップ率に基づいた抽選処理
  - `applyCampaignByRewardType()`: キャンペーン倍率適用

#### UseCase
- **場所**: `api/app/Domain/Stage/UseCases/StageEndUseCase.php`
  - クリア時の報酬計算・付与

### glow-clientでの使用箇所

#### Model
- **場所**: `Assets/GLOW/Scripts/Runtime/Core/Domain/Models/MasterData/MstStageRewardModel.cs`

#### Repository
- **インターフェース**: `Assets/GLOW/Scripts/Runtime/Core/Domain/Definitions/Repositories/MasterData/IMstStageRewardDataRepository.cs`

#### UseCase
- **場所**: `HomeStageInfoUseCases.cs` - ステージ詳細画面での報酬表示
- **場所**: `StageVictoryResultModelFactory.cs` - クリア時の報酬計算・表示

### データ変更による影響（ゲーム体験の観点）

| カラム名 | ゲーム体験への影響 | 影響度 |
|---------|------------------|-------|
| `id` | 報酬レコードが参照できなくなり、該当報酬が獲得できなくなる | 🔴 高 |
| `mst_stage_id` | 報酬が別のステージに移動する。元のステージでは該当報酬が獲得できなくなる | 🔴 高 |
| `reward_category` | 報酬の獲得条件が変わる。`FirstClear`→`Always`にすると毎回獲得できるようになり、逆にすると初回のみになる。`Random`にするとドロップ抽選になる | 🔴 高 |
| `resource_type` | 報酬の種類が変わる。Coin→Itemにするとコインではなくアイテムが獲得できるようになる | 🔴 高 |
| `resource_id` | 獲得できる具体的なアイテムが変わる。例：アイテムAからアイテムBに変更 | 🔴 高 |
| `resource_amount` | 報酬の量が変わる。増やすとユーザーの収入が増え、減らすと収入が減る。ゲーム経済バランスに直結 | 🔴 高（経済バランス） |
| `percentage` | ランダム報酬のドロップ率が変わる。増やすとドロップしやすくなり、減らすとレアになる | 🔴 高 |
| `sort_order` | 報酬表示の順序が変わる。プレイヤーの視認性に影響 | 🟢 低 |

### 重要なビジネスロジック

1. **報酬カテゴリー分岐**:
   - `Always`: 毎回獲得する報酬
   - `FirstClear`: クリア一度目のみ
   - `Random`: 確率でドロップ
2. **抽選処理**: `percentage` に基づいてランダム報酬を抽選
3. **キャンペーン適用**: ドロップキャンペーン倍率を適用

---

## 4. mst_stage_event_rewards

### テーブル概要
イベントクエストのステージ報酬を管理するテーブル。通常報酬とは別に管理。

### 主要カラム
mst_stage_rewardsと同じ構造。

### データ変更による影響（ゲーム体験の観点）

`mst_stage_rewards`と同じ影響。イベントステージ専用の報酬テーブル。

---

## 5. mst_stage_end_conditions

### テーブル概要
ステージの終了条件を管理するテーブル。勝利条件・敗北条件を定義。

### 主要カラム
- `id`: 条件ID（UUID）
- `mst_stage_id`: ステージID
- `stage_end_type`: 終了タイプ（Victory/Defeat/Finish）
- `condition_type`: 条件タイプ（PlayerOutpostBreakDown/DefeatedEnemyCount/TimeLimit等）
- `condition_value1`/`condition_value2`: 条件値

### glow-serverでの使用箇所

#### Model
- **場所**: `api/app/Domain/Resource/Mst/Models/MstStageEndCondition.php`

#### 使用箇所
- **Resource**: `api/app/Http/Resources/Api/Masterdata/MstStageEndConditionResource.php`
  - マスターデータAPIで取得されクライアントに送信

### glow-clientでの使用箇所

#### Model
- **場所**: `Assets/GLOW/Scripts/Runtime/Core/Domain/Models/MasterData/MstStageEndConditionModel.cs`

#### Repository
- **インターフェース**: `Assets/GLOW/Scripts/Runtime/Core/Domain/Definitions/Repositories/MasterData/IMstStageEndConditionDataRepository.cs`

#### UseCase
- **場所**: `StageQuestInitializer.cs` - インゲーム初期化時に終了条件をゲーム設定に反映
- **場所**: `InGameSpecialRuleModelFactory.cs` - 敵撃破数制限、特定敵撃破、時間制限等の解析

### データ変更による影響（ゲーム体験の観点）

| カラム名 | ゲーム体験への影響 | 影響度 |
|---------|------------------|-------|
| `id` | 終了条件が参照できなくなり、ステージのクリア判定が機能しなくなる | 🔴 致命的 |
| `mst_stage_id` | 終了条件が別のステージに移動する | 🔴 高 |
| `stage_end_type` | 勝利条件か敗北条件かが変わる。Victory→Defeatにすると勝敗が逆転する | 🔴 高 |
| `condition_type` | クリア条件の種類が変わる。例：敵砦破壊→敵撃破数に変更すると、砦を壊してもクリアにならず、指定数の敵を倒す必要がある | 🔴 高 |
| `condition_value1` | 条件の具体的な値が変わる。例：撃破数10→20にすると難易度が上がる。時間制限60秒→30秒にするとタイムアタックが厳しくなる | 🔴 高 |
| `condition_value2` | 複数条件がある場合の2つ目の値が変わる | 🔴 高 |

### 重要なゲームロジック

1. **敵砦破壊勝利**: `condition_type == EnemyOutpostBreakDown` - デフォルト条件
2. **敵撃破数による勝利**: `condition_type == DefeatedEnemyCount` - `condition_value1`で敵数指定
3. **特定敵撃破による勝利**: `condition_type == DefeatUnit` - ボス敵撃破等
4. **時間制限**: `condition_type == TimeLimit` - スピードアタック制限時間

---

## 6. mst_in_games

### テーブル概要
インゲーム設定を管理するテーブル。ステージのビジュアル、BGM、敵パラメータ係数を定義。

### 主要カラム
- `id`: インゲームID（UUID）
- `mst_auto_player_sequence_id`: オートプレイシーケンスID
- `mst_auto_player_sequence_set_id`: オートプレイシーケンスセットID
- `bgm_asset_key`: BGMアセットキー
- `boss_bgm_asset_key`: ボスBGMアセットキー
- `loop_background_asset_key`: 背景アセットキー
- `player_outpost_asset_key`: プレイヤータワーアセットキー
- `mst_page_id`: ページID
- `mst_enemy_outpost_id`: 敵拠点ID
- `mst_defense_target_id`: 防御対象ID
- `boss_mst_enemy_stage_parameter_id`: ボスパラメータID
- `boss_count`: ボス数
- `normal_enemy_hp_coef`: ステージ内敵HP倍率（通常敵）
- `normal_enemy_attack_coef`: ステージ内敵攻撃倍率（通常敵）
- `normal_enemy_speed_coef`: ステージ内敵スピード倍率（通常敵）
- `boss_enemy_hp_coef`: ステージ内敵HP倍率（ボス）
- `boss_enemy_attack_coef`: ステージ内敵攻撃倍率（ボス）
- `boss_enemy_speed_coef`: ステージ内敵スピード倍率（ボス）

### glow-serverでの使用箇所

#### Model
- **場所**: `api/app/Domain/Resource/Mst/Models/MstInGame.php`

#### 使用箇所
- **Service**:
  - `api/app/Domain/InGame/Services/InGameSpecialRuleService.php`
  - `api/app/Domain/Stage/Services/StageService.php`

### glow-clientでの使用箇所

#### Model
- **場所**: `Assets/GLOW/Scripts/Runtime/Core/Domain/Definitions/Repositories/MasterData/IMstInGameModel.cs`（インターフェース）
- **実装**: `MstStageModel`が`IMstInGameModel`を実装

#### UseCase
- **場所**: `StageQuestInitializer.cs` - ゲームシーン初期化時にBGM、背景、敵配置を設定

### データ変更による影響（ゲーム体験の観点）

| カラム名 | ゲーム体験への影響 | 影響度 |
|---------|------------------|-------|
| `id` | インゲーム設定が参照できなくなり、ステージが正常に動作しなくなる | 🔴 致命的 |
| `normal_enemy_hp_coef` | 通常敵のHPが変わる。1.0→2.0にすると敵のHPが2倍になり難易度が上がる。0.5にすると半分になり簡単になる | 🔴 高（バランス） |
| `normal_enemy_attack_coef` | 通常敵の攻撃力が変わる。増やすと敵が強くなり、減らすと弱くなる | 🔴 高（バランス） |
| `normal_enemy_speed_coef` | 通常敵の移動速度が変わる。増やすと敵が速く動き、減らすと遅くなる | 🔴 高（バランス） |
| `boss_enemy_hp_coef` | ボス敵のHPが変わる。ボス戦の難易度に直結 | 🔴 高（バランス） |
| `boss_enemy_attack_coef` | ボス敵の攻撃力が変わる。ボス戦の難易度に直結 | 🔴 高（バランス） |
| `boss_enemy_speed_coef` | ボス敵の移動速度が変わる。ボス戦の難易度に直結 | 🔴 高（バランス） |
| `boss_count` | ボスの出現数が変わる。1→3にするとボスが3体出現し難易度が大幅に上がる | 🔴 高（バランス） |
| `bgm_asset_key` | ステージのBGMが変わる。雰囲気は変わるがゲームプレイには影響しない | 🟡 中 |
| `boss_bgm_asset_key` | ボス戦のBGMが変わる。雰囲気は変わるがゲームプレイには影響しない | 🟡 中 |
| `loop_background_asset_key` | 背景画像が変わる。見た目のみの変更 | 🟢 低 |
| `player_outpost_asset_key` | プレイヤー拠点の見た目が変わる。見た目のみの変更 | 🟢 低 |
| `mst_auto_player_sequence_set_id` | 敵の配置パターンが変わる。敵の出現タイミングや位置が変わり、難易度に影響 | 🔴 高（バランス） |
| `mst_page_id` | ページ設定が変わる。ステージの進行に影響 | 🟡 中 |
| `mst_enemy_outpost_id` | 敵拠点の設定が変わる。敵拠点のHPや防御力が変わり、難易度に影響 | 🔴 高（バランス） |
| `boss_mst_enemy_stage_parameter_id` | ボスの種類が変わる。別のボスに置き換わり、戦闘内容が大きく変わる | 🔴 高 |

### 重要なビジネスロジック

1. **ステージビジュアル管理**: BGM、背景、拠点のアセット指定
2. **敵パラメータ係数**: 敵のHP・攻撃・速度に乗算
3. **ボス管理**: ボス数と敵パラメータの関連

---

## 7. mst_enemy_stage_parameters

### テーブル概要
敵のステージパラメータを管理するテーブル。HP、攻撃力、移動速度、アビリティを定義。

### 主要カラム
- `id`: 敵パラメータID（UUID）
- `mst_enemy_character_id`: 敵キャラクターID
- `character_unit_kind`: ユニット種別（Normal/Boss等）
- `role_type`: ロールタイプ（Attack/Defense等）
- `color`: 影色（Red/Blue/Yellow/Green）
- `hp`: HP
- `attack_power`: 攻撃力
- `move_speed`: 移動速度
- `well_distance`: 索敵距離
- `damage_knock_back_count`: 被撃破までのノックバック回数

### glow-serverでの使用箇所

#### Model
- **場所**: `api/app/Domain/Resource/Mst/Models/MstEnemyStageParameter.php`

#### 使用箇所
- **Resource**: `api/app/Http/Resources/Api/Masterdata/MstEnemyStageParameterResource.php`
  - マスターデータAPIで取得されクライアントに送信

### glow-clientでの使用箇所

#### Model
- **場所**: `Assets/GLOW/Scripts/Runtime/Core/Domain/Models/MasterData/MstEnemyStageParameterModel.cs`

#### UseCase
- **場所**: `HomeStageInfoUseCases.cs` - ステージ詳細画面での敵表示
- **場所**: `StageQuestInitializer.cs` - ゲーム開始時の敵配置

### データ変更による影響（ゲーム体験の観点）

| カラム名 | ゲーム体験への影響 | 影響度 |
|---------|------------------|-------|
| `id` | 敵パラメータが参照できなくなり、該当の敵が出現しなくなる | 🔴 致命的 |
| `hp` | 敵のHPが変わる。増やすと敵が倒しにくくなり、減らすと倒しやすくなる。難易度に直結 | 🔴 高（バランス） |
| `attack_power` | 敵の攻撃力が変わる。増やすとプレイヤーが受けるダメージが増え、減らすと減る。難易度に直結 | 🔴 高（バランス） |
| `move_speed` | 敵の移動速度が変わる。増やすと敵が速く移動し、減らすと遅くなる。速いと迎撃が難しくなる | 🔴 高（バランス） |
| `well_distance` | 敵の索敵距離が変わる。増やすと遠くから敵が反応し、減らすと近づかないと反応しない | 🟡 中 |
| `damage_knock_back_count` | ノックバック回数が変わる。増やすと敵が倒れるまで多くの攻撃が必要になる | 🟡 中 |
| `color` | 敵の属性が変わる。プレイヤーキャラクターとの相性判定に影響。有利な色なら攻撃力UP、不利なら攻撃力DOWN | 🟡 中（バランス） |
| `role_type` | 敵のロールが変わる。Attack/Defense等で特性が変わる | 🟡 中 |
| `character_unit_kind` | 敵のカテゴリが変わる。Normal→Bossにするとボス扱いになる | 🔴 高 |
| `mst_enemy_character_id` | 敵キャラクターの参照が変わる。見た目やアニメーションが変わる | 🟡 中 |

### 重要なビジネスロジック

1. **敵スペック定義**: ステージに出現する敵の基本性能
2. **変身管理**: 条件に基づいて敵が別のパラメータに変身
3. **色属性**: プレイヤーキャラクターとの相性判定

---

## 8. mst_stage_clear_time_rewards

### テーブル概要
ステージクリアタイム報酬を管理するテーブル。クリアタイムが一定時間以内なら追加報酬。

### 主要カラム
- `id`: 報酬ID（UUID）
- `mst_stage_id`: ステージID
- `upper_clear_time_ms`: クリアタイム上限（ミリ秒）
- `resource_type`: リソースタイプ
- `resource_id`: リソースID
- `resource_amount`: リソース数量

### glow-serverでの使用箇所

#### Model
- **場所**: `api/app/Domain/Resource/Mst/Models/MstStageClearTimeReward.php`

#### Repository
- **場所**: `api/app/Domain/Resource/Mst/Repositories/MstStageClearTimeRewardRepository.php`

### glow-clientでの使用箇所

#### Model
- **場所**: `Assets/GLOW/Scripts/Runtime/Core/Domain/Models/MasterData/MstStageClearTimeRewardModel.cs`

### データ変更による影響（ゲーム体験の観点）

| カラム名 | ゲーム体験への影響 | 影響度 |
|---------|------------------|-------|
| `id` | タイム報酬が参照できなくなり、該当報酬が獲得できなくなる | 🔴 高 |
| `mst_stage_id` | タイム報酬が別のステージに移動する | 🔴 高 |
| `upper_clear_time_ms` | タイムアタックの制限時間が変わる。60000ms（60秒）→30000ms（30秒）にすると達成が難しくなる。逆に増やすと達成しやすくなる | 🟡 中 |
| `resource_type` | 報酬の種類が変わる。Coin→Itemにするとコインではなくアイテムが獲得できるようになる | 🔴 高 |
| `resource_id` | 獲得できる具体的なアイテムが変わる | 🔴 高 |
| `resource_amount` | 報酬の量が変わる。ユーザーの収入に直結 | 🔴 高（経済バランス） |

### 重要なビジネスロジック

1. **タイムアタック報酬**: クリアタイムが一定時間以内なら追加報酬
2. **段階的報酬**: 複数のタイムラインで異なる報酬を設定可能

---

## 9. mst_stage_enhance_reward_params

### テーブル概要
強化クエストのスコアアタック報酬パラメータを管理するテーブル。

### 主要カラム
- `id`: パラメータID（UUID）
- `min_threshold_score`: スコア最小閾値
- `coin_reward_amount`: コイン報酬額
- `coin_reward_size_type`: コイン報酬サイズタイプ（Small/Medium/Large）

### glow-serverでの使用箇所

#### Model
- **場所**: `api/app/Domain/Resource/Mst/Models/MstStageEnhanceRewardParam.php`

#### Repository
- **場所**: `api/app/Domain/Resource/Mst/Repositories/MstStageEnhanceRewardParamRepository.php`

### glow-clientでの使用箇所

#### Model
- **場所**: `Assets/GLOW/Scripts/Runtime/Core/Domain/Models/MasterData/MstStageEnhanceRewardParamModel.cs`

### データ変更による影響（ゲーム体験の観点）

| カラム名 | ゲーム体験への影響 | 影響度 |
|---------|------------------|-------|
| `id` | スコア報酬パラメータが参照できなくなり、スコアアタックの報酬が正常に計算されなくなる | 🔴 高 |
| `min_threshold_score` | スコア達成の閾値が変わる。1000→2000にすると達成が難しくなり、逆に減らすと達成しやすくなる | 🟡 中 |
| `coin_reward_amount` | 獲得できるコインの量が変わる。ユーザーの収入に直結 | 🔴 高（経済バランス） |
| `coin_reward_size_type` | 報酬のサイズ表示が変わる。Small/Medium/Largeの表示に影響。直接的なゲームプレイへの影響は少ない | 🟢 低 |

### 重要なビジネスロジック

1. **スコア基準報酬**: スコア達成度に応じた報酬量の決定
2. **段階的クリア**: 複数の閾値で報酬を設定可能

---

## 10. mst_stage_event_settings

### テーブル概要
イベントステージの設定を管理するテーブル。期間制限、クリア回数制限、広告リトライを制御。

### 主要カラム
- `id`: イベント設定ID（UUID）
- `mst_stage_id`: ステージID
- `reset_type`: リセットタイプ（DAILY/NONE）
- `clearable_count`: クリア可能回数
- `ad_challenge_count`: 広告チャレンジ回数
- `background_asset_key`: 背景アセットキー
- `mst_stage_rule_group_id`: ステージルールグループID
- `start_at`/`end_at`: 開催期間

### glow-serverでの使用箇所

#### Model
- **場所**: `api/app/Domain/Resource/Mst/Models/MstStageEventSetting.php`

#### Repository
- **場所**: `api/app/Domain/Resource/Mst/Repositories/MstStageEventSettingRepository.php`

#### Service
- **場所**: `api/app/Domain/Stage/Services/StageService.php`
  - ステージ開始時のリセット判定
  - 挑戦回数制限チェック

### glow-clientでの使用箇所

#### Model
- **場所**: `Assets/GLOW/Scripts/Runtime/Core/Domain/Models/MasterData/MstStageEventSettingModel.cs`

#### Repository
- **インターフェース**: `Assets/GLOW/Scripts/Runtime/Core/Domain/Definitions/Repositories/MasterData/IMstStageEventSettingDataRepository.cs`

### データ変更による影響（ゲーム体験の観点）

| カラム名 | ゲーム体験への影響 | 影響度 |
|---------|------------------|-------|
| `id` | イベント設定が参照できなくなり、イベントステージが正常に動作しなくなる | 🔴 致命的 |
| `mst_stage_id` | イベント設定が別のステージに移動する | 🔴 高 |
| `reset_type` | リセットタイプが変わる。NONE→DAILYにすると毎日回数がリセットされ、プレイ機会が増える。逆にするとリセットされなくなり、プレイ機会が減る | 🔴 高 |
| `clearable_count` | クリア可能回数が変わる。3→10にすると1日10回までプレイできるようになる。減らすとプレイ機会が減る | 🔴 高 |
| `ad_challenge_count` | 広告視聴でのリトライ回数が変わる。増やすとスタミナ消費なしでプレイできる回数が増える | 🟡 中 |
| `start_at`/`end_at` | イベントの開催期間が変わる。期間外はイベントステージがプレイ不可になる | 🔴 高 |
| `background_asset_key` | イベント画面の背景が変わる。見た目のみの変更 | 🟢 低 |
| `mst_stage_rule_group_id` | ステージルールグループが変わる。特殊ルールの適用に影響 | 🟡 中 |

### 重要なビジネスロジック

1. **デイリーリセット**: `reset_type='DAILY'` の場合、毎日回数が復活
2. **回数制限管理**: `clearable_count` + 広告挑戦 `ad_challenge_count` の上限
3. **期間管理**: `start_at` / `end_at` でイベント開催期間を管理

---

## 11. mst_stage_tips

### テーブル概要
ステージヒントを管理するテーブル。多言語対応のヒントテキスト。

### 主要カラム
- `id`: ヒントID
- `language`: 言語
- `mst_stage_tips_group_id`: ステージヒントグループID
- `title`: ヒントタイトル
- `description`: ヒント説明文

### glow-serverでの使用箇所

- `mst_stages`テーブル内の`mst_stage_tips_group_id`で参照

### glow-clientでの使用箇所

- マスターデータとして取得し、ステージ詳細画面でヒントを表示

### データ変更による影響（ゲーム体験の観点）

| カラム名 | ゲーム体験への影響 | 影響度 |
|---------|------------------|-------|
| `id` | ヒントが参照できなくなり、ステージヒントが表示されなくなる | 🟡 中 |
| `language` | 言語設定が変わる。表示言語に影響 | 🟡 中 |
| `mst_stage_tips_group_id` | ヒントグループが変わる。表示されるヒントのセットが変わる | 🟢 低 |
| `title` | ヒントのタイトルが変わる。プレイヤーへの補助情報のみで直接的な影響は少ない | 🟢 低 |
| `description` | ヒントの説明文が変わる。プレイヤーへの補助情報のみで直接的な影響は少ない | 🟢 低 |

### 重要なビジネスロジック

1. **マルチ言語対応**: 言語別にヒントを管理
2. **グループ管理**: グループIDで複数ヒントをまとめる

---

## 12. mst_quest_bonus_units

### テーブル概要
クエストボーナスユニットを管理するテーブル。特定ユニットを編成するとコインボーナス獲得。

### 主要カラム
- `id`: ボーナスユニットID（UUID）
- `mst_quest_id`: クエストID
- `mst_unit_id`: ユニットID
- `coin_bonus_rate`: コインボーナス倍率
- `start_at`/`end_at`: 開催期間

### glow-serverでの使用箇所

#### Model
- **場所**: `api/app/Domain/Resource/Mst/Models/MstQuestBonusUnit.php`

#### Repository
- **場所**: `api/app/Domain/Resource/Mst/Repositories/MstQuestBonusUnitRepository.php`

### glow-clientでの使用箇所

- マスターデータとして取得し、クエスト選択画面でボーナスユニットを表示

### データ変更による影響（ゲーム体験の観点）

| カラム名 | ゲーム体験への影響 | 影響度 |
|---------|------------------|-------|
| `id` | ボーナスユニット設定が参照できなくなり、該当ユニットのボーナスが適用されなくなる | 🔴 高 |
| `mst_quest_id` | ボーナスが適用されるクエストが変わる | 🔴 高 |
| `mst_unit_id` | ボーナス対象のユニットが変わる。ユニットAからユニットBに変更すると、ユニットAのボーナスが消え、ユニットBがボーナス対象になる | 🟡 中 |
| `coin_bonus_rate` | ボーナス倍率が変わる。1.5→2.0にするとコイン獲得量が1.5倍から2倍になる。ユーザーの収入に直結 | 🔴 高（経済バランス） |
| `start_at`/`end_at` | ボーナス期間が変わる。期間外はボーナスが適用されなくなる | 🟡 中 |

### 重要なビジネスロジック

1. **キャラクターボーナス**: 特定ユニットを編成するとコインボーナス獲得
2. **期間限定**: イベントごとに異なるユニットがボーナス対象になる
3. **複合ボーナス**: キャンペーン倍率と乗算される可能性

---

## 13. mst_quest_event_bonus_schedules

### テーブル概要
クエストイベントボーナススケジュールを管理するテーブル。期間限定ボーナスの設定。

### 主要カラム
- `id`: スケジュールID（UUID）
- `mst_quest_id`: クエストID
- `event_bonus_group_id`: ボーナスグループID
- `start_at`/`end_at`: 開催期間

### glow-serverでの使用箇所

#### Model
- **場所**: `api/app/Domain/Resource/Mst/Models/MstQuestEventBonusSchedule.php`

#### Repository
- **場所**: `api/app/Domain/Resource/Mst/Repositories/MstQuestEventBonusScheduleRepository.php`

### glow-clientでの使用箇所

- マスターデータとして取得し、イベント画面でボーナス期間を表示

### データ変更による影響（ゲーム体験の観点）

| カラム名 | ゲーム体験への影響 | 影響度 |
|---------|------------------|-------|
| `id` | ボーナススケジュールが参照できなくなり、イベントボーナスが適用されなくなる | 🔴 高 |
| `mst_quest_id` | ボーナスが適用されるクエストが変わる | 🔴 高 |
| `event_bonus_group_id` | ボーナスグループが変わる。適用されるボーナスの内容・種類が変わる（ドロップ率UP、経験値UP等） | 🟡 中 |
| `start_at`/`end_at` | ボーナス期間が変わる。期間外はボーナスが適用されなくなる | 🟡 中 |

### 重要なビジネスロジック

1. **イベントボーナス管理**: クエスト毎の期間限定ボーナス設定
2. **グループ化**: ボーナスグループで複数の関連設定をまとめる
3. **期間管理**: 複数のボーナス期間を管理可能

---

## テーブル依存関係図

```
mst_quests (クエストマスター)
    ├── mst_stages (ステージマスター)
    │   ├── mst_stage_rewards (通常ステージ報酬)
    │   ├── mst_stage_event_rewards (イベントステージ報酬)
    │   ├── mst_stage_event_settings (イベント設定)
    │   ├── mst_stage_clear_time_rewards (タイム報酬)
    │   ├── mst_stage_enhance_reward_params (強化報酬)
    │   ├── mst_stage_end_conditions (終了条件)
    │   ├── mst_stage_tips (ヒント)
    │   └── mst_in_games (インゲーム設定)
    │       └── mst_enemy_stage_parameters (敵パラメータ)
    ├── mst_quest_bonus_units (ユニットボーナス)
    └── mst_quest_event_bonus_schedules (イベントボーナススケジュール)
```

---

## データ変更時の影響度マトリクス

### 高リスク（🔴）- ゲーム進行に直結

| テーブル | カラム | ゲーム体験への影響 |
|---------|------|------------------|
| mst_stages | id | ステージが完全に参照できなくなりプレイ不可 |
| mst_stages | cost_stamina | プレイ回数・周回効率に直結 |
| mst_stages | start_at/end_at | ステージのプレイ可否に直結 |
| mst_stages | prev_mst_stage_id | ストーリー進行順序の変更 |
| mst_stages | exp, coin | プレイヤーの収入・成長速度に直結 |
| mst_quests | id | クエスト全体がプレイ不可 |
| mst_quests | quest_type | ゲームロジックの根本的な変更 |
| mst_quests | start_date/end_date | クエスト全体のプレイ可否に直結 |
| mst_stage_rewards | resource_amount | ユーザーの収入に直結（経済バランス） |
| mst_stage_rewards | percentage | ドロップ率の変更（経済バランス） |
| mst_stage_rewards | reward_category | 報酬獲得条件の変更 |
| mst_stage_end_conditions | condition_type | クリア条件の根本的な変更 |
| mst_stage_end_conditions | condition_value1/2 | 難易度の直接的な変更 |
| mst_in_games | normal_enemy_hp_coef等 | 敵の強さが大きく変わる |
| mst_in_games | boss_count | ボス数の変更（難易度に直結） |
| mst_enemy_stage_parameters | hp/attack_power | ステージ難易度が大きく変わる |
| mst_enemy_stage_parameters | move_speed | 敵の行動速度変更（難易度に影響） |
| mst_stage_clear_time_rewards | resource_amount | ユーザーの収入に直結 |
| mst_stage_enhance_reward_params | coin_reward_amount | ユーザーの収入に直結 |
| mst_stage_event_settings | clearable_count | プレイ可能回数に直結 |
| mst_stage_event_settings | reset_type | プレイ機会の増減に直結 |
| mst_stage_event_settings | start_at/end_at | イベントのプレイ可否に直結 |
| mst_quest_bonus_units | coin_bonus_rate | ユーザーの収入に直結 |

### 中リスク（🟡）- ユーザー体験に影響

| テーブル | カラム | ゲーム体験への影響 |
|---------|------|------------------|
| mst_stages | recommended_level | 推奨レベル表示の変更 |
| mst_stages | auto_lap_type | 周回機能の利用タイミング変更 |
| mst_stages | max_auto_lap_count | 周回効率の変更 |
| mst_quests | difficulty | 難易度表示の変更 |
| mst_in_games | bgm_asset_key等 | 雰囲気の変更（ゲームプレイには影響少） |
| mst_enemy_stage_parameters | color | 相性判定への影響 |
| mst_stage_clear_time_rewards | upper_clear_time_ms | タイムアタックの難易度変更 |
| mst_stage_enhance_reward_params | min_threshold_score | スコア達成の難易度変更 |
| mst_quest_bonus_units | mst_unit_id | ボーナス対象ユニットの変更 |
| mst_quest_event_bonus_schedules | event_bonus_group_id | ボーナス内容の変更 |
| mst_stage_event_settings | ad_challenge_count | 広告リトライ回数の変更 |

### 低リスク（🟢）- 限定的な影響

| テーブル | カラム | ゲーム体験への影響 |
|---------|------|------------------|
| mst_stages | asset_key | ステージビジュアル変更（見た目のみ） |
| mst_stages | sort_order | 表示順序の変更 |
| mst_quests | quest_group | グループ表示制御 |
| mst_quests | asset_key | クエストビジュアル変更（見た目のみ） |
| mst_stage_tips | title/description | ヒントテキストの変更 |
| mst_stage_event_settings | background_asset_key | 背景画像の変更（見た目のみ） |

---

## 主要APIエンドポイント

### ステージ関連（StageController）
- **POST** `/api/stage/start` - ステージ開始
  - 参照テーブル: mst_stages, mst_quests, mst_stage_event_settings, mst_in_games
- **POST** `/api/stage/end` - ステージ終了
  - 参照テーブル: mst_stages, mst_quests, mst_stage_rewards, mst_stage_event_rewards, mst_stage_clear_time_rewards, mst_stage_enhance_reward_params
- **POST** `/api/stage/continue_diamond` - ダイアモンド継続
- **POST** `/api/stage/continue_ad` - 広告視聴継続
- **POST** `/api/stage/abort` - ステージ中止

---

## 実装時の注意点

### glow-server
1. **期間管理**: 猶予期間を考慮した期間チェック（`getStageGracePeriod`）
2. **キャンペーン適用**: 報酬計算時にキャンペーン倍率を適用
3. **トランザクション管理**: 報酬付与時の排他制御

### glow-client
1. **キャッシング**: マスターデータはメモリにキャッシュ（次回起動時に反映）
2. **難読化**: ObscuredInt/ObscuredFloat等でチート対策
3. **ポリモーフィズム**: IMstInGameModelインターフェースで異なる敵配置系を統一的に扱う
4. **ValueObjectパターン**: 敵パラメータ係数等をValueObjectで管理

---

## まとめ

これらのテーブルはゲームのステージ・クエストシステムの核を形成しており、相互に密接に関連しています。特に`mst_stages` → `mst_quests`の親子関係と、各報酬テーブルの分岐構造（通常/イベント）が重要な設計パターンになっています。データの追加・変更時には、関連テーブル全体への影響を慎重に検討し、テストを実施する必要があります。

**特に注意すべき点**:
- **経済バランス**: `cost_stamina`, `exp`, `coin`, 報酬の`resource_amount`, ボーナスの`coin_bonus_rate`等はユーザーの収入・成長速度に直結
- **難易度バランス**: 敵パラメータ係数、`hp`, `attack_power`, `move_speed`, 終了条件の`condition_value`等は難易度に直結
- **プレイ機会**: `start_at`/`end_at`, `clearable_count`, `reset_type`等はユーザーのプレイ可否に直結
- **ゲームロジック**: `quest_type`, `condition_type`, `reward_category`等はゲームの根本的な挙動を決定

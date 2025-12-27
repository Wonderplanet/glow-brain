# テーブルカタログ

GLOWプロジェクトの全165テーブルを10カテゴリに分類し、詳細な情報を提供します。

## 目次

1. [テーブル命名規則](#テーブル命名規則)
2. [10カテゴリ概要](#10カテゴリ概要)
3. [カテゴリ別詳細リスト](#カテゴリ別詳細リスト)
4. [テーブル関連図](#テーブル関連図)
5. [主要enum型](#主要enum型)

## テーブル命名規則

### プレフィックスによる分類

| プレフィックス | 意味 | 特徴 | 例 |
|-------------|------|------|-----|
| `Mst*` | Master Data | 固定的なゲームデータ（154テーブル） | MstCharacter, MstStage |
| `Opr*` | Operation Data | 運営施策・期間限定データ（11テーブル） | OprEvent, OprGacha |

### サフィックスによる分類

| サフィックス | 意味 | 特徴 | 例 |
|-----------|------|------|-----|
| `*I18n` | Internationalization | 多言語対応テーブル（約40テーブル） | MstCharacterI18n, MstStageI18n |
| `*Group` | Group/Set | グループ化されたデータ | MstRewardGroup, MstDropGroup |
| `*Content` | Content/Detail | 親テーブルの詳細データ | MstPackContent, MstEventContent |
| `*Relation` | Relation/Mapping | 多対多の関連データ | MstCharacterSkillRelation |

### データベース分離

| データベース | 用途 | テーブル数 |
|-----------|------|----------|
| `mst` | マスターデータ（読み取り専用） | 165テーブル |
| `mysql` | ユーザーデータ・ログ | - |
| `admin` | 管理画面専用 | - |
| `mng` | 管理系データ | - |

## 10カテゴリ概要

| # | カテゴリ | テーブル数 | 主要テーブル |
|---|---------|----------|-----------|
| 1 | キャラクター関連 | ~10 | MstCharacter, MstRarity, MstClass |
| 2 | 攻撃・スキル関連 | ~5 | MstSkill, MstAutoSkill, MstLeaderSkill |
| 3 | クエスト・ステージ | ~8 | MstQuest, MstStage, MstEnemy |
| 4 | ガチャ関連 | ~6 | OprGacha, OprGachaStep, MstGachaAnimation |
| 5 | ミッション関連 | ~15 | MstMission*, OprMission* |
| 6 | アイテム・報酬 | ~8 | MstItem, MstRewardGroup, MstDropGroup |
| 7 | イベント関連 | ~8 | OprEvent, MstEvent*, OprEventPoint |
| 8 | 降臨バトル | ~6 | MstAdventBattle, MstAdventBattleStage |
| 9 | 多言語対応 | ~40 | *I18n テーブル群 |
| 10 | その他 | ~60 | Shop, Currency, Tutorial, etc. |

## カテゴリ別詳細リスト

### 1. キャラクター関連（~10テーブル）

キャラクターの基本情報、ステータス、育成要素を管理。

#### 主要テーブル

**MstCharacter**
- 役割: キャラクターの基本情報
- 主要カラム: `id`, `rarity_id`, `class_id`, `max_level`, `base_hp`, `base_attack`
- 関連: MstRarity, MstClass, MstCharacterSkillRelation
- 多言語: MstCharacterI18n（名前、説明文）

**MstRarity**
- 役割: レアリティ定義（★1〜★5など）
- 主要カラム: `id`, `sort_order`, `growth_rate`
- 特徴: レアリティごとの成長率を定義

**MstClass**
- 役割: クラス・属性定義
- 主要カラム: `id`, `class_type`, `element_type`
- 多言語: MstClassI18n

**MstCharacterSkillRelation**
- 役割: キャラクターとスキルの紐付け
- 主要カラム: `mst_character_id`, `mst_skill_id`, `unlock_level`
- 特徴: 多対多の関連テーブル

**MstCharacterAwakening**
- 役割: キャラクター覚醒設定
- 主要カラム: `mst_character_id`, `awakening_level`, `required_item_id`

**MstCharacterEvolution**
- 役割: キャラクター進化設定
- 主要カラム: `from_character_id`, `to_character_id`, `required_items`

#### 関連するI18nテーブル

- MstCharacterI18n
- MstClassI18n
- MstCharacterAwakeningI18n

### 2. 攻撃・スキル関連（~5テーブル）

バトルシステムで使用されるスキル・攻撃アクションを管理。

#### 主要テーブル

**MstSkill**
- 役割: アクティブスキル定義
- 主要カラム: `id`, `skill_type`, `target_type`, `damage_multiplier`, `cooldown`
- 多言語: MstSkillI18n

**MstAutoSkill**
- 役割: パッシブスキル定義
- 主要カラム: `id`, `trigger_type`, `effect_type`, `effect_value`
- 多言語: MstAutoSkillI18n

**MstLeaderSkill**
- 役割: リーダースキル定義
- 主要カラム: `id`, `target_condition`, `effect_type`, `effect_value`
- 多言語: MstLeaderSkillI18n

**MstAttackAction**
- 役割: 攻撃アクションの詳細設定
- 主要カラム: `id`, `action_type`, `animation_id`, `hit_count`

**MstBuffEffect**
- 役割: バフ・デバフ効果定義
- 主要カラム: `id`, `effect_type`, `duration`, `stack_type`

#### 関連するI18nテーブル

- MstSkillI18n
- MstAutoSkillI18n
- MstLeaderSkillI18n

### 3. クエスト・ステージ（~8テーブル）

ゲームのステージ構成、敵配置、報酬を管理。

#### 主要テーブル

**MstQuest**
- 役割: クエストの大分類
- 主要カラム: `id`, `quest_type`, `unlock_condition`, `sort_order`
- 多言語: MstQuestI18n

**MstStage**
- 役割: ステージ（バトル）の詳細
- 主要カラム: `id`, `mst_quest_id`, `stamina_cost`, `drop_group_id`, `first_clear_reward_group_id`
- 関連: MstQuest, MstDropGroup, MstRewardGroup

**MstEnemy**
- 役割: 敵キャラクター定義
- 主要カラム: `id`, `enemy_type`, `hp`, `attack`, `defense`
- 多言語: MstEnemyI18n

**MstWave**
- 役割: ステージのウェーブ（Wave）構成
- 主要カラム: `mst_stage_id`, `wave_number`, `enemy_group_id`

**MstEnemyGroup**
- 役割: 敵グループ（ウェーブ内の敵配置）
- 主要カラム: `id`, `enemy_positions`

**MstDropGroup**
- 役割: ドロップ報酬グループ
- 主要カラム: `id`, `drop_items`, `drop_rates`

**MstStageGimmick**
- 役割: ステージギミック（トラップ、特殊効果）
- 主要カラム: `mst_stage_id`, `gimmick_type`, `trigger_condition`

#### 関連するI18nテーブル

- MstQuestI18n
- MstStageI18n
- MstEnemyI18n

### 4. ガチャ関連（~6テーブル）

ガチャシステムの設定と提供割合を管理。

#### 主要テーブル

**OprGacha**
- 役割: ガチャの基本設定（運営データ）
- 主要カラム: `id`, `gacha_type`, `start_at`, `end_at`, `cost_currency_id`, `cost_amount`
- 特徴: 期間限定のため`Opr*`プレフィックス
- 多言語: OprGachaI18n

**OprGachaStep**
- 役割: ステップアップガチャの段階設定
- 主要カラム: `opr_gacha_id`, `step_number`, `cost_override`, `guaranteed_reward`

**MstGachaAnimation**
- 役割: ガチャ演出設定
- 主要カラム: `id`, `rarity_id`, `animation_type`, `effect_id`

**MstGachaLineup**
- 役割: ガチャ提供アイテム・キャラクター
- 主要カラム: `opr_gacha_id`, `reward_type`, `reward_id`, `weight`, `is_pickup`

**MstGachaPity**
- 役割: 天井システム設定
- 主要カラム: `opr_gacha_id`, `pity_count`, `guaranteed_reward_id`

#### 関連するI18nテーブル

- OprGachaI18n

### 5. ミッション関連（~15テーブル）

デイリー、ウィークリー、イベントミッションを管理。

#### 主要テーブル

**MstMission**
- 役割: 恒常ミッションの定義
- 主要カラム: `id`, `mission_type`, `mission_category`, `target_type`, `target_value`, `reward_group_id`
- 多言語: MstMissionI18n

**OprMission**
- 役割: 期間限定ミッションの定義
- 主要カラム: `id`, `start_at`, `end_at`, `mission_type`, `target_type`, `reward_group_id`
- 多言語: OprMissionI18n

**MstMissionDaily**
- 役割: デイリーミッション設定
- 主要カラム: `id`, `reset_hour`, `mst_mission_id`

**MstMissionWeekly**
- 役割: ウィークリーミッション設定
- 主要カラム: `id`, `reset_day_of_week`, `mst_mission_id`

**MstMissionEvent**
- 役割: イベントミッション設定
- 主要カラム: `id`, `opr_event_id`, `mst_mission_id`

**MstMissionCategory**
- 役割: ミッションカテゴリ定義
- 主要カラム: `id`, `category_name`, `sort_order`
- 多言語: MstMissionCategoryI18n

**MstMissionGroup**
- 役割: ミッショングループ（連続ミッション）
- 主要カラム: `id`, `group_type`, `mission_ids`

**MstMissionPanel**
- 役割: ミッションパネル（ビンゴ形式）
- 主要カラム: `id`, `panel_layout`, `mission_ids`, `completion_reward_group_id`

#### その他のミッション関連テーブル

- MstMissionAchievement（実績系ミッション）
- MstMissionLogin（ログインボーナス）
- MstMissionChallenge（チャレンジミッション）
- MstMissionLimitedTime（期間限定ミッション）

#### 関連するI18nテーブル

- MstMissionI18n
- OprMissionI18n
- MstMissionCategoryI18n

### 6. アイテム・報酬（~8テーブル）

アイテム、通貨、報酬グループを管理。

#### 主要テーブル

**MstItem**
- 役割: アイテムの基本情報
- 主要カラム: `id`, `item_type`, `item_category`, `rarity`, `max_stack`, `sell_price`
- 多言語: MstItemI18n

**MstCurrency**
- 役割: ゲーム内通貨定義
- 主要カラム: `id`, `currency_type`, `is_paid`, `max_amount`
- 多言語: MstCurrencyI18n

**MstRewardGroup**
- 役割: 報酬グループ（複数報酬のセット）
- 主要カラム: `id`, `reward_items`, `reward_amounts`
- 特徴: JSON形式で複数報酬を格納

**MstDropGroup**
- 役割: ドロップ報酬グループ（確率付き）
- 主要カラム: `id`, `drop_items`, `drop_rates`, `guaranteed_items`

**MstExchangeItem**
- 役割: アイテム交換設定
- 主要カラム: `id`, `required_item_id`, `required_amount`, `exchanged_item_id`, `exchanged_amount`

**MstConsumableItem**
- 役割: 消費アイテム設定
- 主要カラム: `mst_item_id`, `effect_type`, `effect_value`, `duration`

**MstMaterial**
- 役割: 素材アイテム設定
- 主要カラム: `mst_item_id`, `material_type`, `usage_category`

**MstTreasureBox**
- 役割: 宝箱・ガチャチケット設定
- 主要カラム: `mst_item_id`, `reward_group_id`, `animation_id`

#### 関連するI18nテーブル

- MstItemI18n
- MstCurrencyI18n

### 7. イベント関連（~8テーブル）

期間限定イベントの設定を管理。

#### 主要テーブル

**OprEvent**
- 役割: イベントの基本設定
- 主要カラム: `id`, `event_type`, `start_at`, `end_at`, `banner_image_url`
- 特徴: 期間限定のため`Opr*`プレフィックス
- 多言語: OprEventI18n

**MstEventType**
- 役割: イベントタイプ定義
- 主要カラム: `id`, `type_name`, `point_system_enabled`
- 例: ポイントイベント、ランキングイベント、レイドイベント

**OprEventPoint**
- 役割: イベントポイント報酬設定
- 主要カラム: `opr_event_id`, `threshold_points`, `reward_group_id`

**OprEventRanking**
- 役割: イベントランキング報酬設定
- 主要カラム: `opr_event_id`, `rank_from`, `rank_to`, `reward_group_id`

**MstEventExchange**
- 役割: イベント交換所設定
- 主要カラム: `opr_event_id`, `mst_item_id`, `exchange_cost`, `exchange_limit`

**MstEventStory**
- 役割: イベントストーリー設定
- 主要カラム: `opr_event_id`, `story_id`, `unlock_condition`

**OprEventBanner**
- 役割: イベントバナー表示設定
- 主要カラム: `opr_event_id`, `banner_type`, `display_priority`, `image_url`

**MstEventBoss**
- 役割: イベントボス設定（レイドボスなど）
- 主要カラム: `opr_event_id`, `mst_enemy_id`, `hp_multiplier`, `reward_group_id`

#### 関連するI18nテーブル

- OprEventI18n

### 8. 降臨バトル（~6テーブル）

高難易度の降臨バトルコンテンツを管理。

#### 主要テーブル

**MstAdventBattle**
- 役割: 降臨バトルの基本設定
- 主要カラム: `id`, `difficulty`, `unlock_condition`, `clear_reward_group_id`
- 多言語: MstAdventBattleI18n

**MstAdventBattleStage**
- 役割: 降臨バトルのステージ構成
- 主要カラム: `mst_advent_battle_id`, `stage_number`, `mst_enemy_id`, `hp_multiplier`

**MstAdventBattleSchedule**
- 役割: 降臨バトルの開催スケジュール
- 主要カラム: `mst_advent_battle_id`, `day_of_week`, `start_hour`, `end_hour`

**MstAdventBattleReward**
- 役割: 降臨バトル報酬設定
- 主要カラム: `mst_advent_battle_id`, `clear_type`, `reward_group_id`
- clear_type例: 初回クリア、ノーコンティニュー、スコア達成

**MstAdventBattleMission**
- 役割: 降臨バトル専用ミッション
- 主要カラム: `mst_advent_battle_id`, `mission_condition`, `reward_group_id`

**MstAdventBattleRanking**
- 役割: 降臨バトルランキング設定
- 主要カラム: `mst_advent_battle_id`, `rank_from`, `rank_to`, `reward_group_id`

#### 関連するI18nテーブル

- MstAdventBattleI18n

### 9. 多言語対応（~40テーブル）

ゲーム内テキストの多言語対応を管理。

#### 命名規則

すべてのI18nテーブルは`*I18n`サフィックスを持つ。

#### 主要な構造

**共通カラム**:
- `mst_*_id`: 親テーブルのID（外部キー）
- `language`: 言語コード（ja, en, zh-CN, zh-TW など）
- `name`: 名称
- `description`: 説明文
- その他のテキストフィールド

#### I18nテーブル一覧（抜粋）

**キャラクター関連**:
- MstCharacterI18n
- MstClassI18n
- MstRarityI18n

**スキル関連**:
- MstSkillI18n
- MstAutoSkillI18n
- MstLeaderSkillI18n

**クエスト・ステージ関連**:
- MstQuestI18n
- MstStageI18n
- MstEnemyI18n

**アイテム関連**:
- MstItemI18n
- MstCurrencyI18n

**ミッション関連**:
- MstMissionI18n
- OprMissionI18n
- MstMissionCategoryI18n

**イベント関連**:
- OprEventI18n
- OprGachaI18n

**その他**:
- MstShopI18n
- MstTutorialI18n
- MstAnnouncementI18n
- MstNewsI18n
- MstTipsI18n
- など約40テーブル

### 10. その他（~60テーブル）

上記9カテゴリに含まれない様々な機能を管理。

#### ショップ関連

**MstShop**
- 役割: ショップ設定
- 主要カラム: `id`, `shop_type`, `display_order`

**MstPack**
- 役割: 課金パック設定
- 主要カラム: `id`, `product_id`, `price`, `reward_group_id`

**OprProduct**
- 役割: 期間限定商品設定
- 主要カラム: `id`, `start_at`, `end_at`, `mst_pack_id`, `discount_rate`

**MstShopItem**
- 役割: ショップアイテム設定
- 主要カラム: `mst_shop_id`, `mst_item_id`, `cost_currency_id`, `cost_amount`, `purchase_limit`

#### チュートリアル関連

**MstTutorial**
- 役割: チュートリアル設定
- 主要カラム: `id`, `tutorial_type`, `step_order`, `trigger_condition`

**MstTutorialStep**
- 役割: チュートリアルステップ詳細
- 主要カラム: `mst_tutorial_id`, `step_number`, `ui_element`, `message`

#### PvP関連

**MstPvpSeason**
- 役割: PvPシーズン設定
- 主要カラム: `id`, `season_number`, `start_at`, `end_at`

**MstPvpRank**
- 役割: PvPランク設定
- 主要カラム: `id`, `rank_name`, `required_rating`, `season_reward_group_id`

**MstPvpReward**
- 役割: PvP報酬設定
- 主要カラム: `rank_from`, `rank_to`, `reward_group_id`

#### ギルド関連

**MstGuild**
- 役割: ギルド設定
- 主要カラム: `id`, `max_members`, `required_level`

**MstGuildRole**
- 役割: ギルド役職設定
- 主要カラム: `id`, `role_name`, `permissions`

**MstGuildFacility**
- 役割: ギルド施設設定
- 主要カラム: `id`, `facility_type`, `max_level`, `upgrade_cost`

#### フレンド関連

**MstFriendshipLevel**
- 役割: フレンドシップレベル設定
- 主要カラム: `level`, `required_points`, `reward_group_id`

**MstFriendGift**
- 役割: フレンドギフト設定
- 主要カラム: `id`, `gift_type`, `daily_send_limit`, `reward_group_id`

#### 装備関連

**MstEquipment**
- 役割: 装備アイテム設定
- 主要カラム: `id`, `equipment_type`, `rarity`, `base_stats`

**MstEquipmentEnhancement**
- 役割: 装備強化設定
- 主要カラム: `mst_equipment_id`, `enhancement_level`, `required_items`, `stat_bonus`

#### バトルパス関連

**OprBattlePass**
- 役割: バトルパス設定
- 主要カラム: `id`, `season_number`, `start_at`, `end_at`, `max_level`

**MstBattlePassReward**
- 役割: バトルパス報酬設定
- 主要カラム: `opr_battle_pass_id`, `level`, `free_reward_group_id`, `premium_reward_group_id`

#### お知らせ・ニュース関連

**MstAnnouncement**
- 役割: お知らせ設定
- 主要カラム: `id`, `announcement_type`, `priority`, `start_at`, `end_at`

**MstNews**
- 役割: ニュース設定
- 主要カラム: `id`, `news_category`, `published_at`, `thumbnail_url`

#### 設定・マスタ関連

**MstConfig**
- 役割: ゲーム設定値
- 主要カラム: `key`, `value`, `value_type`

**MstConstant**
- 役割: 定数値定義
- 主要カラム: `key`, `value`, `description`

#### その他多数

- MstLoginBonus（ログインボーナス）
- MstAchievement（実績）
- MstTitle（称号）
- MstVoice（ボイス）
- MstBgm（BGM）
- MstSe（効果音）
- MstMovie（ムービー）
- MstIllustration（イラスト）
- MstStory（ストーリー）
- MstCharacterProfile（キャラクタープロフィール）
- MstTips（Tips）
- MstHelp（ヘルプ）
- など約60テーブル

## テーブル関連図

### 1. イベントシステム

```
OprEvent（イベント基本設定）
  ├─ OprEventI18n（多言語）
  ├─ MstEventType（イベントタイプ）
  ├─ OprEventPoint（ポイント報酬）
  │    └─ MstRewardGroup（報酬グループ）
  ├─ OprEventRanking（ランキング報酬）
  │    └─ MstRewardGroup
  ├─ MstEventExchange（イベント交換所）
  │    └─ MstItem（アイテム）
  ├─ MstEventStory（イベントストーリー）
  ├─ OprEventBanner（バナー表示）
  └─ MstEventBoss（イベントボス）
       └─ MstEnemy（敵キャラクター）
```

**調査方法**:
```bash
# OprEventテーブルの全カラム確認
jq '.databases.mst.tables.opr_events' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json

# イベント関連テーブル一覧
jq '.databases.mst.tables | keys | map(select(test("event"; "i")))' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### 2. ガチャシステム

```
OprGacha（ガチャ基本設定）
  ├─ OprGachaI18n（多言語）
  ├─ OprGachaStep（ステップアップ）
  ├─ MstGachaLineup（提供アイテム）
  │    ├─ MstCharacter（キャラクター）
  │    └─ MstItem（アイテム）
  ├─ MstGachaPity（天井システム）
  │    └─ MstRewardGroup（天井報酬）
  └─ MstGachaAnimation（ガチャ演出）
       └─ MstRarity（レアリティ）
```

**調査方法**:
```bash
# OprGachaテーブルの構造確認
jq '.databases.mst.tables.opr_gachas' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json

# ガチャ関連テーブル一覧
jq '.databases.mst.tables | keys | map(select(test("gacha"; "i")))' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### 3. ミッションシステム

```
MstMission（恒常ミッション）
  ├─ MstMissionI18n（多言語）
  ├─ MstMissionCategory（カテゴリ）
  ├─ MstRewardGroup（報酬）
  └─ MstMissionGroup（連続ミッション）

OprMission（期間限定ミッション）
  ├─ OprMissionI18n（多言語）
  └─ MstRewardGroup（報酬）

MstMissionDaily（デイリー）
  └─ MstMission

MstMissionWeekly（ウィークリー）
  └─ MstMission

MstMissionEvent（イベント）
  ├─ OprEvent（イベント）
  └─ MstMission

MstMissionPanel（ビンゴパネル）
  ├─ MstMission（複数）
  └─ MstRewardGroup（完成報酬）
```

**調査方法**:
```bash
# ミッション関連テーブル一覧
jq '.databases.mst.tables | keys | map(select(test("mission"; "i")))' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json

# MstMissionのカラム一覧
jq '.databases.mst.tables.mst_missions.columns | keys' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### 4. ショップ・パックシステム

```
MstShop（ショップ）
  ├─ MstShopI18n（多言語）
  └─ MstShopItem（ショップアイテム）
       ├─ MstItem（アイテム）
       └─ MstCurrency（通貨）

MstPack（課金パック）
  ├─ MstPackContent（パック内容）
  │    └─ MstRewardGroup（報酬）
  └─ OprProduct（期間限定商品）
       └─ OprProductI18n（多言語）
```

**調査方法**:
```bash
# ショップ関連テーブル一覧
jq '.databases.mst.tables | keys | map(select(test("shop|pack|product"; "i")))' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### 5. クエスト・ステージシステム

```
MstQuest（クエスト）
  ├─ MstQuestI18n（多言語）
  └─ MstStage（ステージ）
       ├─ MstStageI18n（多言語）
       ├─ MstWave（ウェーブ）
       │    └─ MstEnemyGroup（敵グループ）
       │         └─ MstEnemy（敵キャラクター）
       ├─ MstDropGroup（ドロップ報酬）
       │    └─ MstItem（アイテム）
       ├─ MstRewardGroup（初回クリア報酬）
       └─ MstStageGimmick（ギミック）
```

**調査方法**:
```bash
# クエスト・ステージ関連テーブル一覧
jq '.databases.mst.tables | keys | map(select(test("quest|stage|wave|enemy"; "i")))' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json

# MstStageの外部キー確認
jq '.databases.mst.tables.mst_stages.columns | to_entries | map(select(.value.foreign_key != null))' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### 6. キャラクター・スキルシステム

```
MstCharacter（キャラクター）
  ├─ MstCharacterI18n（多言語）
  ├─ MstRarity（レアリティ）
  ├─ MstClass（クラス・属性）
  ├─ MstCharacterSkillRelation（スキル紐付け）
  │    ├─ MstSkill（アクティブスキル）
  │    ├─ MstAutoSkill（パッシブスキル）
  │    └─ MstLeaderSkill（リーダースキル）
  ├─ MstCharacterAwakening（覚醒）
  │    └─ MstItem（必要素材）
  └─ MstCharacterEvolution（進化）
       └─ MstItem（必要素材）
```

**調査方法**:
```bash
# キャラクター関連テーブル一覧
jq '.databases.mst.tables | keys | map(select(test("character|skill|class|rarity"; "i")))' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### 7. 報酬・アイテムシステム

```
MstRewardGroup（報酬グループ）
  └─ reward_items（JSON配列）
       ├─ MstCharacter（キャラクター）
       ├─ MstItem（アイテム）
       └─ MstCurrency（通貨）

MstDropGroup（ドロップグループ）
  └─ drop_items（JSON配列）
       └─ MstItem（アイテム）
```

**特徴**:
- MstRewardGroupとMstDropGroupは、複数の報酬を1つのグループとして管理
- reward_items, drop_itemsはJSON形式で格納
- 様々なテーブルから参照される（MstStage, OprEventPoint, MstMission など）

## 主要enum型

DBスキーマにはenum型のカラムが多数存在します。主要なenum値を以下に示します。

### テーブル一覧取得方法

```bash
# enum型を持つすべてのカラムを抽出
jq '.databases.mst.tables | to_entries[] |
  {table: .key, enums: (.value.columns | to_entries | map(select(.value.enum != null)) | map({column: .key, values: .value.enum}))} |
  select(.enums | length > 0)' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### 主要なenum型

#### MstCharacter

**class_type**:
```
- warrior（戦士）
- mage（魔法使い）
- archer（弓使い）
- healer（回復役）
- tank（タンク）
```

**element_type**:
```
- fire（火）
- water（水）
- earth（土）
- wind（風）
- light（光）
- dark（闇）
```

#### MstSkill

**skill_type**:
```
- damage（ダメージ）
- heal（回復）
- buff（バフ）
- debuff（デバフ）
- special（特殊）
```

**target_type**:
```
- single（単体）
- all_enemies（敵全体）
- all_allies（味方全体）
- random（ランダム）
- self（自分）
```

#### MstQuest

**quest_type**:
```
- main（メインクエスト）
- event（イベントクエスト）
- daily（デイリークエスト）
- special（スペシャルクエスト）
```

#### OprGacha

**gacha_type**:
```
- normal（通常ガチャ）
- step_up（ステップアップ）
- limited（期間限定）
- pickup（ピックアップ）
```

#### MstMission

**mission_type**:
```
- daily（デイリー）
- weekly（ウィークリー）
- event（イベント）
- achievement（実績）
- beginner（初心者）
```

**target_type**:
```
- stage_clear（ステージクリア）
- character_level（キャラクターレベル）
- item_collect（アイテム収集）
- login（ログイン）
- gacha（ガチャ実行）
```

#### MstItem

**item_type**:
```
- consumable（消費アイテム）
- material（素材）
- equipment（装備）
- treasure_box（宝箱）
- ticket（チケット）
```

**item_category**:
```
- enhancement（強化素材）
- evolution（進化素材）
- awakening（覚醒素材）
- general（汎用）
```

#### MstCurrency

**currency_type**:
```
- gem（ジェム・有償石）
- free_gem（無償石）
- coin（コイン）
- event_point（イベントポイント）
- gacha_ticket（ガチャチケット）
```

#### OprEvent

**event_type**:
```
- point（ポイントイベント）
- ranking（ランキングイベント）
- raid（レイドイベント）
- tower（タワーイベント）
- collection（収集イベント）
```

### enum値の確認方法

特定のテーブル・カラムのenum値を確認：

```bash
# MstCharacterのelement_type enum値確認
jq '.databases.mst.tables.mst_characters.columns.element_type.enum' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json

# MstSkillのskill_type enum値確認
jq '.databases.mst.tables.mst_skills.columns.skill_type.enum' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

## 調査のヒント

### よくある調査パターン

#### 1. 特定の機能に関連するテーブルを全て探す

```bash
# 「ガチャ」関連のテーブルを全検索
jq '.databases.mst.tables | keys | map(select(test("gacha"; "i")))' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

#### 2. 特定のテーブルの外部キー関係を調べる

```bash
# MstStageの外部キー一覧
jq '.databases.mst.tables.mst_stages.columns | to_entries |
  map(select(.value.foreign_key != null)) |
  map({column: .key, references: .value.foreign_key})' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

#### 3. 特定のテーブルを参照している全テーブルを逆引き

```bash
# MstRewardGroupを参照している全テーブルを検索
jq '.databases.mst.tables | to_entries |
  map({table: .key, columns: (.value.columns | to_entries |
    map(select(.value.foreign_key != null and (.value.foreign_key | test("mst_reward_groups")))) |
    map(.key))}) |
  select(.columns | length > 0)' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

#### 4. 既存データの例を確認

```bash
# MstCharacterの実データ例（先頭5行）
head -n 5 projects/glow-masterdata/MstCharacter.csv
```

#### 5. CSVテンプレートの列順を確認

```bash
# MstCharacterのCSVテンプレート（列名定義行）
sed -n '2p' projects/glow-masterdata/sheet_schema/MstCharacter.csv
```

## 関連ドキュメント

- [schema-reference.md](schema-reference.md) - DBスキーマの詳細な調査方法
- [investigation-patterns.md](investigation-patterns.md) - よくある調査シナリオと手順
- [SKILL.md](../SKILL.md) - スキルの基本的な使い方

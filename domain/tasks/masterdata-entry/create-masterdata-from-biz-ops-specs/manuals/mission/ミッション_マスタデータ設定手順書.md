# ミッション マスタデータ設定手順書

## 概要

イベントミッションのマスタデータ作成手順を記載します。
本手順書に従うことで、ゲーム内でエラーが発生しない正確なマスタデータを作成できます。

## 対象テーブル

イベントミッションのマスタデータは、以下の8テーブル構成で作成します。

**ミッション基本情報**:
- **MstMissionEvent** - イベントミッションの基本情報（達成条件、報酬等）
- **MstMissionEventI18n** - ミッション説明文（多言語対応）
- **MstMissionEventDependency** - ミッション間の解放順序の依存関係
- **MstMissionReward** - ミッション報酬

**ログインボーナス**:
- **MstMissionEventDailyBonus** - イベント期間中のログインボーナス
- **MstMissionEventDailyBonusSchedule** - ログインボーナスの開催スケジュール

**期間限定ミッション**:
- **MstMissionLimitedTerm** - 期間限定ミッション（降臨バトル等）
- **MstMissionLimitedTermI18n** - 期間限定ミッション説明文（多言語対応）

**重要**: 各I18nテーブルは独立したシートとして作成します。

## 作成フロー

### 1. 仕様書の確認

運営仕様書から以下の情報を抽出します。

**必要情報**:
- ミッションの達成条件（何を何回実行するか）
- ミッションの報酬内容
- イベントID（mst_event_id）
- ミッションの表示順序
- ミッション説明文（日本語）
- ミッション間の依存関係（順序解放の有無）
- ログインボーナスの期間と報酬内容
- 期間限定ミッションの有無

### 2. MstMissionEvent シートの作成

#### 2.1 シートスキーマ

このシートには、ENABLE行とデータ行が含まれます。

**ENABLEと列名行** - カラム名を示します。

```
ENABLE,id,release_key,mst_event_id,criterion_type,criterion_value,criterion_count,unlock_criterion_type,unlock_criterion_value,unlock_criterion_count,group_key,mst_mission_reward_group_id,sort_order,destination_scene
```

#### 2.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | ミッションの一意識別子。命名規則: `event_{作品ID}_{イベント連番5桁}_{ミッション連番}` |
| **release_key** | リリースキー。例: `202601010` |
| **mst_event_id** | イベントID。MstEventテーブルのidと対応。例: `event_jig_00001` |
| **criterion_type** | 達成条件タイプ。下記の「criterion_type設定一覧」を参照 |
| **criterion_value** | 条件の指定値。criterion_typeに応じて設定。不要な場合は空欄 |
| **criterion_count** | 条件の回数。例: `5`(5回クリア) |
| **unlock_criterion_type** | 解放条件タイプ。通常は `__NULL__` (依存関係はMstMissionEventDependencyで管理) |
| **unlock_criterion_value** | 解放条件の値。通常は空欄 |
| **unlock_criterion_count** | 解放条件の回数。通常は `0` |
| **group_key** | グループキー。**イベントミッションでは使用しない**。常に空欄 |
| **mst_mission_reward_group_id** | 報酬グループID。MstMissionRewardのgroup_idと対応。下記の「mst_mission_reward_group_id採番ルール」を参照 |
| **sort_order** | 表示順序。小さい数字ほど上位に表示。例: `1`, `2`, `3`... |
| **destination_scene** | タップ時の遷移先シーン。例: `Event`, `Gacha`, `UnitList` |

#### 2.3 criterion_type設定一覧

イベントミッション（mst_event_id指定）で使用可能なcriterion_typeは以下の通りです。**大文字小文字を正確に一致**させてください。

| criterion_type | 説明 | criterion_value | criterion_count | destination_scene候補 | 使用例 |
|---------------|------|----------------|----------------|---------------------|--------|
| **SpecificItemCollect** | 指定アイテムを収集 | アイテムID (mst_items.id)<br>例: `item_glo_00001` | 集めて欲しいアイテム個数 | Event | いいジャンメダル【赤】を200個集めよう |
| **DefeatEnemyCount** | インゲームで敵を撃破 | 空欄(`__NULL__`) | 撃破して欲しい敵数 | Event | 敵を100体撃破しよう |
| **DefeatBossEnemyCount** | インゲームで強敵(ボス)を撃破 | 空欄(`__NULL__`) | 撃破して欲しい敵数 | Event | ボスを10体撃破しよう |
| **SpecificGachaDrawCount** | 特定ガシャを引く | ガシャID (opr_gachas.id)<br>例: `gasho_001` | ガシャを引いて欲しい回数 | Gacha | 賀正ガシャ2026を10回引こう |
| **GachaDrawCount** | 通算でガチャを引く | 空欄(`__NULL__`) | 通算でガシャを引いて欲しい回数 | Gacha | ガシャを合計50回引こう |
| **OutpostEnhanceCount** | ゲートを強化 | 空欄(`__NULL__`) | ゲートを強化してほしい回数 | OutpostEnhance | ゲートを5回強化しよう |
| **SpecificOutpostEnhanceLevel** | 指定したゲート強化項目がレベル到達 | 強化項目ID (mst_outpost_enhancements.id) | 到達してほしいレベル | OutpostEnhance | ゲート強化「体力増強」をLv.5に到達 |
| **IaaCount** | 広告視聴 | 空欄(`__NULL__`) | 広告視聴してほしい回数 | Home | 広告を3回視聴しよう |
| **SpecificQuestClear** | 特定クエストを初クリア | クエストID (mst_quests.id)<br>例: `quest_event_jig1_charaget01` | 固定値: `1`<br>※2以上は設定不可 | Event | 収集クエスト「芸能界へ!」をクリアしよう |
| **SpecificStageClearCount** | 指定ステージをクリア | ステージID (mst_stages.id)<br>例: `event_jig1_1day_00001` | クリアしてほしい回数 | Event | デイリークエスト「開運!ジャンブル運試し」を3回クリア |
| **SpecificStageChallengeCount** | 指定ステージに挑戦 | ステージID (mst_stages.id) | 挑戦してほしい回数 | Event | デイリークエスト「開運!ジャンブル運試し」に5回挑戦 |
| **QuestClearCount** | 通算クエストクリア回数 | 空欄(`__NULL__`) | クリアして欲しいクエスト数 | QuestSelect | クエストを合計20回クリアしよう |
| **StageClearCount** | 通算ステージクリア回数 | 空欄(`__NULL__`) | ステージをクリアして欲しい回数 | Event | ステージを合計50回クリアしよう |
| **SpecificUnitStageClearCount** | 特定ユニット編成でステージクリア | ユニットID.ステージID<br>例: `chara_jig_00601.event_jig1_1day_00001` | クリアして欲しい回数 | Event | メイを編成に入れて「本能が告げている 危険だと」を5回クリア |
| **SpecificUnitStageChallengeCount** | 特定ユニット編成でステージ挑戦 | ユニットID.ステージID<br>例: `chara_jig_00601.event_jig1_challenge01_00001` | 挑戦して欲しい回数 | Event | メイを編成に入れて「死罪人と首切り役人」1話を1回挑戦 |
| **MissionClearCount** | ミッションをクリア | 空欄(`__NULL__`) | 同じミッションタイプの内でクリアして欲しい数 | Home | ミッションを5個クリアしよう |
| **SpecificMissionClearCount** | 指定したミッショングループをクリア | group_key | 同じgroup_key内でクリアして欲しい数 | Home | 指定グループのミッションを全てクリアしよう |
| **UserLevel** | プレイヤーレベル到達 | 空欄(`__NULL__`) | 到達してほしいレベル | Home | プレイヤーレベル10に到達しよう |
| **CoinCollect** | コインを集める | 空欄(`__NULL__`) | 集めてほしいコイン数 | StageSelect | コインを10000枚集めよう |
| **CoinUsedCount** | コインを使用 | 空欄(`__NULL__`) | 使用してほしいコイン数 | StageSelect | コインを5000枚使用しよう |
| **UnitLevelUpCount** | ユニットのレベルアップ | 空欄(`__NULL__`) | レベルアップを実行してほしい回数 | UnitList | ユニットのレベルアップを10回実行しよう |
| **UnitLevel** | 全ユニットの内でいずれかがレベル到達 | 空欄(`__NULL__`) | 到達して欲しいレベル | UnitList | いずれかのユニットをLv.30に到達させよう |
| **SpecificUnitLevel** | 指定ユニットがレベル到達 | ユニットID (mst_units.id)<br>例: `chara_jig_00601` | 到達して欲しいレベル | UnitList | メイをLv.50に到達させよう |
| **SpecificUnitRankUpCount** | 指定ユニットのランクアップ回数 | ユニットID (mst_units.id) | ランクアップをしてほしい回数 | UnitList | メイのランクアップを3回実行しよう |
| **SpecificUnitGradeUpCount** | 指定ユニットのグレードアップ回数 | ユニットID (mst_units.id) | グレードアップをしてほしい回数 | UnitList | メイのグレードアップを2回実行しよう |
| **LoginCount** | 通算ログイン日数 | 空欄(`__NULL__`) | 通算ログインして欲しい日数 | Home | 通算ログイン7日目 |
| **LoginContinueCount** | 連続ログイン日数 | 空欄(`__NULL__`) | 連続ログインして欲しい日数 | Home | 連続ログイン3日目 |
| **IdleIncentiveQuickCount** | クイック探索 | 空欄(`__NULL__`) | クイック探索をして欲しい回数 | IdleIncentive | クイック探索を5回実行しよう |
| **IdleIncentiveCount** | 探索 | 空欄(`__NULL__`) | 探索をして欲しい回数 | IdleIncentive | 探索を10回実行しよう |
| **SpecificSeriesUnitAcquiredCount** | 特定作品のユニットを獲得 | 作品ID (mst_series.id) | 獲得してほしいユニットの種類数 | Gacha | 【地獄楽】のユニットを5種類獲得しよう |
| **UnitAcquiredCount** | ユニットを入手 | 空欄(`__NULL__`) | 入手して欲しいユニット体数 | Gacha | ユニットを10体入手しよう |
| **SpecificUnitAcquiredCount** | 指定ユニットを獲得 | ユニットID (mst_units.id) | 獲得してほしい体数 | Gacha | メイを1体獲得しよう |
| **SpecificSeriesEnemyDiscoveryCount** | 指定作品の敵キャラを発見 | 作品ID (mst_series.id) | 発見してほしいエネミーの種類数 | Event | 【地獄楽】の敵キャラを3種類発見しよう |
| **EnemyDiscoveryCount** | 敵キャラを発見 | 空欄(`__NULL__`) | 発見してほしいエネミーの種類数 | Event | 敵キャラを10種類発見しよう |
| **SpecificEnemyDiscoveryCount** | 敵キャラXを発見 | エネミーID (mst_enemy_characters.id) | 発見してほしい体数 | Event | 特定の敵キャラを5体発見しよう |
| **SpecificSeriesEmblemAcquiredCount** | 指定作品のエンブレムを獲得 | 作品ID (mst_series.id) | 獲得してほしいエンブレムの種類数 | QuestSelect | 【地獄楽】のエンブレムを3種類獲得しよう |
| **EmblemAcquiredCount** | エンブレムを獲得 | 空欄(`__NULL__`) | 獲得してほしいエンブレムの種類数 | QuestSelect | エンブレムを10種類獲得しよう |
| **SpecificEmblemAcquiredCount** | 指定エンブレムを獲得 | エンブレムID (mst_emblems.id) | 獲得してほしい個数 | QuestSelect | 特定のエンブレムを1個獲得しよう |
| **SpecificSeriesArtworkCompletedCount** | 指定作品の原画を完成 | 作品ID (mst_series.id) | 完成させてほしい原画数 | QuestSelect | 【地獄楽】の原画を5つ完成させよう |
| **ArtworkCompletedCount** | 原画を完成 | 空欄(`__NULL__`) | 完成させてほしい原画数 | QuestSelect | 原画を10つ完成させよう |
| **SpecificArtworkCompletedCount** | 指定原画を完成 | 原画ID (mst_artworks.id) | 固定値: `1` (原画完成は生涯1回のみ) | QuestSelect | 特定の原画を1つ完成させよう |
| **PvpChallengeCount** | 決闘に挑戦 | 空欄(`__NULL__`) | 挑戦してほしい回数 | Pvp | 決闘に3回挑戦しよう |
| **PvpWinCount** | 決闘に勝利 | 空欄(`__NULL__`) | 勝利してほしい回数 | Pvp | 決闘に5回勝利しよう |

**イベントミッションで頻繁に使用されるcriterion_typeの参考**:
- SpecificUnitGradeUpCount
- SpecificUnitLevel
- SpecificQuestClear
- DefeatEnemyCount
- SpecificStageClearCount
- SpecificUnitStageClearCount

#### 2.4 destination_scene設定ルール

`destination_scene`は、ミッションをタップした時にユーザーが遷移する画面を指定します。

**重要な原則**:
- **criterion_typeとdestination_sceneは強い相関関係があります**
- 83%のcriterion_typeは1つのdestination_sceneに固定されています
- 上記のcriterion_type一覧表の「destination_scene候補」列を参照してください

**destination_scene一覧**:

| destination_scene | 説明 | 主な用途 |
|------------------|------|---------|
| **Event** | イベント画面 | イベント関連ミッション (最も使用頻度が高い) |
| **UnitList** | ユニット一覧画面 | ユニット育成系ミッション |
| **Gacha** | ガシャ画面 | ガシャ引き回数ミッション |
| **QuestSelect** | クエスト選択画面 | クエスト・原画・エンブレム系ミッション |
| **Home** | ホーム画面 | ログイン・プレイヤーレベル系ミッション |
| **StageSelect** | ステージ選択画面 | コイン収集・通常ステージ系ミッション |
| **OutpostEnhance** | ゲート強化画面 | ゲート強化系ミッション |
| **IdleIncentive** | 放置報酬画面 | 探索系ミッション |
| **Pvp** | 決闘画面 | 決闘系ミッション |
| **Web** | Web遷移 | SNSフォロー・レビュー・外部サイト系 |
| **LinkBnId** | アカウント連携画面 | アカウント連携系ミッション |
| **(空欄)** | 遷移なし | ボーナスポイント累積系ミッション |

**複数のdestination_scene候補がある場合**:

以下のcriterion_typeは、イベントミッションと通常ミッションで異なるdestination_sceneを使用します:

| criterion_type | イベントミッション | 通常ミッション |
|---------------|------------------|---------------|
| DefeatEnemyCount | Event | StageSelect |
| DefeatBossEnemyCount | Event | StageSelect |
| SpecificQuestClear | Event | QuestSelect |
| SpecificSeriesEnemyDiscoveryCount | Event | StageSelect |
| EnemyDiscoveryCount | Event | StageSelect |
| SpecificEnemyDiscoveryCount | Event | StageSelect |

**イベントミッションでの推奨設定**:
- イベント関連のミッションは基本的に`Event`を設定
- ガシャ引き回数ミッションは`Gacha`
- ユニット育成系ミッションは`UnitList`

#### 2.5 イベントミッション固有の設定

イベントミッションでは、以下のように設定します。

- **mst_event_id**: イベントIDを必ず指定（例: `event_jig_00001`）
- **criterion_type**: 上記のcriterion_type一覧から選択。イベントミッションで頻繁に使用されるものを優先的に検討
- **criterion_value**: criterion_typeに応じて設定（不要な場合は空欄）
- **criterion_count**: 達成に必要な回数またはレベル・数量
- **unlock_criterion_type**: 通常は `__NULL__` (依存関係はMstMissionEventDependencyで管理)
- **unlock_criterion_value**: 通常は空欄
- **unlock_criterion_count**: 通常は `0`
- **group_key**: 常に空欄 (イベントミッションでは使用しない)
- **destination_scene**: criterion_typeに応じて設定。上記の「destination_scene設定ルール」を参照

#### 2.6 mst_mission_reward_group_id採番ルール

イベントミッションの報酬グループIDは、以下の形式で採番します。

```
{作品ID}_{イベント連番5桁}_event_reward_{報酬段階}
```

**パラメータ**:
- `作品ID`: 3文字の作品識別子
  - jig = 地獄楽
  - osh = 推しの子
  - kai = 怪獣8号
  - glo = GLOW全体
- `イベント連番5桁`: イベントごとに採番（00001からゼロパディング）
- `報酬段階`: 報酬のステップ番号

**報酬段階のゼロパディングルール**:
- **通常**: 2桁ゼロパディング（01, 02, 03...28）
- **例外（osh: 推しの子のみ）**: ゼロパディングなし（1, 2, 3...53）

**採番例**:
```
jig_00001_event_reward_01   (地獄楽 イベント1 報酬段階1)
jig_00001_event_reward_28   (地獄楽 イベント1 報酬段階28)
osh_00001_event_reward_1    (推しの子 イベント1 報酬段階1)
osh_00001_event_reward_53   (推しの子 イベント1 報酬段階53)
glo_00001_event_reward_01   (GLOW全体 イベント1 報酬段階1)
```

**重要な注意点**:
- ミッションIDの連番と報酬段階の連番は必ずしも一致しない
- 同じ報酬段階番号を複数のミッションで共有しない（1ミッション = 1報酬グループ）

#### 2.7 ID採番ルール

イベントミッションのIDは、以下の形式で採番します。

```
event_{作品ID}_{イベント連番5桁}_{ミッション連番}
```

**パラメータ**:
- `作品ID`: 3文字の作品識別子
  - jig = 地獄楽
  - osh = 推しの子
  - kai = 怪獣8号
  - glo = GLOW全体
- `イベント連番5桁`: イベントごとに採番（00001からゼロパディング）
- `ミッション連番`: ミッションごとに1から順番に採番（ゼロパディングなし）

**採番例**:
```
event_jig_00001_1    (地獄楽 イベント1 ミッション1)
event_jig_00001_2    (地獄楽 イベント1 ミッション2)
event_jig_00001_43   (地獄楽 イベント1 ミッション43)
event_osh_00001_1    (推しの子 イベント1 ミッション1)
```

#### 2.8 作成例

```
ENABLE,id,release_key,mst_event_id,criterion_type,criterion_value,criterion_count,unlock_criterion_type,unlock_criterion_value,unlock_criterion_count,group_key,mst_mission_reward_group_id,sort_order,destination_scene
e,event_jig_00001_1,202601010,event_jig_00001,SpecificUnitGradeUpCount,chara_jig_00701,2,__NULL__,,0,,jig_00001_event_reward_01,1,UnitList
e,event_jig_00001_2,202601010,event_jig_00001,SpecificUnitGradeUpCount,chara_jig_00701,3,__NULL__,,0,,jig_00001_event_reward_02,2,UnitList
e,event_jig_00001_23,202601010,event_jig_00001,SpecificQuestClear,quest_event_jig1_charaget01,1,__NULL__,,0,,jig_00001_event_reward_23,23,Event
e,event_jig_00001_27,202601010,event_jig_00001,DefeatEnemyCount,,10,__NULL__,,0,,jig_00001_event_reward_27,27,Event
```

#### 2.9 説明文作成のポイント

- イベント名を明記する（例: 「ストーリークエスト「必ず生きて帰る」をクリアしよう」）
- 達成条件を明確に記載する（例: 「5回クリア」）
- ユーザーフレンドリーな表現にする
- 文末は「〜しよう」形式が推奨
- 特別な演出がある場合は【】で強調する（例: 【汗が輝いてるよ!】）

### 3. MstMissionEventI18n シートの作成

#### 3.1 シートスキーマ

```
ENABLE,release_key,id,mst_mission_event_id,language,description
```

#### 3.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **release_key** | リリースキー。MstMissionEventと同じ値 |
| **id** | I18nの一意識別子。命名規則: `{mst_mission_event_id}_{language}` |
| **mst_mission_event_id** | ミッションID。MstMissionEvent.idと対応 |
| **language** | 言語コード。`ja`: 日本語、`en`: 英語、`zh-CN`: 中国語（簡体字）、`zh-TW`: 中国語（繁体字） |
| **description** | ミッション説明文。ユーザーに表示される文言 |

#### 3.3 作成例

```
ENABLE,release_key,id,mst_mission_event_id,language,description
e,202601010,event_jig_00001_1_ja,event_jig_00001_1,ja,"メイ をグレード2まで強化しよう"
e,202601010,event_jig_00001_2_ja,event_jig_00001_2,ja,"メイ をグレード3まで強化しよう"
e,202601010,event_jig_00001_23_ja,event_jig_00001_23,ja,ストーリークエスト「必ず生きて帰る」をクリアしよう
e,202601010,event_jig_00001_27_ja,event_jig_00001_27,ja,敵を10体撃破しよう
```

### 4. MstMissionEventDependency シートの作成

#### 4.1 シートスキーマ

```
ENABLE,id,release_key,group_id,mst_mission_event_id,unlock_order,備考
```

#### 4.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | 依存関係の一意識別子。連番で設定。例: `151`, `152`, `153`... |
| **release_key** | リリースキー。MstMissionEventと同じ値 |
| **group_id** | 依存関係のグループID。下記の「group_id採番ルール」を参照 |
| **mst_mission_event_id** | ミッションID。MstMissionEvent.idと対応 |
| **unlock_order** | 解放順序。1から順番に設定 |
| **備考** | 任意のメモ |

#### 4.3 group_id採番ルール

MstMissionEventDependencyの`group_id`は、**依存関係グループの最初のミッションIDと同じ値**を使用します。

**採番パターン**:
```
event_{作品ID}_{イベント連番5桁}_{ミッション連番}
```

**重要な原則**:
- group_idは、そのグループで**最初に解放されるミッション（unlock_order=1）のミッションID**と同じ
- 同じgroup_id内のミッションは、unlock_orderの順番に解放される
- group_idが異なる場合は、それぞれ独立した依存関係グループとして管理される

**実例**:

| group_id | 含まれるミッション | unlock_order | 説明 |
|----------|------------------|--------------|------|
| event_jig_00001_1 | event_jig_00001_1 | 1 | グレードアップミッション（1番目） |
| event_jig_00001_1 | event_jig_00001_2 | 2 | グレードアップミッション（2番目） |
| event_jig_00001_1 | event_jig_00001_3 | 3 | グレードアップミッション（3番目） |
| event_jig_00001_1 | event_jig_00001_4 | 4 | グレードアップミッション（4番目） |
| event_jig_00001_5 | event_jig_00001_5 | 1 | レベルアップミッション（1番目） |
| event_jig_00001_5 | event_jig_00001_6 | 2 | レベルアップミッション（2番目） |
| event_jig_00001_27 | event_jig_00001_27 | 1 | 敵撃破ミッション（1番目） |
| event_jig_00001_27 | event_jig_00001_28 | 2 | 敵撃破ミッション（2番目） |

**設定方法**:

1. 依存関係を持つミッションのグループを特定する
2. そのグループの最初のミッションIDをgroup_idとして使用する
3. 同じgroup_id内のミッションに、unlock_order=1から順番に番号を振る

**例**:

メイのグレードアップミッション（グレード2→3→4→5）を作成する場合:

```
group_id: event_jig_00001_1 (最初のミッションIDと同じ)

event_jig_00001_1, unlock_order: 1 (グレード2)
event_jig_00001_2, unlock_order: 2 (グレード3)
event_jig_00001_3, unlock_order: 3 (グレード4)
event_jig_00001_4, unlock_order: 4 (グレード5)
```

#### 4.4 依存関係の設定方法

**重要**: 依存関係は、**順序解放が必要なミッショングループのみ**に設定します。全てのミッションに設定する必要はありません。

**依存関係が必要なミッションの例**:
- グレードアップミッション（グレード2→3→4→5）
- レベルアップミッション（Lv.20→30→40→50→60→70→80）
- 敵撃破数ミッション（10体→20体→30体→...→1000体）
- ガシャ引き回数ミッション（10回→20回→30回→40回...）

**依存関係が不要なミッションの例**:
- 単発の特定クエストクリアミッション
- 独立したガシャミッション（他のミッションと依存関係がない場合）
- アカウント連携、SNSフォロー等の1回限りのミッション

**設定手順**:

1. 依存関係グループを特定する
2. 各グループの最初のミッションIDをgroup_idとして使用
3. 同じgroup_id内のミッションにunlock_order=1から順番に番号を振る
4. 依存関係が不要なミッションはMstMissionEventDependencyに含めない

#### 4.5 作成例

```
ENABLE,id,release_key,group_id,mst_mission_event_id,unlock_order,備考
e,151,202601010,event_jig_00001_1,event_jig_00001_1,1,
e,152,202601010,event_jig_00001_1,event_jig_00001_2,2,
e,153,202601010,event_jig_00001_1,event_jig_00001_3,3,
e,154,202601010,event_jig_00001_1,event_jig_00001_4,4,
e,155,202601010,event_jig_00001_5,event_jig_00001_5,1,
e,156,202601010,event_jig_00001_5,event_jig_00001_6,2,
e,157,202601010,event_jig_00001_5,event_jig_00001_7,3,
```

上記の例:
- `event_jig_00001_1`グループ: グレードアップミッション（1→2→3→4の順に解放）
- `event_jig_00001_5`グループ: レベルアップミッション（5→6→7の順に解放）

### 5. MstMissionReward シートの作成

#### 5.1 シートスキーマ

```
ENABLE,id,release_key,group_id,resource_type,resource_id,resource_amount,sort_order,備考
```

#### 5.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | 報酬の一意識別子。命名規則: `mission_reward_{連番}` |
| **release_key** | リリースキー。MstMissionEventと同じ値 |
| **group_id** | 報酬グループID。MstMissionEvent.mst_mission_reward_group_idと対応 |
| **resource_type** | 報酬のリソースタイプ。下記の「resource_type設定一覧」を参照 |
| **resource_id** | リソースID。resource_typeに応じて設定。不要な場合は空欄 |
| **resource_amount** | 報酬の数量 |
| **sort_order** | 表示順序。複数報酬がある場合の並び順 |
| **備考** | 任意のメモ。ミッション説明文などを記載すると管理しやすい |

#### 5.3 resource_type設定一覧

ミッション報酬（MstMissionReward）で使用可能なresource_typeは以下の通りです。**大文字小文字を正確に一致**させてください。

| resource_type | 説明 | resource_id | 参照先テーブル | 主な用途 |
|--------------|------|------------|--------------|---------|
| **Coin** | コイン（ゲーム内通貨） | 不要（空欄） | - | ミッション報酬の定番 |
| **FreeDiamond** | 無償プリズム | 不要（空欄） | - | 重要なミッション報酬 |
| **Exp** | 経験値（プレイヤー経験値） | 不要（空欄） | - | クエストクリア報酬 |
| **Item** | アイテム（チケット、素材など） | **必要** | **MstItem** | ガシャチケット、育成素材 |
| **Emblem** | エンブレム（称号） | **必要** | **MstEmblem** | イベント達成報酬 |
| **Unit** | ユニット（キャラクター） | **必要** | **MstUnit** | ガシャ報酬、特別報酬 |

#### 5.4 作成例

```
ENABLE,id,release_key,group_id,resource_type,resource_id,resource_amount,sort_order,備考
e,mission_reward_480,202601010,jig_00001_event_reward_01,Item,memory_chara_jig_00701,200,1,jigいいジャン祭_ミッション
e,mission_reward_481,202601010,jig_00001_event_reward_02,Item,memory_chara_jig_00701,300,1,jigいいジャン祭_ミッション
e,mission_reward_484,202601010,jig_00001_event_reward_05,Item,piece_jig_00701,10,1,jigいいジャン祭_ミッション
e,mission_reward_490,202601010,jig_00001_event_reward_11,FreeDiamond,,50,1,jigいいジャン祭_ミッション
e,mission_reward_502,202601010,jig_00001_event_reward_23,Coin,,12500,1,jigいいジャン祭_ミッション
```

### 6. MstMissionEventDailyBonus シートの作成

#### 6.1 シートスキーマ

```
ENABLE,id,release_key,mst_mission_event_daily_bonus_schedule_id,login_day,mst_mission_reward_group_id
```

#### 6.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | ログインボーナスの一意識別子。命名規則: `{mst_event_id}_daily_bonus_{login_day:02d}` |
| **release_key** | リリースキー。MstMissionEventと同じ値 |
| **mst_mission_event_daily_bonus_schedule_id** | スケジュールID。MstMissionEventDailyBonusSchedule.idと対応 |
| **login_day** | ログイン日数。1から順番に採番 |
| **mst_mission_reward_group_id** | 報酬グループID。MstMissionReward.group_idと対応 |

#### 6.3 ID採番ルール

```
{mst_event_id}_daily_bonus_{login_day:02d}
```

**パラメータ**:
- `mst_event_id`: イベントID（例: `event_jig_00001`）
- `login_day`: ログイン日数（01から2桁ゼロパディング）

**採番例**:
```
event_jig_00001_daily_bonus_01   (1日目)
event_jig_00001_daily_bonus_02   (2日目)
event_jig_00001_daily_bonus_17   (17日目)
```

#### 6.4 報酬グループID採番ルール

ログインボーナスの報酬グループIDは、以下の形式で採番します。

```
{作品ID}_{イベント連番5桁}_daily_bonus_{login_day:02d}
```

**採番例**:
```
jig_00001_daily_bonus_01   (1日目)
jig_00001_daily_bonus_17   (17日目)
```

#### 6.5 作成例

```
ENABLE,id,release_key,mst_mission_event_daily_bonus_schedule_id,login_day,mst_mission_reward_group_id
e,event_jig_00001_daily_bonus_01,202601010,event_jig_00001_daily_bonus_schedule,1,event_jig_00001_daily_bonus_01
e,event_jig_00001_daily_bonus_02,202601010,event_jig_00001_daily_bonus_schedule,2,event_jig_00001_daily_bonus_02
e,event_jig_00001_daily_bonus_17,202601010,event_jig_00001_daily_bonus_schedule,17,event_jig_00001_daily_bonus_17
```

**注**: ログインボーナスの報酬はMstMissionRewardで設定します。

### 7. MstMissionEventDailyBonusSchedule シートの作成

#### 7.1 シートスキーマ

```
ENABLE,id,release_key,mst_event_id,start_at,end_at
```

#### 7.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | スケジュールの一意識別子。命名規則: `{mst_event_id}_daily_bonus_schedule` |
| **release_key** | リリースキー。MstMissionEventと同じ値 |
| **mst_event_id** | イベントID。MstEvent.idと対応 |
| **start_at** | 開始日時。形式: `YYYY-MM-DD HH:MM:SS` |
| **end_at** | 終了日時。形式: `YYYY-MM-DD HH:MM:SS` |

#### 7.3 ID採番ルール

```
{mst_event_id}_daily_bonus_schedule
```

**採番例**:
```
event_jig_00001_daily_bonus_schedule
event_osh_00001_daily_bonus_schedule
```

#### 7.4 作成例

```
ENABLE,id,release_key,mst_event_id,start_at,end_at
e,event_jig_00001_daily_bonus_schedule,202601010,event_jig_00001,2026-01-16 15:00:00,2026-02-02 03:59:00
```

### 8. MstMissionLimitedTerm シートの作成

#### 8.1 シートスキーマ

```
ENABLE,id,release_key,mst_event_id,criterion_type,criterion_value,criterion_count,mst_mission_reward_group_id,sort_order,start_at,end_at,destination_scene
```

#### 8.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | 期間限定ミッションの一意識別子。命名規則: `{作品ID}_{イベント連番5桁}_limited_term_{連番}` |
| **release_key** | リリースキー。MstMissionEventと同じ値 |
| **mst_event_id** | イベントID。MstEvent.idと対応 |
| **criterion_type** | 達成条件タイプ。MstMissionEventと同じcriterion_typeを使用 |
| **criterion_value** | 条件の指定値。criterion_typeに応じて設定 |
| **criterion_count** | 条件の回数 |
| **mst_mission_reward_group_id** | 報酬グループID。MstMissionReward.group_idと対応 |
| **sort_order** | 表示順序 |
| **start_at** | 開始日時。形式: `YYYY-MM-DD HH:MM:SS` |
| **end_at** | 終了日時。形式: `YYYY-MM-DD HH:MM:SS` |
| **destination_scene** | タップ時の遷移先シーン。MstMissionEventと同じdestination_sceneを使用 |

#### 8.3 ID採番ルール

```
{作品ID}_{イベント連番5桁}_limited_term_{連番}
```

**パラメータ**:
- `作品ID`: 3文字の作品識別子
- `イベント連番5桁`: イベントごとに採番（00001からゼロパディング）
- `連番`: 期間限定ミッションごとに1から順番に採番（ゼロパディングなし）

**採番例**:
```
jig_00001_limited_term_1
jig_00001_limited_term_2
jig_00001_limited_term_4
```

#### 8.4 報酬グループID採番ルール

期間限定ミッションの報酬グループIDは、以下の形式で採番します。

```
{作品ID}_{イベント連番5桁}_limited_term_{連番}
```

**採番例**:
```
jig_00001_limited_term_1
jig_00001_limited_term_2
```

#### 8.5 作成例

```
ENABLE,id,release_key,mst_event_id,criterion_type,criterion_value,criterion_count,mst_mission_reward_group_id,sort_order,start_at,end_at,destination_scene
e,jig_00001_limited_term_1,202601010,event_jig_00001,AdventBattleChallengeCount,quest_raid_jig1_00001,5,jig_00001_limited_term_1,1,2026-01-23 15:00:00,2026-01-29 14:59:00,Event
e,jig_00001_limited_term_2,202601010,event_jig_00001,AdventBattleChallengeCount,quest_raid_jig1_00001,10,jig_00001_limited_term_2,2,2026-01-23 15:00:00,2026-01-29 14:59:00,Event
```

**注**: 降臨バトル期間限定ミッションなどで使用されます。

### 9. MstMissionLimitedTermI18n シートの作成

#### 9.1 シートスキーマ

```
ENABLE,release_key,id,mst_mission_limited_term_id,language,description
```

#### 9.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **release_key** | リリースキー。MstMissionLimitedTermと同じ値 |
| **id** | I18nの一意識別子。命名規則: `{mst_mission_limited_term_id}_{language}` |
| **mst_mission_limited_term_id** | 期間限定ミッションID。MstMissionLimitedTerm.idと対応 |
| **language** | 言語コード。`ja`: 日本語、`en`: 英語、`zh-CN`: 中国語（簡体字）、`zh-TW`: 中国語（繁体字） |
| **description** | ミッション説明文 |

#### 9.3 作成例

```
ENABLE,release_key,id,mst_mission_limited_term_id,language,description
e,202601010,jig_00001_limited_term_1_ja,jig_00001_limited_term_1,ja,"降臨バトル「まるで 悪夢を見ているようだ」に5回挑戦しよう！"
e,202601010,jig_00001_limited_term_2_ja,jig_00001_limited_term_2,ja,"降臨バトル「まるで 悪夢を見ているようだ」に10回挑戦しよう！"
```

## データ整合性のチェック

マスタデータ作成後、以下の項目を確認してください。

### 必須チェック項目

- [ ] **ヘッダーの列順が正しいか**
  - スキーマファイルと完全一致している

- [ ] **IDの一意性**
  - すべてのidが一意である
  - 他のリリースのidと重複していない

- [ ] **ID採番ルール**
  - MstMissionEvent.id: `event_{作品ID}_{イベント連番5桁}_{ミッション連番}`
    - 例: `event_jig_00001_1`, `event_jig_00001_43`
  - MstMissionEvent.mst_event_id: MstEventテーブルに存在するイベントID
    - 例: `event_jig_00001`
  - MstMissionEvent.mst_mission_reward_group_id: `{作品ID}_{イベント連番5桁}_event_reward_{報酬段階}`
    - 通常: 2桁ゼロパディング（例: `jig_00001_event_reward_01`）
    - 例外（osh）: ゼロパディングなし（例: `osh_00001_event_reward_1`）
  - MstMissionEvent.group_key: 常に空欄
  - MstMissionEventDependency.id: 連番（例: `151`, `152`, `153`...）
  - MstMissionEventDependency.group_id: 依存関係グループの最初のミッションIDと同じ
    - 例: `event_jig_00001_1`（そのグループの最初のミッション）
  - MstMissionReward.id: `mission_reward_{連番}`
    - 例: `mission_reward_480`, `mission_reward_481`
  - MstMissionReward.group_id: MstMissionEvent.mst_mission_reward_group_idと同じ値
  - MstMissionEventDailyBonus.id: `{mst_event_id}_daily_bonus_{login_day:02d}`
    - 例: `event_jig_00001_daily_bonus_01`
  - MstMissionEventDailyBonusSchedule.id: `{mst_event_id}_daily_bonus_schedule`
    - 例: `event_jig_00001_daily_bonus_schedule`
  - MstMissionLimitedTerm.id: `{作品ID}_{イベント連番5桁}_limited_term_{連番}`
    - 例: `jig_00001_limited_term_1`

- [ ] **リレーションの整合性**
  - `MstMissionEvent.mst_mission_reward_group_id` = `MstMissionReward.group_id`
  - `MstMissionEventDependency.mst_mission_event_id` がMstMissionEvent.idに存在する
  - `MstMissionEventI18n.mst_mission_event_id` がMstMissionEvent.idに存在する
  - `MstMissionEventDailyBonus.mst_mission_event_daily_bonus_schedule_id` がMstMissionEventDailyBonusSchedule.idに存在する
  - `MstMissionLimitedTermI18n.mst_mission_limited_term_id` がMstMissionLimitedTerm.idに存在する
  - すべてのミッションにdescription（説明文）が設定されている

- [ ] **criterion_typeの正確性**
  - 「criterion_type設定一覧」に記載されたいずれかを使用している
  - **大文字小文字が正確に一致している**（例: `SpecificUnitGradeUpCount` ○, `specificunitgradeupcount` ×）
  - criterion_valueの設定有無が正しい（必要なタイプには設定、不要なタイプは空欄）
  - criterion_countが適切な値である

- [ ] **destination_sceneの正確性**
  - 「destination_scene設定ルール」に記載されたいずれかを使用している
  - criterion_typeに対応するdestination_sceneが設定されている
  - criterion_type一覧表の「destination_scene候補」列と一致している

- [ ] **resource_typeの正確性**
  - 「resource_type設定一覧」に記載された値を使用している
  - 大文字小文字が正確に一致している
  - resource_idの設定有無が正しい（必要なタイプには設定、不要なタイプは空欄）

- [ ] **unlock設定の統一性**
  - unlock_criterion_typeは通常 `__NULL__`
  - unlock_criterion_valueは通常空欄
  - unlock_criterion_countは通常 `0`

- [ ] **数値の妥当性**
  - criterion_count, resource_amount, sort_orderが正の整数である

- [ ] **開催期間の妥当性**
  - MstMissionEventDailyBonusSchedule.start_at、end_atがイベント期間内である
  - MstMissionLimitedTerm.start_at、end_atがイベント期間内である
  - 日時形式が `YYYY-MM-DD HH:MM:SS` である

### 推奨チェック項目

- [ ] **命名規則の統一**
  - idのプレフィックスが統一されている

- [ ] **説明文の品質**
  - 誤字脱字がない
  - ユーザーが理解しやすい表現である

- [ ] **依存関係の妥当性**
  - unlock_orderが1から連番で設定されている
  - 同じgroup_id内で欠番がない

- [ ] **destination_sceneとcriterion_typeの組み合わせの一貫性**
  - 同じcriterion_typeのミッションは同じdestination_sceneを使用している
  - イベント内で一貫性のあるdestination_scene設定になっている
  - ユーザーが混乱しない遷移先設定になっている

- [ ] **ログインボーナスの連続性**
  - login_dayが1から連番で設定されている
  - 欠番がない

## 出力フォーマット

最終的な出力は以下の8シート構成で行います。

### MstMissionEvent シート

| ENABLE | id | release_key | mst_event_id | criterion_type | criterion_value | criterion_count | unlock_criterion_type | unlock_criterion_value | unlock_criterion_count | group_key | mst_mission_reward_group_id | sort_order | destination_scene |
|--------|----|-----------|--------------|-----------------|-----------------|-----------------|-----------------------|------------------------|------------------------|-----------|----------------------------|-----------|-------------------|
| e | event_jig_00001_1 | 202601010 | event_jig_00001 | SpecificUnitGradeUpCount | chara_jig_00701 | 2 | `__NULL__` | | 0 | | jig_00001_event_reward_01 | 1 | UnitList |
| e | event_jig_00001_2 | 202601010 | event_jig_00001 | SpecificUnitGradeUpCount | chara_jig_00701 | 3 | `__NULL__` | | 0 | | jig_00001_event_reward_02 | 2 | UnitList |

**注意**: group_keyカラムは常に空欄です。

### MstMissionEventI18n シート

| ENABLE | release_key | id | mst_mission_event_id | language | description |
|--------|-------------|----|--------------------|----------|------------|
| e | 202601010 | event_jig_00001_1_ja | event_jig_00001_1 | ja | "メイ をグレード2まで強化しよう" |
| e | 202601010 | event_jig_00001_2_ja | event_jig_00001_2 | ja | "メイ をグレード3まで強化しよう" |

### MstMissionEventDependency シート

| ENABLE | id | release_key | group_id | mst_mission_event_id | unlock_order | 備考 |
|--------|----|-----------|---------|--------------------|--------------|-----|
| e | 151 | 202601010 | event_jig_00001_1 | event_jig_00001_1 | 1 | |
| e | 152 | 202601010 | event_jig_00001_1 | event_jig_00001_2 | 2 | |

### MstMissionReward シート

| ENABLE | id | release_key | group_id | resource_type | resource_id | resource_amount | sort_order | 備考 |
|--------|----|-----------|---------|--------------|-----------|-----------------|-----------|----|
| e | mission_reward_480 | 202601010 | jig_00001_event_reward_01 | Item | memory_chara_jig_00701 | 200 | 1 | jigいいジャン祭_ミッション |
| e | mission_reward_490 | 202601010 | jig_00001_event_reward_11 | FreeDiamond | | 50 | 1 | jigいいジャン祭_ミッション |

### MstMissionEventDailyBonus シート

| ENABLE | id | release_key | mst_mission_event_daily_bonus_schedule_id | login_day | mst_mission_reward_group_id |
|--------|----|-------------|-----------------------------------------|----------|----------------------------|
| e | event_jig_00001_daily_bonus_01 | 202601010 | event_jig_00001_daily_bonus_schedule | 1 | event_jig_00001_daily_bonus_01 |
| e | event_jig_00001_daily_bonus_02 | 202601010 | event_jig_00001_daily_bonus_schedule | 2 | event_jig_00001_daily_bonus_02 |

### MstMissionEventDailyBonusSchedule シート

| ENABLE | id | release_key | mst_event_id | start_at | end_at |
|--------|----|-----------|--------------|---------|----|
| e | event_jig_00001_daily_bonus_schedule | 202601010 | event_jig_00001 | 2026-01-16 15:00:00 | 2026-02-02 03:59:00 |

### MstMissionLimitedTerm シート

| ENABLE | id | release_key | mst_event_id | criterion_type | criterion_value | criterion_count | mst_mission_reward_group_id | sort_order | start_at | end_at | destination_scene |
|--------|----|-----------|--------------|-----------------|-----------------|-----------------|-----------------------------|-----------|----------|--------|-------------------|
| e | jig_00001_limited_term_1 | 202601010 | event_jig_00001 | AdventBattleChallengeCount | quest_raid_jig1_00001 | 5 | jig_00001_limited_term_1 | 1 | 2026-01-23 15:00:00 | 2026-01-29 14:59:00 | Event |

### MstMissionLimitedTermI18n シート

| ENABLE | release_key | id | mst_mission_limited_term_id | language | description |
|--------|-------------|----|--------------------------|-----------|-----------|
| e | 202601010 | jig_00001_limited_term_1_ja | jig_00001_limited_term_1 | ja | "降臨バトル「まるで 悪夢を見ているようだ」に5回挑戦しよう！" |

## 重要なポイント

- **8テーブル構成**: イベントミッションは8つのテーブルで構成されます
- **I18nは独立したシート**: 各I18nテーブルは独立したシートとして作成
- **依存関係は必要に応じて**: 順序解放が必要なミッショングループのみMstMissionEventDependencyを設定
- **ログインボーナスは任意**: イベントにログインボーナスがある場合のみ設定
- **期間限定ミッションは任意**: 降臨バトル等の期間限定ミッションがある場合のみ設定
- **group_keyは常に空欄**: イベントミッションではgroup_keyは使用しない
- **unlock_criterion_typeは常に__NULL__**: 依存関係はMstMissionEventDependencyで管理
- **外部キー整合性の徹底**: すべてのリレーションが正しく設定されていることを確認

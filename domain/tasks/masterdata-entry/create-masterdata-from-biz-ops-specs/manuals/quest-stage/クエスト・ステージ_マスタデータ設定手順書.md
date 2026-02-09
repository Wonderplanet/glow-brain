# クエスト・ステージ マスタデータ設定手順書

## 概要

イベントクエスト・ステージのマスタデータ作成手順を記載します。
本手順書に従うことで、ゲーム内でエラーが発生しない正確なマスタデータを作成できます。

## 対象テーブル

クエスト・ステージのマスタデータは、以下の10テーブル構成で作成します。

**クエスト基本情報**:
- **MstQuest** - クエストの基本情報(タイプ、イベントID、開催期間等)
- **MstQuestI18n** - クエスト名・説明文(多言語対応)
- **MstQuestBonusUnit** - クエスト特効キャラ設定

**ステージ情報**:
- **MstStage** - ステージの基本情報(推奨レベル、スタミナ、報酬等)
- **MstStageI18n** - ステージ名(多言語対応)
- **MstStageEventReward** - ステージ報酬(初回クリア・ランダム報酬)
- **MstStageEventSetting** - ステージイベント設定(リセット、開催期間、背景等)
- **MstStageClearTimeReward** - タイムアタック報酬
- **MstStageEndCondition** - ステージ終了条件
- **MstQuestEventBonusSchedule** - 特効スケジュール(降臨バトル用)

**重要**: 各I18nテーブルは独立したシートとして作成します。

## 作成フロー

### 1. 仕様書の確認

クエスト設計書から以下の情報を抽出します。

**必要情報**:
- クエストの基本情報(クエスト名、タイプ、難易度)
- イベントID(紐付け先のイベント)
- 開催期間(開始日時・終了日時)
- ステージ構成(ステージ数、各ステージの設定)
- 推奨レベル、スタミナコスト、経験値、コイン
- 報酬設定(初回クリア報酬、ランダム報酬、タイムアタック報酬)
- リセット設定(Daily、None等)
- 特効キャラ設定(ボーナス率)
- 背景・BGM設定

### 2. MstQuest シートの作成

#### 2.1 シートスキーマ

このシートには、ENABLE行とデータ行が含まれます。

**ENABLEと列名行** - カラム名を示します。

```
ENABLE,id,quest_type,mst_event_id,sort_order,asset_key,start_date,end_date,quest_group,difficulty,release_key
```

#### 2.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | クエストの一意識別子。命名規則: `quest_event_{series_id}{連番}_{クエストタイプ略称}` |
| **quest_type** | クエストタイプ。下記の「quest_type設定一覧」を参照 |
| **mst_event_id** | イベントID。MstEvent.idと対応 |
| **sort_order** | 表示順序。クエスト一覧での表示順(1から順番に採番) |
| **asset_key** | アセットキー。通常はクエストIDの一部を使用 |
| **start_date** | 開始日時。形式: `YYYY-MM-DD HH:MM:SS` |
| **end_date** | 終了日時。形式: `YYYY-MM-DD HH:MM:SS` |
| **quest_group** | クエストグループ。同一グループのクエストをまとめる識別子 |
| **difficulty** | 難易度。下記の「difficulty設定一覧」を参照 |
| **release_key** | リリースキー。例: `202601010` |

#### 2.3 quest_type設定一覧

クエストで使用可能なquest_typeは以下の通りです。**大文字小文字を正確に一致**させてください。

| quest_type | 説明 | 使用例 |
|----------|------|--------|
| **event** | イベントクエスト | ストーリークエスト、デイリークエスト、チャレンジクエスト等 |
| **raid** | レイドクエスト | 降臨バトル等(別テーブルMstAdventBattleと連携) |
| **story** | ストーリークエスト | メインストーリー |
| **enhance** | 強化クエスト | 素材集めクエスト |

**頻繁に使用されるquest_type**:
- event(イベント系クエストで最も使用頻度が高い)

#### 2.4 difficulty設定一覧

| difficulty | 説明 | 使用例 |
|--------|------|--------|
| **Normal** | 通常難易度 | 大部分のクエスト |
| **Hard** | 高難度 | チャレンジクエスト等 |
| **VeryHard** | 最高難度 | 高難度クエスト等 |

#### 2.5 ID採番ルール

クエストのIDは、以下の形式で採番します。

```
quest_event_{series_id}{連番}_{クエストタイプ略称}
```

**パラメータ**:
- `series_id`: 3文字のシリーズ識別子
  - jig = 地獄楽
  - osh = 推しの子
  - kai = 怪獣8号
  - glo = GLOW全体
- `連番`: シリーズ内で1から採番
- `クエストタイプ略称`: クエストの種類を示す短縮名
  - charaget01、charaget02 = キャラ入手クエスト
  - 1day = デイリークエスト
  - challenge01 = チャレンジクエスト
  - savage = 高難度クエスト

**採番例**:
```
quest_event_jig1_charaget01   (地獄楽 イベント1 キャラ入手クエスト1)
quest_event_jig1_1day         (地獄楽 イベント1 デイリークエスト)
quest_event_jig1_challenge01  (地獄楽 イベント1 チャレンジクエスト1)
quest_event_jig1_savage       (地獄楽 イベント1 高難度クエスト)
```

**quest_groupの採番**:
```
event_{series_id}{連番}_{識別子}
```

クエストをグループ化する識別子。通常はクエストIDに準じた形式。

**採番例**:
```
event_jig1_charaget_mei       (地獄楽 イベント1 メイ入手クエストグループ)
event_jig1_1day               (地獄楽 イベント1 デイリークエストグループ)
```

#### 2.6 作成例

```
ENABLE,id,quest_type,mst_event_id,sort_order,asset_key,start_date,end_date,quest_group,difficulty,release_key
e,quest_event_jig1_charaget01,event,event_jig_00001,1,jig1_charaget01,"2026-01-16 15:00:00","2026-02-16 10:59:59",event_jig1_charaget_mei,Normal,202601010
e,quest_event_jig1_1day,event,event_jig_00001,5,jig1_1day,"2026-01-16 15:00:00","2026-02-2 03:59:59",event_jig1_1day,Normal,202601010
e,quest_event_jig1_challenge01,event,event_jig_00001,2,jig1_challenge01,"2026-01-16 15:00:00","2026-02-16 10:59:59",event_jig1_challenge01,Normal,202601010
```

#### 2.7 開催期間設定のポイント

- **start_date / end_date**: 必ず日時まで指定(`YYYY-MM-DD HH:MM:SS`形式)
- **時刻設定**: 通常15:00:00開始、10:59:59または14:59:59終了
- **期間の整合性**: イベント全体の期間内に収まるように設定
- **段階的開放**: ストーリークエストの後編等は開始日時を遅らせることがある

### 3. MstQuestI18n シートの作成

#### 3.1 シートスキーマ

```
ENABLE,release_key,id,mst_quest_id,language,name,category_name,flavor_text
```

#### 3.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **release_key** | リリースキー。MstQuestと同じ値 |
| **id** | I18nの一意識別子。命名規則: `{mst_quest_id}_{language}` |
| **mst_quest_id** | クエストID。MstQuest.idと対応 |
| **language** | 言語コード。`ja`: 日本語、`en`: 英語、`zh-CN`: 中国語(簡体字)、`zh-TW`: 中国語(繁体字) |
| **name** | クエスト名 |
| **category_name** | カテゴリ名。例: ストーリー、デイリー、チャレンジ、高難易度 |
| **flavor_text** | フレーバーテキスト(クエスト説明。通常は空欄) |

#### 3.3 作成例

```
ENABLE,release_key,id,mst_quest_id,language,name,category_name,flavor_text
e,202601010,quest_event_jig1_charaget01_ja,quest_event_jig1_charaget01,ja,必ず生きて帰る,ストーリー,
e,202601010,quest_event_jig1_1day_ja,quest_event_jig1_1day,ja,"本能が告げている 危険だと",デイリー,
e,202601010,quest_event_jig1_challenge01_ja,quest_event_jig1_challenge01,ja,死罪人と首切り役人,チャレンジ,
```

### 4. MstQuestBonusUnit シートの作成

#### 4.1 シートスキーマ

```
ENABLE,id,mst_quest_id,mst_unit_id,coin_bonus_rate,start_at,end_at,release_key
```

#### 4.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | 特効設定の一意識別子。連番で採番 |
| **mst_quest_id** | クエストID。MstQuest.idと対応 |
| **mst_unit_id** | 特効キャラID。MstUnit.idと対応 |
| **coin_bonus_rate** | コインボーナス率。例: `0.15`(15%UP)、`0.1`(10%UP) |
| **start_at** | 特効開始日時。形式: `YYYY-MM-DD HH:MM:SS` |
| **end_at** | 特効終了日時。形式: `YYYY-MM-DD HH:MM:SS` |
| **release_key** | リリースキー。MstQuestと同じ値 |

#### 4.3 作成例

```
ENABLE,id,mst_quest_id,mst_unit_id,coin_bonus_rate,start_at,end_at,release_key
e,58,quest_enhance_00001,chara_jig_00001,0.15,"2026-01-16 15:00:00","2026-02-16 10:59:59",202601010
e,62,quest_enhance_00001,chara_jig_00401,0.2,"2026-01-16 15:00:00","2026-02-16 10:59:59",202601010
e,63,quest_enhance_00001,chara_jig_00501,0.1,"2026-01-16 15:00:00","2026-02-16 10:59:59",202601010
```

上記の例:
- chara_jig_00401: 20%ボーナス(最高)
- chara_jig_00001: 15%ボーナス
- chara_jig_00501: 10%ボーナス

### 5. MstStage シートの作成

#### 5.1 シートスキーマ

```
ENABLE,id,mst_quest_id,mst_in_game_id,stage_number,recommended_level,cost_stamina,exp,coin,prev_mst_stage_id,mst_stage_tips_group_id,auto_lap_type,max_auto_lap_count,sort_order,asset_key,mst_stage_limit_status_id,release_key,mst_artwork_fragment_drop_group_id,start_at,end_at
```

#### 5.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | ステージの一意識別子。命名規則: `{クエストタイプ略称}_{連番5桁}` |
| **mst_quest_id** | クエストID。MstQuest.idと対応 |
| **mst_in_game_id** | インゲームID。MstInGame.idと対応(別途作成) |
| **stage_number** | ステージ番号。1から順番に採番 |
| **recommended_level** | 推奨レベル。プレイヤーに推奨するレベル |
| **cost_stamina** | スタミナコスト。ステージプレイに必要なスタミナ |
| **exp** | 経験値。クリア時に獲得できる経験値 |
| **coin** | コイン。クリア時に獲得できるコイン |
| **prev_mst_stage_id** | 前提ステージID。解放条件となる前のステージID(最初のステージは空欄) |
| **mst_stage_tips_group_id** | Tipsグループ ID。通常は`1` |
| **auto_lap_type** | 周回タイプ。下記の「auto_lap_type設定一覧」を参照 |
| **max_auto_lap_count** | 最大周回回数。周回可能な最大回数 |
| **sort_order** | 表示順序。ステージ一覧での表示順(stage_numberと同じ値) |
| **asset_key** | アセットキー。ステージサムネイル等のアセット識別子 |
| **mst_stage_limit_status_id** | ステージ制限ステータスID。通常は空欄 |
| **release_key** | リリースキー。例: `202601010` |
| **mst_artwork_fragment_drop_group_id** | 原画欠片ドロップグループID。原画演出があるストーリークエストの場合に設定 |
| **start_at** | ステージ開始日時。形式: `YYYY-MM-DD HH:MM:SS` |
| **end_at** | ステージ終了日時。形式: `YYYY-MM-DD HH:MM:SS` |

#### 5.3 auto_lap_type設定一覧

| auto_lap_type | 説明 | 使用例 |
|-------------|------|--------|
| **__NULL__** | 周回不可 | デイリークエスト、チャレンジクエスト等 |
| **AfterClear** | クリア後周回可能 | ストーリークエスト等 |

#### 5.4 ID採番ルール

```
{クエストタイプ略称}_{連番5桁}
```

**パラメータ**:
- `クエストタイプ略称`: クエストIDと同じ略称
- `連番5桁`: ステージごとに00001から順番にゼロパディング

**採番例**:
```
event_jig1_charaget01_00001   (キャラ入手クエスト1のステージ1)
event_jig1_charaget01_00002   (キャラ入手クエスト1のステージ2)
event_jig1_1day_00001         (デイリークエストのステージ1)
```

#### 5.5 作成例

```
ENABLE,id,mst_quest_id,mst_in_game_id,stage_number,recommended_level,cost_stamina,exp,coin,prev_mst_stage_id,mst_stage_tips_group_id,auto_lap_type,max_auto_lap_count,sort_order,asset_key,mst_stage_limit_status_id,release_key,mst_artwork_fragment_drop_group_id,start_at,end_at
e,event_jig1_charaget01_00001,quest_event_jig1_charaget01,event_jig1_charaget01_00001,1,10,5,50,100,,1,AfterClear,5,1,event_jig1_00001,,202601010,event_jig_a_0001,"2026-01-16 15:00:00","2026-02-16 10:59:59"
e,event_jig1_charaget01_00002,quest_event_jig1_charaget01,event_jig1_charaget01_00002,2,15,5,50,100,event_jig1_charaget01_00001,1,AfterClear,5,2,general_diamond,,202601010,event_jig_a_0002,"2026-01-16 15:00:00","2026-02-16 10:59:59"
e,event_jig1_1day_00001,quest_event_jig1_1day,event_jig1_1day_00001,1,1,1,100,1500,,1,__NULL__,1,1,general_diamond,,202601010,__NULL__,"2026-01-16 15:00:00","2026-02-2 03:59:59"
```

#### 5.6 ステージ設定のポイント

- **recommended_level**: ステージが進むごとに上昇させる(例: 10→15→20→25→30)
- **cost_stamina**: 難易度に応じて設定(通常: 5-10、高難度: 15-50)
- **exp / coin**: ステージが進むごとに増加させる
- **prev_mst_stage_id**: ステージの解放順序を制御(前のステージをクリアしないと次が開放されない)
- **auto_lap_type**: ストーリー系はAfterClear、デイリーやチャレンジは__NULL__

### 6. MstStageI18n シートの作成

#### 6.1 シートスキーマ

```
ENABLE,release_key,id,mst_stage_id,language,name,category_name
```

#### 6.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **release_key** | リリースキー。MstStageと同じ値 |
| **id** | I18nの一意識別子。命名規則: `{mst_stage_id}_{language}` |
| **mst_stage_id** | ステージID。MstStage.idと対応 |
| **language** | 言語コード。`ja`: 日本語、`en`: 英語、`zh-CN`: 中国語(簡体字)、`zh-TW`: 中国語(繁体字) |
| **name** | ステージ名(通常はクエスト名と同じ) |
| **category_name** | カテゴリ名(通常は空欄) |

#### 6.3 作成例

```
ENABLE,release_key,id,mst_stage_id,language,name,category_name
e,202601010,event_jig1_charaget01_00001_ja,event_jig1_charaget01_00001,ja,必ず生きて帰る,
e,202601010,event_jig1_charaget01_00002_ja,event_jig1_charaget01_00002,ja,必ず生きて帰る,
e,202601010,event_jig1_1day_00001_ja,event_jig1_1day_00001,ja,"本能が告げている 危険だと",
```

### 7. MstStageEventReward シートの作成

#### 7.1 シートスキーマ

```
ENABLE,id,mst_stage_id,reward_category,resource_type,resource_id,resource_amount,percentage,sort_order,release_key
```

#### 7.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | 報酬設定の一意識別子。連番で採番 |
| **mst_stage_id** | ステージID。MstStage.idと対応 |
| **reward_category** | 報酬カテゴリ。下記の「reward_category設定一覧」を参照 |
| **resource_type** | リソースタイプ。下記の「resource_type設定一覧」を参照 |
| **resource_id** | リソースID。resource_typeに応じて設定(ユニットID、アイテムID等) |
| **resource_amount** | リソース数量。獲得できる数量 |
| **percentage** | 獲得確率。100: 確定、50: 50%等 |
| **sort_order** | 表示順序。報酬一覧での表示順 |
| **release_key** | リリースキー。MstStageと同じ値 |

#### 7.3 reward_category設定一覧

| reward_category | 説明 | 使用例 |
|---------------|------|--------|
| **FirstClear** | 初回クリア報酬 | 初回のみ獲得できる報酬 |
| **Random** | ランダム報酬 | クリアごとに確率で獲得できる報酬 |

#### 7.4 resource_type設定一覧

| resource_type | 説明 | resource_id設定 |
|-------------|------|----------------|
| **FreeDiamond** | 無償ダイヤ | prism_glo_00001(固定) |
| **Coin** | コイン | 空欄 |
| **Unit** | ユニット | MstUnit.id |
| **Item** | アイテム | MstItem.id(メモリー、欠片等) |

#### 7.5 作成例

```
ENABLE,id,mst_stage_id,reward_category,resource_type,resource_id,resource_amount,percentage,sort_order,release_key
e,569,event_jig1_charaget01_00001,FirstClear,Unit,chara_jig_00701,1,100,1,202601010
e,570,event_jig1_charaget01_00001,FirstClear,FreeDiamond,prism_glo_00001,40,100,2,202601010
e,571,event_jig1_charaget01_00001,FirstClear,Coin,,500,100,3,202601010
e,572,event_jig1_charaget01_00001,Random,Item,piece_jig_00701,1,10,4,202601010
e,539,event_jig1_1day_00001,FirstClear,FreeDiamond,prism_glo_00001,20,100,1,202601010
e,540,event_jig1_1day_00001,Random,Coin,,2000,50,2,202601010
e,541,event_jig1_1day_00001,Random,Coin,,4000,40,3,202601010
e,542,event_jig1_1day_00001,Random,FreeDiamond,prism_glo_00001,30,30,4,202601010
```

上記の例:
- ステージ1: 初回クリアでキャラ(chara_jig_00701)、ダイヤ40個、コイン500枚。ランダムで欠片1個(10%確率)
- デイリー: 初回クリアでダイヤ20個。ランダムでコイン2000枚(50%確率)、4000枚(40%確率)、ダイヤ30個(30%確率)

#### 7.6 報酬設定のポイント

- **FirstClear報酬**: percentage は常に100(確定)
- **Random報酬**: percentage の合計が100を超えても良い(複数報酬同時獲得可能)
- **ユニット獲得**: キャラ入手クエストの最初のステージでユニットを付与
- **メモリー・欠片**: ステージが進むごとに獲得量を増やす

### 8. MstStageEventSetting シートの作成

#### 8.1 シートスキーマ

```
ENABLE,id,mst_stage_id,reset_type,clearable_count,ad_challenge_count,mst_stage_rule_group_id,start_at,end_at,release_key,background_asset_key
```

#### 8.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | イベント設定の一意識別子。連番で採番 |
| **mst_stage_id** | ステージID。MstStage.idと対応 |
| **reset_type** | リセットタイプ。下記の「reset_type設定一覧」を参照 |
| **clearable_count** | クリア可能回数。reset_typeがDaily等の場合に設定 |
| **ad_challenge_count** | 広告チャレンジ回数。通常は`0` |
| **mst_stage_rule_group_id** | ステージルールグループID。通常は`__NULL__` |
| **start_at** | 開始日時。形式: `YYYY-MM-DD HH:MM:SS` |
| **end_at** | 終了日時。形式: `YYYY-MM-DD HH:MM:SS` |
| **release_key** | リリースキー。MstStageと同じ値 |
| **background_asset_key** | 背景アセットキー。ステージ背景の識別子 |

#### 8.3 reset_type設定一覧

| reset_type | 説明 | 使用例 |
|----------|------|--------|
| **__NULL__** | リセットなし | ストーリークエスト、チャレンジクエスト等 |
| **Daily** | 毎日リセット | デイリークエスト |

#### 8.4 作成例

```
ENABLE,id,mst_stage_id,reset_type,clearable_count,ad_challenge_count,mst_stage_rule_group_id,start_at,end_at,release_key,background_asset_key
e,162,event_jig1_1day_00001,Daily,1,0,__NULL__,"2026-01-16 15:00:00","2026-02-2 03:59:59",202601010,jig_00001
e,169,event_jig1_charaget01_00001,__NULL__,,0,__NULL__,"2026-01-16 15:00:00","2026-02-16 10:59:59",202601010,jig_00003
e,170,event_jig1_charaget01_00002,__NULL__,,0,__NULL__,"2026-01-16 15:00:00","2026-02-16 10:59:59",202601010,jig_00003
```

上記の例:
- デイリークエスト: 毎日1回クリア可能、背景jig_00001
- ストーリークエスト: リセットなし、背景jig_00003

#### 8.5 イベント設定のポイント

- **reset_type=Daily**: clearable_countを必ず設定(通常は1)
- **reset_type=__NULL__**: clearable_countは空欄
- **background_asset_key**: 各クエストで統一した背景を使用することが多い

### 9. MstStageClearTimeReward シートの作成

#### 9.1 シートスキーマ

```
ENABLE,id,mst_stage_id,upper_clear_time_ms,resource_type,resource_id,resource_amount,release_key
```

#### 9.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | タイムアタック報酬の一意識別子。命名規則: `{mst_stage_id}_{連番}` |
| **mst_stage_id** | ステージID。MstStage.idと対応 |
| **upper_clear_time_ms** | クリアタイム上限(ミリ秒)。この時間以内にクリアで報酬獲得 |
| **resource_type** | リソースタイプ。通常は`FreeDiamond` |
| **resource_id** | リソースID。FreeDiamondの場合は空欄 |
| **resource_amount** | リソース数量。獲得できるダイヤ数 |
| **release_key** | リリースキー。MstStageと同じ値 |

#### 9.3 作成例

```
ENABLE,id,mst_stage_id,upper_clear_time_ms,resource_type,resource_id,resource_amount,release_key
e,event_jig1_challenge01_00001_1,event_jig1_challenge01_00001,140000,FreeDiamond,,20,202601010
e,event_jig1_challenge01_00001_2,event_jig1_challenge01_00001,200000,FreeDiamond,,20,202601010
e,event_jig1_challenge01_00001_3,event_jig1_challenge01_00001,300000,FreeDiamond,,20,202601010
e,event_jig1_savage_00003_1,event_jig1_savage_00003,240000,FreeDiamond,,20,202601010
e,event_jig1_savage_00003_2,event_jig1_savage_00003,300000,FreeDiamond,,20,202601010
e,event_jig1_savage_00003_3,event_jig1_savage_00003,400000,FreeDiamond,,20,202601010
```

上記の例:
- チャレンジステージ1: 140秒以内、200秒以内、300秒以内でそれぞれダイヤ20個
- 高難度ステージ3: 240秒以内、300秒以内、400秒以内でそれぞれダイヤ20個

#### 9.4 タイムアタック報酬設定のポイント

- **upper_clear_time_ms**: ミリ秒単位で設定(140000ms = 140秒)
- **段階的設定**: 3段階程度の時間設定が一般的
- **難易度調整**: ステージが進むほど時間を長めに設定

### 10. MstStageEndCondition シートの作成

#### 10.1 シートスキーマ

```
ENABLE,id,mst_stage_id,stage_end_type,condition_type,condition_value1,condition_value2,release_key
```

#### 10.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | 終了条件の一意識別子。通常はmst_stage_idと同じ |
| **mst_stage_id** | ステージID。MstStage.idと対応 |
| **stage_end_type** | ステージ終了タイプ。下記の「stage_end_type設定一覧」を参照 |
| **condition_type** | 条件タイプ。下記の「condition_type設定一覧」を参照 |
| **condition_value1** | 条件値1。条件タイプに応じた値(秒数等) |
| **condition_value2** | 条件値2。通常は空欄 |
| **release_key** | リリースキー。MstStageと同じ値 |

**重要**: 通常のイベントクエストではMstStageEndConditionは設定不要です。降臨バトルやPVP等の特殊なクエストでのみ設定します。

#### 10.3 stage_end_type設定一覧

| stage_end_type | 説明 | 使用例 |
|--------------|------|--------|
| **Defeat** | 敗北条件 | 降臨バトル等 |
| **Finish** | 終了条件 | PVP等 |

#### 10.4 condition_type設定一覧

| condition_type | 説明 | 使用例 |
|-------------|------|--------|
| **TimeOver** | 時間切れ | 降臨バトル、PVP等 |

#### 10.5 作成例

```
ENABLE,id,mst_stage_id,stage_end_type,condition_type,condition_value1,condition_value2,release_key
e,quest_raid_jig1_00001,quest_raid_jig1_00001,Defeat,TimeOver,120,,202601010
e,pvp_jig_01,pvp_jig_01,Finish,TimeOver,180,,202601010
```

上記の例:
- 降臨バトル: 120秒でタイムオーバー(敗北)
- PVP: 180秒でタイムオーバー(終了)

### 11. MstQuestEventBonusSchedule シートの作成

#### 11.1 シートスキーマ

```
ENABLE,id,mst_quest_id,event_bonus_group_id,start_at,end_at,release_key
```

#### 11.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | スケジュールの一意識別子。連番で採番 |
| **mst_quest_id** | クエストID。MstQuest.idと対応 |
| **event_bonus_group_id** | 特効グループID。MstEventBonusUnitと対応 |
| **start_at** | 特効開始日時。形式: `YYYY-MM-DD HH:MM:SS` |
| **end_at** | 特効終了日時。形式: `YYYY-MM-DD HH:MM:SS` |
| **release_key** | リリースキー。MstQuestと同じ値 |

**重要**: このテーブルは降臨バトル等の特殊なクエストでのみ使用します。通常のイベントクエストではMstQuestBonusUnitを使用します。

#### 11.3 作成例

```
ENABLE,id,mst_quest_id,event_bonus_group_id,start_at,end_at,release_key
e,9,quest_raid_jig1_00001,raid_jig1_00001,"2026-01-23 15:00:00","2026-01-29 14:59:59",202601010
```

上記の例:
- 降臨バトル: 特効グループraid_jig1_00001を2026-01-23～2026-01-29の期間で有効化

## データ整合性のチェック

マスタデータ作成後、以下の項目を確認してください。

### 必須チェック項目

- [ ] **ヘッダーの列順が正しいか**
  - スキーマファイルと完全一致している

- [ ] **IDの一意性**
  - すべてのidが一意である
  - 他のリリースのidと重複していない

- [ ] **ID採番ルール**
  - MstQuest.id: `quest_event_{series_id}{連番}_{クエストタイプ略称}`
  - MstStage.id: `{クエストタイプ略称}_{連番5桁}`
  - I18n系テーブルのid: `{親テーブルid}_{language}`

- [ ] **リレーションの整合性**
  - `MstQuest.mst_event_id` が `MstEvent.id` に存在する
  - `MstQuestI18n.mst_quest_id` が `MstQuest.id` に存在する
  - `MstQuestBonusUnit.mst_quest_id` が `MstQuest.id` に存在する
  - `MstQuestBonusUnit.mst_unit_id` が `MstUnit.id` に存在する
  - `MstStage.mst_quest_id` が `MstQuest.id` に存在する
  - `MstStage.prev_mst_stage_id` が `MstStage.id` に存在する(または空欄)
  - `MstStageI18n.mst_stage_id` が `MstStage.id` に存在する
  - `MstStageEventReward.mst_stage_id` が `MstStage.id` に存在する
  - `MstStageEventSetting.mst_stage_id` が `MstStage.id` に存在する
  - `MstStageClearTimeReward.mst_stage_id` が `MstStage.id` に存在する

- [ ] **enum値の正確性**
  - quest_type: event、raid、story、enhance
  - difficulty: Normal、Hard、VeryHard
  - auto_lap_type: __NULL__、AfterClear
  - reward_category: FirstClear、Random
  - resource_type: FreeDiamond、Coin、Unit、Item
  - reset_type: __NULL__、Daily
  - 大文字小文字が正確に一致している

- [ ] **日時形式の妥当性**
  - すべての日時が`YYYY-MM-DD HH:MM:SS`形式
  - start_date < end_date
  - イベント期間内に収まっている

- [ ] **数値の妥当性**
  - stage_number、recommended_level、cost_stamina、exp、coinが正の整数である
  - percentageが0～100の範囲内である
  - upper_clear_time_msがミリ秒単位で設定されている

- [ ] **ステージ解放順序**
  - prev_mst_stage_idが正しく設定されている
  - ステージが順番に解放されるようになっている

### 推奨チェック項目

- [ ] **命名規則の統一**
  - idのプレフィックスがシリーズIDと一致している
  - クエストIDとステージIDの命名規則が統一されている

- [ ] **I18n設定の完全性**
  - 日本語(ja)が必須で設定されている
  - 他言語(en、zh-CN、zh-TW)も設定されている

- [ ] **報酬設定の妥当性**
  - FirstClear報酬のpercentageが100である
  - ステージが進むごとに報酬量が増加している
  - resource_idが適切に設定されている(FreeDiamondの場合は空欄等)

- [ ] **特効設定の妥当性**
  - coin_bonus_rateが適切な範囲内(0.05～0.2程度)
  - 特効期間がクエスト開催期間内である

## 出力フォーマット

最終的な出力は以下の10シート構成で行います。

### MstQuest シート

| ENABLE | id | quest_type | mst_event_id | sort_order | asset_key | start_date | end_date | quest_group | difficulty | release_key |
|--------|----|-----------|--------------|-----------|---------|-----------|---------|-----------|-----------|---------||
| e | quest_event_jig1_charaget01 | event | event_jig_00001 | 1 | jig1_charaget01 | 2026-01-16 15:00:00 | 2026-02-16 10:59:59 | event_jig1_charaget_mei | Normal | 202601010 |

### MstQuestI18n シート

| ENABLE | release_key | id | mst_quest_id | language | name | category_name | flavor_text |
|--------|-------------|----|--------------|---------|----|--------------|------------|
| e | 202601010 | quest_event_jig1_charaget01_ja | quest_event_jig1_charaget01 | ja | 必ず生きて帰る | ストーリー | |

### MstQuestBonusUnit シート

| ENABLE | id | mst_quest_id | mst_unit_id | coin_bonus_rate | start_at | end_at | release_key |
|--------|----|--------------|-----------|--------------|---------|---------|---------||
| e | 58 | quest_enhance_00001 | chara_jig_00001 | 0.15 | 2026-01-16 15:00:00 | 2026-02-16 10:59:59 | 202601010 |

### MstStage シート

| ENABLE | id | mst_quest_id | mst_in_game_id | stage_number | recommended_level | cost_stamina | exp | coin | prev_mst_stage_id | mst_stage_tips_group_id | auto_lap_type | max_auto_lap_count | sort_order | asset_key | mst_stage_limit_status_id | release_key | mst_artwork_fragment_drop_group_id | start_at | end_at |
|--------|----|--------------|--------------|-----------|--------------|-----------|----|----|-----------------|-----------------------|--------------|------------------|-----------|---------|-----------------------|-----------|---------------------------------|---------|---------||
| e | event_jig1_charaget01_00001 | quest_event_jig1_charaget01 | event_jig1_charaget01_00001 | 1 | 10 | 5 | 50 | 100 | | 1 | AfterClear | 5 | 1 | event_jig1_00001 | | 202601010 | event_jig_a_0001 | 2026-01-16 15:00:00 | 2026-02-16 10:59:59 |

### MstStageI18n シート

| ENABLE | release_key | id | mst_stage_id | language | name | category_name |
|--------|-------------|----|--------------|---------|----|--------------|
| e | 202601010 | event_jig1_charaget01_00001_ja | event_jig1_charaget01_00001 | ja | 必ず生きて帰る | |

### MstStageEventReward シート

| ENABLE | id | mst_stage_id | reward_category | resource_type | resource_id | resource_amount | percentage | sort_order | release_key |
|--------|----|--------------|--------------|--------------|-----------|--------------|-----------|-----------|---------||
| e | 569 | event_jig1_charaget01_00001 | FirstClear | Unit | chara_jig_00701 | 1 | 100 | 1 | 202601010 |

### MstStageEventSetting シート

| ENABLE | id | mst_stage_id | reset_type | clearable_count | ad_challenge_count | mst_stage_rule_group_id | start_at | end_at | release_key | background_asset_key |
|--------|----|--------------|-----------|--------------|-----------------|-----------------------|---------|---------|-----------|--------------------|
| e | 169 | event_jig1_charaget01_00001 | __NULL__ | | 0 | __NULL__ | 2026-01-16 15:00:00 | 2026-02-16 10:59:59 | 202601010 | jig_00003 |

### MstStageClearTimeReward シート

| ENABLE | id | mst_stage_id | upper_clear_time_ms | resource_type | resource_id | resource_amount | release_key |
|--------|----|--------------|--------------------|--------------|-----------|---------------|---------||
| e | event_jig1_challenge01_00001_1 | event_jig1_challenge01_00001 | 140000 | FreeDiamond | | 20 | 202601010 |

### MstStageEndCondition シート(特殊クエストのみ)

| ENABLE | id | mst_stage_id | stage_end_type | condition_type | condition_value1 | condition_value2 | release_key |
|--------|----|--------------|--------------|--------------|-----------------|-----------------|---------||
| e | quest_raid_jig1_00001 | quest_raid_jig1_00001 | Defeat | TimeOver | 120 | | 202601010 |

### MstQuestEventBonusSchedule シート(特殊クエストのみ)

| ENABLE | id | mst_quest_id | event_bonus_group_id | start_at | end_at | release_key |
|--------|----|--------------|--------------------|----------|---------|---------||
| e | 9 | quest_raid_jig1_00001 | raid_jig1_00001 | 2026-01-23 15:00:00 | 2026-01-29 14:59:59 | 202601010 |

## 重要なポイント

- **10テーブル構成**: クエスト・ステージは複数のテーブルに関連します
- **I18nは独立したシート**: 各I18nテーブルは独立したシートとして作成
- **報酬の2種類**: FirstClear(初回クリア)とRandom(ランダムドロップ)
- **リセット設定**: デイリークエストはreset_type=Daily、通常クエストは__NULL__
- **周回設定**: ストーリーはAfterClear、デイリーやチャレンジは__NULL__
- **ステージ解放順序**: prev_mst_stage_idで前提ステージを設定
- **タイムアタック報酬**: チャレンジや高難度クエストで設定
- **特殊クエスト**: 降臨バトルやPVPはMstStageEndCondition、MstQuestEventBonusScheduleを使用
- **外部キー整合性の徹底**: すべてのリレーションが正しく設定されていることを確認

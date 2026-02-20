# 降臨バトル マスタデータ設定手順書

## 概要

降臨バトル（レイドイベント）のマスタデータ作成手順を記載します。
本手順書に従うことで、ゲーム内でエラーが発生しない正確なマスタデータを作成できます。

## 対象テーブル

降臨バトルのマスタデータは、以下の7テーブル構成で作成します。

**降臨バトル基本情報**:
- **MstAdventBattle** - 降臨バトルの基本情報（ID、開催期間、スコア設定等）
- **MstAdventBattleI18n** - 降臨バトル名・ボス説明（多言語対応）

**ランクシステム**:
- **MstAdventBattleRank** - ランク評価設定（Bronze/Silver/Gold/Master 各4レベル）

**報酬設定**:
- **MstAdventBattleClearReward** - クリア時ランダム報酬
- **MstAdventBattleRewardGroup** - 報酬カテゴリ定義（MaxScore/RaidTotalScore/Rank/Ranking等）
- **MstAdventBattleReward** - 報酬詳細

**特効設定**:
- **MstEventBonusUnit** - 特効キャラ設定（ボーナス率）

**重要**: 各I18nテーブルは独立したシートとして作成します。

## 作成フロー

### 1. 仕様書の確認

降臨バトル設計書から以下の情報を抽出します。

**必要情報**:
- 降臨バトルの基本情報（バトルID、イベントID、開催期間）
- バトルタイプ（ScoreChallenge、BossDefeat等）
- スコア設定（初期BP、スコア加算係数）
- 挑戦回数設定（通常挑戦、広告挑戦）
- ランク評価基準（Bronze～Master、各レベルのスコア閾値）
- 報酬設定（最高スコア報酬、累計スコア報酬、ランク報酬、ランキング報酬、初回クリア報酬）
- 特効キャラとボーナス率
- ボス敵キャラID
- インゲーム設定（MstInGame.id）

### 2. MstAdventBattle シートの作成

#### 2.1 シートスキーマ

このシートには、ENABLE行とデータ行が含まれます。

**ENABLEと列名行** - カラム名を示します。

```
ENABLE,id,mst_event_id,mst_in_game_id,asset_key,advent_battle_type,initial_battle_point,score_addition_type,score_additional_coef,score_addition_target_mst_enemy_stage_parameter_id,mst_stage_rule_group_id,event_bonus_group_id,challengeable_count,ad_challengeable_count,display_mst_unit_id1,display_mst_unit_id2,display_mst_unit_id3,exp,coin,start_at,end_at,release_key
```

#### 2.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | 降臨バトルの一意識別子。命名規則: `quest_raid_{series_id}_{連番5桁}` |
| **mst_event_id** | イベントID。MstEvent.idと対応（例: `event_jig_00001`） |
| **mst_in_game_id** | インゲームID。MstInGame.idと対応（例: `raid_jig1_00001`） |
| **asset_key** | アセットキー。背景等のアセット識別子（例: `jig_00001`） |
| **advent_battle_type** | バトルタイプ。下記の「advent_battle_type設定一覧」を参照 |
| **initial_battle_point** | 初期BP（バトルポイント）。通常は `500` |
| **score_addition_type** | スコア加算タイプ。下記の「score_addition_type設定一覧」を参照 |
| **score_additional_coef** | スコア加算係数。倒した敵のBP×この係数でスコア加算（例: `0.07`） |
| **score_addition_target_mst_enemy_stage_parameter_id** | スコア加算対象の敵パラメータID。通常は `test` または空欄 |
| **mst_stage_rule_group_id** | ステージルールグループID。通常は空欄 |
| **event_bonus_group_id** | 特効グループID。MstEventBonusUnit.event_bonus_group_idと対応（例: `raid_jig1_00001`） |
| **challengeable_count** | 通常挑戦可能回数。通常は `3` |
| **ad_challengeable_count** | 広告視聴での追加挑戦可能回数。通常は `2` |
| **display_mst_unit_id1** | 表示キャラ1のユニットID。ボスキャラのIDを設定（例: `enemy_jig_00601`） |
| **display_mst_unit_id2** | 表示キャラ2のユニットID。通常は空欄 |
| **display_mst_unit_id3** | 表示キャラ3のユニットID。通常は空欄 |
| **exp** | クリア時の経験値。通常は `100` |
| **coin** | クリア時のコイン。通常は `300` |
| **start_at** | 開催開始日時。フォーマット: `YYYY-MM-DD HH:MM:SS` |
| **end_at** | 開催終了日時。フォーマット: `YYYY-MM-DD HH:MM:SS` |
| **release_key** | リリースキー。例: `202601010` |

#### 2.3 advent_battle_type設定一覧

降臨バトルで使用可能なadvent_battle_typeは以下の通りです。**大文字小文字を正確に一致**させてください。

| advent_battle_type | 説明 | 特徴 |
|-------------------|------|------|
| **ScoreChallenge** | スコアチャレンジ | スコアを競うランキング型。最も一般的 |
| **BossDefeat** | ボス討伐 | ボス撃破を目標とする協力型 |
| **TimeAttack** | タイムアタック | クリアタイムを競う |

**頻繁に使用されるadvent_battle_type**:
- ScoreChallenge（最も使用頻度が高い）

#### 2.4 score_addition_type設定一覧

| score_addition_type | 説明 | スコア計算方法 |
|--------------------|------|---------------|
| **AllEnemiesAndOutPost** | 全敵とアウトポスト | 倒した全ての敵のBP×係数 |
| **BossOnly** | ボスのみ | ボスのBP×係数 |
| **None** | なし | スコア加算なし |

**頻繁に使用されるscore_addition_type**:
- AllEnemiesAndOutPost（最も使用頻度が高い）

#### 2.5 ID採番ルール

降臨バトルのIDは、以下の形式で採番します。

```
quest_raid_{series_id}{連番1桁}_{連番5桁}
```

**パラメータ**:
- `series_id`: 3文字のシリーズ識別子
  - jig = 地獄楽
  - osh = 推しの子
  - kai = 怪獣8号
- `連番1桁`: イベント内の降臨バトル連番（1～9）
- `連番5桁`: 降臨バトルの通し番号（00001からゼロパディング）

**採番例**:
```
quest_raid_jig1_00001   (地獄楽イベント1の降臨バトル1)
quest_raid_jig2_00001   (地獄楽イベント2の降臨バトル1)
quest_raid_osh1_00001   (推しの子イベント1の降臨バトル1)
```

**event_bonus_group_idの採番**:
```
raid_{series_id}{連番1桁}_{連番5桁}
```

idの`quest_`を削除した形式です。

**採番例**:
```
raid_jig1_00001   (quest_raid_jig1_00001の特効グループ)
```

#### 2.6 作成例

```
ENABLE,id,mst_event_id,mst_in_game_id,asset_key,advent_battle_type,initial_battle_point,score_addition_type,score_additional_coef,score_addition_target_mst_enemy_stage_parameter_id,mst_stage_rule_group_id,event_bonus_group_id,challengeable_count,ad_challengeable_count,display_mst_unit_id1,display_mst_unit_id2,display_mst_unit_id3,exp,coin,start_at,end_at,release_key
e,quest_raid_jig1_00001,event_jig_00001,raid_jig1_00001,jig_00001,ScoreChallenge,500,AllEnemiesAndOutPost,0.07,test,,raid_jig1_00001,3,2,enemy_jig_00601,,,100,300,"2026-01-23 15:00:00","2026-01-29 14:59:59",202601010
```

#### 2.7 開催期間設定のポイント

- **start_at / end_at**: 必ず`"`（ダブルクォート）で囲む
- **期間**: 通常は7日間（1週間）が一般的
- **開催時刻**: 15:00開始、14:59:59終了が標準的
- **イベント期間との関係**: イベント全体期間内に収める

### 3. MstAdventBattleI18n シートの作成

#### 3.1 シートスキーマ

```
ENABLE,release_key,id,mst_advent_battle_id,language,name,boss_description
```

#### 3.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **release_key** | リリースキー。MstAdventBattleと同じ値 |
| **id** | I18nの一意識別子。命名規則: `{mst_advent_battle_id}_{locale}` |
| **mst_advent_battle_id** | 降臨バトルID。MstAdventBattle.idと対応 |
| **language** | 言語コード。`ja`: 日本語、`en`: 英語、`zh-CN`: 中国語（簡体字）、`zh-TW`: 中国語（繁体字） |
| **name** | 降臨バトル名（クエスト名） |
| **boss_description** | ボス説明文。プレイヤーへの訴求文言（例: 「ボスを倒して高スコア獲得!!」） |

#### 3.3 作成例

```
ENABLE,release_key,id,mst_advent_battle_id,language,name,boss_description
e,202601010,quest_raid_jig1_00001_ja,quest_raid_jig1_00001,ja,"まるで 悪夢を見ているようだ",ボスを倒して高スコア獲得!!
```

### 4. MstAdventBattleRank シートの作成

#### 4.1 シートスキーマ

```
ENABLE,id,mst_advent_battle_id,rank_type,rank_level,required_lower_score,asset_key,release_key
```

#### 4.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | ランクの一意識別子。命名規則: `{mst_advent_battle_id}_rank_{連番2桁}` |
| **mst_advent_battle_id** | 降臨バトルID。MstAdventBattle.idと対応 |
| **rank_type** | ランクタイプ。下記の「rank_type設定一覧」を参照 |
| **rank_level** | ランクレベル。各ランクタイプ内で`1`～`4` |
| **required_lower_score** | 必要スコア下限。このスコア以上でランク到達 |
| **asset_key** | アセットキー。通常は空欄 |
| **release_key** | リリースキー。MstAdventBattleと同じ値 |

#### 4.3 rank_type設定一覧

降臨バトルで使用可能なrank_typeは以下の通りです。**大文字小文字を正確に一致**させてください。

| rank_type | 説明 | 特徴 |
|----------|------|------|
| **Bronze** | ブロンズ | 初級ランク（4レベル） |
| **Silver** | シルバー | 中級ランク（4レベル） |
| **Gold** | ゴールド | 上級ランク（4レベル） |
| **Master** | マスター | 最上級ランク（4レベル） |
| **Platinum** | プラチナ | 特別最上級ランク（4レベル）※使用頻度低 |

**標準的なランク構成**:
- Bronze（4レベル）+ Silver（4レベル）+ Gold（4レベル）+ Master（4レベル）= 計16レベル

#### 4.4 ID採番ルール

```
{mst_advent_battle_id}_rank_{連番2桁}
```

**パラメータ**:
- `mst_advent_battle_id`: MstAdventBattle.idと同じID
- `連番2桁`: 01から順番に採番（Bronze 1→01、Bronze 2→02、...、Master 4→16）

**採番例**:
```
quest_raid_jig1_00001_rank_01   (Bronze レベル1)
quest_raid_jig1_00001_rank_05   (Silver レベル1)
quest_raid_jig1_00001_rank_09   (Gold レベル1)
quest_raid_jig1_00001_rank_13   (Master レベル1)
```

#### 4.5 スコア設定のガイドライン

**Bronze（初級）**:
- レベル1: 1,000～5,000
- レベル2: 5,000～10,000
- レベル3: 10,000～15,000
- レベル4: 15,000～30,000

**Silver（中級）**:
- レベル1: 30,000～50,000
- レベル2: 50,000～75,000
- レベル3: 75,000～100,000
- レベル4: 100,000～150,000

**Gold（上級）**:
- レベル1: 150,000～200,000
- レベル2: 200,000～250,000
- レベル3: 250,000～300,000
- レベル4: 300,000～500,000

**Master（最上級）**:
- レベル1: 500,000～1,000,000
- レベル2: 1,000,000～1,500,000
- レベル3: 1,500,000～2,000,000
- レベル4: 2,000,000以上

#### 4.6 作成例

```
ENABLE,id,mst_advent_battle_id,rank_type,rank_level,required_lower_score,asset_key,release_key
e,quest_raid_jig1_00001_rank_01,quest_raid_jig1_00001,Bronze,1,1000,,202601010
e,quest_raid_jig1_00001_rank_02,quest_raid_jig1_00001,Bronze,2,5000,,202601010
e,quest_raid_jig1_00001_rank_03,quest_raid_jig1_00001,Bronze,3,10000,,202601010
e,quest_raid_jig1_00001_rank_04,quest_raid_jig1_00001,Bronze,4,15000,,202601010
e,quest_raid_jig1_00001_rank_05,quest_raid_jig1_00001,Silver,1,30000,,202601010
e,quest_raid_jig1_00001_rank_06,quest_raid_jig1_00001,Silver,2,50000,,202601010
e,quest_raid_jig1_00001_rank_07,quest_raid_jig1_00001,Silver,3,75000,,202601010
e,quest_raid_jig1_00001_rank_08,quest_raid_jig1_00001,Silver,4,100000,,202601010
e,quest_raid_jig1_00001_rank_09,quest_raid_jig1_00001,Gold,1,150000,,202601010
e,quest_raid_jig1_00001_rank_10,quest_raid_jig1_00001,Gold,2,200000,,202601010
e,quest_raid_jig1_00001_rank_11,quest_raid_jig1_00001,Gold,3,250000,,202601010
e,quest_raid_jig1_00001_rank_12,quest_raid_jig1_00001,Gold,4,300000,,202601010
e,quest_raid_jig1_00001_rank_13,quest_raid_jig1_00001,Master,1,500000,,202601010
e,quest_raid_jig1_00001_rank_14,quest_raid_jig1_00001,Master,2,1000000,,202601010
e,quest_raid_jig1_00001_rank_15,quest_raid_jig1_00001,Master,3,1500000,,202601010
e,quest_raid_jig1_00001_rank_16,quest_raid_jig1_00001,Master,4,2000000,,202601010
```

### 5. MstAdventBattleClearReward シートの作成

#### 5.1 シートスキーマ

```
ENABLE,id,mst_advent_battle_id,reward_category,resource_type,resource_id,resource_amount,percentage,sort_order,release_key
```

#### 5.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | クリア報酬の一意識別子。命名規則: `{mst_advent_battle_id}_{連番1桁}` |
| **mst_advent_battle_id** | 降臨バトルID。MstAdventBattle.idと対応 |
| **reward_category** | 報酬カテゴリ。通常は `Random`（ランダム報酬） |
| **resource_type** | リソースタイプ。下記の「resource_type設定一覧」を参照 |
| **resource_id** | リソースID。resource_typeに応じた具体的なアイテムIDなど |
| **resource_amount** | リソース数量 |
| **percentage** | 獲得確率（%）。ランダム報酬の場合は確率を設定 |
| **sort_order** | 表示順序 |
| **release_key** | リリースキー。MstAdventBattleと同じ値 |

#### 5.3 resource_type設定一覧

| resource_type | 説明 | resource_idの例 |
|--------------|------|----------------|
| **Item** | アイテム | `memory_glo_00001`、`piece_jig_00401` |
| **Coin** | コイン | 空欄 |
| **FreeDiamond** | 無償ダイヤ | 空欄 |
| **PaidDiamond** | 有償ダイヤ | 空欄 |
| **Exp** | 経験値 | 空欄 |
| **Emblem** | エンブレム | `emblem_event_jig_00001` |

#### 5.4 ランダム報酬の確率設定ガイドライン

- **合計確率**: 全報酬の`percentage`合計は`100`にする
- **均等分配**: 5種類の報酬なら各20%が標準的
- **レアリティ調整**: 希少アイテムは確率を下げる

#### 5.5 作成例

```
ENABLE,id,mst_advent_battle_id,reward_category,resource_type,resource_id,resource_amount,percentage,sort_order,release_key
e,quest_raid_jig1_00001_1,quest_raid_jig1_00001,Random,Item,memory_glo_00001,3,20,1,202601010
e,quest_raid_jig1_00001_2,quest_raid_jig1_00001,Random,Item,memory_glo_00002,3,20,2,202601010
e,quest_raid_jig1_00001_3,quest_raid_jig1_00001,Random,Item,memory_glo_00003,3,20,3,202601010
e,quest_raid_jig1_00001_4,quest_raid_jig1_00001,Random,Item,memory_glo_00004,3,20,4,202601010
e,quest_raid_jig1_00001_5,quest_raid_jig1_00001,Random,Item,memory_glo_00005,3,20,5,202601010
```

上記の例: 5種類のメモリーアイテムを各20%の確率で3個ずつ獲得できる。

### 6. MstAdventBattleRewardGroup シートの作成

#### 6.1 シートスキーマ

```
ENABLE,id,mst_advent_battle_id,reward_category,condition_value,release_key
```

#### 6.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | 報酬グループの一意識別子。命名規則: `{mst_advent_battle_id変換}_reward_group_{連番5桁}_{連番2桁}` |
| **mst_advent_battle_id** | 降臨バトルID。MstAdventBattle.idと対応 |
| **reward_category** | 報酬カテゴリ。下記の「reward_category設定一覧」を参照 |
| **condition_value** | 条件値。reward_categoryに応じた達成条件（スコア、ランクID、順位等） |
| **release_key** | リリースキー。MstAdventBattleと同じ値 |

#### 6.3 reward_category設定一覧

| reward_category | 説明 | condition_valueの設定 |
|----------------|------|---------------------|
| **MaxScore** | 最高スコア報酬 | スコア閾値（例: `5000`、`10000`、`100000`） |
| **RaidTotalScore** | 累計スコア報酬 | 累計スコア閾値（例: `500`、`5000`、`160000`） |
| **Rank** | ランク到達報酬 | ランクID（例: `quest_raid_jig1_00001_rank_01`） |
| **Ranking** | ランキング報酬 | 順位（例: `1`、`50`、`1000`）または `Participation`（参加賞） |
| **RankUp** | ランクアップ報酬 | ランクID（新ランク到達時） |
| **FirstClear** | 初回クリア報酬 | 通常は空欄または`1` |

#### 6.4 ID採番ルール

```
{mst_advent_battle_id変換}_reward_group_{連番5桁}_{連番2桁}
```

**パラメータ**:
- `mst_advent_battle_id変換`: `quest_raid_jig1_00001` → `quest_raid_jig1` に変換
- `連番5桁`: 報酬グループの大分類（00001から）
- `連番2桁`: 報酬グループ内の連番（01から）

**採番例**:
```
quest_raid_jig1_reward_group_00001_01   (最初の報酬グループ1)
quest_raid_jig1_reward_group_00001_02   (最初の報酬グループ2)
quest_raid_jig1_reward_group_00001_44   (ランキング1位報酬)
```

#### 6.5 報酬設定の推奨構成

**MaxScore（最高スコア報酬）**:
- 段階的な報酬設定: 5,000 / 10,000 / 15,000 / 20,000 / 30,000 / 50,000 / 100,000 / 150,000 / 200,000 / 300,000
- 報酬内容: 無償ダイヤ、コイン、チケット、メモリーフラグメント

**RaidTotalScore（累計スコア報酬）**:
- こまめな報酬設定: 500 / 5,000 / 15,000 / 20,000 / ... / 160,000
- プレイヤーのモチベーション維持が目的
- 報酬内容: 無償ダイヤ、コイン、ボックスアイテム

**Rank（ランク到達報酬）**:
- 全16ランク（Bronze 1～Master 4）に対して報酬設定
- 報酬内容: 無償ダイヤ、コイン、メモリーフラグメント

**Ranking（ランキング報酬）**:
- 上位報酬: 1位、2位、3位、50位、300位、1,000位、5,000位、10,000位
- 参加賞: `Participation`
- 報酬内容: エンブレム、無償ダイヤ、コイン、チケット

#### 6.6 作成例

```
ENABLE,id,mst_advent_battle_id,reward_category,condition_value,release_key
e,quest_raid_jig1_reward_group_00001_01,quest_raid_jig1_00001,MaxScore,5000,202601010
e,quest_raid_jig1_reward_group_00001_02,quest_raid_jig1_00001,MaxScore,10000,202601010
e,quest_raid_jig1_reward_group_00001_03,quest_raid_jig1_00001,MaxScore,15000,202601010
e,quest_raid_jig1_reward_group_00001_10,quest_raid_jig1_00001,RaidTotalScore,500,202601010
e,quest_raid_jig1_reward_group_00001_11,quest_raid_jig1_00001,RaidTotalScore,5000,202601010
e,quest_raid_jig1_reward_group_00001_28,quest_raid_jig1_00001,Rank,quest_raid_jig1_00001_rank_01,202601010
e,quest_raid_jig1_reward_group_00001_29,quest_raid_jig1_00001,Rank,quest_raid_jig1_00001_rank_02,202601010
e,quest_raid_jig1_reward_group_00001_44,quest_raid_jig1_00001,Ranking,1,202601010
e,quest_raid_jig1_reward_group_00001_45,quest_raid_jig1_00001,Ranking,2,202601010
e,quest_raid_jig1_reward_group_00001_52,quest_raid_jig1_00001,Ranking,Participation,202601010
```

### 7. MstAdventBattleReward シートの作成

#### 7.1 シートスキーマ

```
ENABLE,id,mst_advent_battle_reward_group_id,resource_type,resource_id,resource_amount,release_key
```

#### 7.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | 報酬の一意識別子。命名規則: `{mst_advent_battle_reward_group_id}_{連番1桁}` |
| **mst_advent_battle_reward_group_id** | 報酬グループID。MstAdventBattleRewardGroup.idと対応 |
| **resource_type** | リソースタイプ。MstAdventBattleClearRewardと同じ（`Item`、`Coin`、`FreeDiamond`等） |
| **resource_id** | リソースID。resource_typeに応じた具体的なアイテムIDなど |
| **resource_amount** | リソース数量 |
| **release_key** | リリースキー。MstAdventBattleと同じ値 |

#### 7.3 複数報酬の設定

1つの報酬グループに複数の報酬を設定する場合、同じ`mst_advent_battle_reward_group_id`で複数レコードを作成します。

**作成例**:
```
quest_raid_jig1_reward_group_00001_28_1  （報酬1: 無償ダイヤ10個）
quest_raid_jig1_reward_group_00001_28_2  （報酬2: コイン1000枚）
quest_raid_jig1_reward_group_00001_28_3  （報酬3: メモリーフラグメント1個）
```

#### 7.4 作成例

```
ENABLE,id,mst_advent_battle_reward_group_id,resource_type,resource_id,resource_amount,release_key
e,quest_raid_jig1_reward_group_00001_01_1,quest_raid_jig1_reward_group_00001_01,FreeDiamond,,20,202601010
e,quest_raid_jig1_reward_group_00001_02_1,quest_raid_jig1_reward_group_00001_02,Coin,,1500,202601010
e,quest_raid_jig1_reward_group_00001_28_1,quest_raid_jig1_reward_group_00001_28,FreeDiamond,,10,202601010
e,quest_raid_jig1_reward_group_00001_28_2,quest_raid_jig1_reward_group_00001_28,Coin,,1000,202601010
e,quest_raid_jig1_reward_group_00001_28_3,quest_raid_jig1_reward_group_00001_28,Item,memoryfragment_glo_00001,1,202601010
e,quest_raid_jig1_reward_group_00001_44_1,quest_raid_jig1_reward_group_00001_44,Emblem,emblem_adventbattle_jig_season01_00001,1,202601010
e,quest_raid_jig1_reward_group_00001_44_2,quest_raid_jig1_reward_group_00001_44,FreeDiamond,,1000,202601010
e,quest_raid_jig1_reward_group_00001_44_3,quest_raid_jig1_reward_group_00001_44,Coin,,100000,202601010
e,quest_raid_jig1_reward_group_00001_44_4,quest_raid_jig1_reward_group_00001_44,Item,ticket_glo_00002,5,202601010
```

上記の例:
- 報酬グループ01: 無償ダイヤ20個のみ
- 報酬グループ28: 無償ダイヤ10個 + コイン1000枚 + メモリーフラグメント1個
- 報酬グループ44（1位報酬）: エンブレム + 無償ダイヤ1000個 + コイン100,000枚 + チケット5枚

### 8. MstEventBonusUnit シートの作成

#### 8.1 シートスキーマ

```
ENABLE,id,mst_unit_id,bonus_percentage,event_bonus_group_id,is_pick_up,release_key
```

#### 8.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | 特効の一意識別子。通し番号（1から） |
| **mst_unit_id** | ユニットID。MstUnit.idと対応（例: `chara_jig_00401`） |
| **bonus_percentage** | ボーナス率（%）。通常は`5`、`10`、`15`、`20` |
| **event_bonus_group_id** | 特効グループID。MstAdventBattle.event_bonus_group_idと対応（例: `raid_jig1_00001`） |
| **is_pick_up** | ピックアップフラグ。通常は空欄 |
| **release_key** | リリースキー。MstAdventBattleと同じ値 |

#### 8.3 ボーナス率の設定ガイドライン

**推奨ボーナス率**:
- **20%**: 最高ボーナス（新規実装URキャラ等）
- **15%**: 高ボーナス（イベント主役キャラ）
- **10%**: 中ボーナス（イベント関連キャラ）
- **5%**: 低ボーナス（一般的な関連キャラ）

**設定数の目安**:
- 特効キャラ: 5～10体程度
- ボーナス率の分散: 高ボーナスは1～2体、中低ボーナスは複数体

#### 8.4 作成例

```
ENABLE,id,mst_unit_id,bonus_percentage,event_bonus_group_id,is_pick_up,release_key
e,54,chara_jig_00401,20,raid_jig1_00001,,202601010
e,55,chara_jig_00001,15,raid_jig1_00001,,202601010
e,56,chara_jig_00101,10,raid_jig1_00001,,202601010
e,57,chara_jig_00201,10,raid_jig1_00001,,202601010
e,58,chara_jig_00501,10,raid_jig1_00001,,202601010
e,59,chara_jig_00601,10,raid_jig1_00001,,202601010
e,60,chara_jig_00301,5,raid_jig1_00001,,202601010
```

上記の例: 7体の特効キャラ（20%: 1体、15%: 1体、10%: 4体、5%: 1体）

## データ整合性のチェック

マスタデータ作成後、以下の項目を確認してください。

### 必須チェック項目

- [ ] **ヘッダーの列順が正しいか**
  - スキーマファイルと完全一致している

- [ ] **IDの一意性**
  - すべてのidが一意である
  - 他のリリースのidと重複していない

- [ ] **ID採番ルール**
  - MstAdventBattle.id: `quest_raid_{series_id}{連番1桁}_{連番5桁}`
  - MstAdventBattle.event_bonus_group_id: `raid_{series_id}{連番1桁}_{連番5桁}`
  - MstAdventBattleI18n.id: `{mst_advent_battle_id}_{locale}`
  - MstAdventBattleRank.id: `{mst_advent_battle_id}_rank_{連番2桁}`
  - MstAdventBattleRewardGroup.id: `{mst_advent_battle_id変換}_reward_group_{連番5桁}_{連番2桁}`
  - MstAdventBattleReward.id: `{mst_advent_battle_reward_group_id}_{連番1桁}`

- [ ] **リレーションの整合性**
  - `MstAdventBattle.mst_event_id` が `MstEvent.id` に存在する
  - `MstAdventBattle.mst_in_game_id` が `MstInGame.id` に存在する
  - `MstAdventBattle.event_bonus_group_id` が `MstEventBonusUnit.event_bonus_group_id` に存在する
  - `MstAdventBattle.display_mst_unit_id1` が `MstEnemyCharacter.id` に存在する
  - `MstAdventBattleI18n.mst_advent_battle_id` が `MstAdventBattle.id` に存在する
  - `MstAdventBattleRank.mst_advent_battle_id` が `MstAdventBattle.id` に存在する
  - `MstAdventBattleClearReward.mst_advent_battle_id` が `MstAdventBattle.id` に存在する
  - `MstAdventBattleRewardGroup.mst_advent_battle_id` が `MstAdventBattle.id` に存在する
  - `MstAdventBattleRewardGroup.condition_value`（Rankの場合）が `MstAdventBattleRank.id` に存在する
  - `MstAdventBattleReward.mst_advent_battle_reward_group_id` が `MstAdventBattleRewardGroup.id` に存在する
  - `MstEventBonusUnit.mst_unit_id` が `MstUnit.id` に存在する

- [ ] **enum値の正確性**
  - advent_battle_type: ScoreChallenge、BossDefeat、TimeAttack
  - score_addition_type: AllEnemiesAndOutPost、BossOnly、None
  - rank_type: Bronze、Silver、Gold、Master、Platinum
  - resource_type: Item、Coin、FreeDiamond、PaidDiamond、Exp、Emblem
  - reward_category: MaxScore、RaidTotalScore、Rank、Ranking、RankUp、FirstClear
  - 大文字小文字が正確に一致している

- [ ] **ランク設定の完全性**
  - MstAdventBattleRankに16レコード（Bronze～Master各4レベル）が存在する
  - required_lower_scoreが昇順に設定されている

- [ ] **報酬設定の完全性**
  - MaxScore報酬: 10～15段階程度
  - RaidTotalScore報酬: 15～20段階程度
  - Rank報酬: 16段階（全ランクに対応）
  - Ranking報酬: 上位報酬 + 参加賞

- [ ] **クリア報酬の確率合計**
  - MstAdventBattleClearRewardのpercentageの合計が`100`である

- [ ] **開催期間の妥当性**
  - start_atとend_atが`"`で囲まれている
  - フォーマットが`YYYY-MM-DD HH:MM:SS`である
  - start_at < end_at
  - イベント期間内に収まっている

- [ ] **特効設定の妥当性**
  - bonus_percentageが適切な値（5、10、15、20）である
  - 特効キャラが5～10体程度である

### 推奨チェック項目

- [ ] **命名規則の統一**
  - idのプレフィックスがシリーズIDと一致している

- [ ] **I18n設定の完全性**
  - 日本語（ja）が必須で設定されている
  - 他言語（en、zh-CN、zh-TW）も設定されている

- [ ] **報酬バランスの妥当性**
  - 無償ダイヤ、コイン、アイテムのバランスが適切
  - ランキング上位報酬が魅力的である

- [ ] **スコア設定の妥当性**
  - initial_battle_point、score_additional_coefが適切
  - ランクのrequired_lower_scoreが段階的で現実的

## 出力フォーマット

最終的な出力は以下の7シート構成で行います。

### MstAdventBattle シート

| ENABLE | id | mst_event_id | mst_in_game_id | asset_key | advent_battle_type | initial_battle_point | score_addition_type | score_additional_coef | score_addition_target_mst_enemy_stage_parameter_id | mst_stage_rule_group_id | event_bonus_group_id | challengeable_count | ad_challengeable_count | display_mst_unit_id1 | display_mst_unit_id2 | display_mst_unit_id3 | exp | coin | start_at | end_at | release_key |
|--------|----|--------------|--------------|---------|--------------------|---------------------|---------------------|----------------------|--------------------------------------------------|------------------------|---------------------|---------------------|----------------------|---------------------|---------------------|---------------------|-----|------|----------|--------|-------------|
| e | quest_raid_jig1_00001 | event_jig_00001 | raid_jig1_00001 | jig_00001 | ScoreChallenge | 500 | AllEnemiesAndOutPost | 0.07 | test | | raid_jig1_00001 | 3 | 2 | enemy_jig_00601 | | | 100 | 300 | "2026-01-23 15:00:00" | "2026-01-29 14:59:59" | 202601010 |

### MstAdventBattleI18n シート

| ENABLE | release_key | id | mst_advent_battle_id | language | name | boss_description |
|--------|-------------|----|--------------------|----------|------|------------------|
| e | 202601010 | quest_raid_jig1_00001_ja | quest_raid_jig1_00001 | ja | まるで 悪夢を見ているようだ | ボスを倒して高スコア獲得!! |

### MstAdventBattleRank シート

| ENABLE | id | mst_advent_battle_id | rank_type | rank_level | required_lower_score | asset_key | release_key |
|--------|----|--------------------|----------|-----------|---------------------|-----------|-------------|
| e | quest_raid_jig1_00001_rank_01 | quest_raid_jig1_00001 | Bronze | 1 | 1000 | | 202601010 |
| e | quest_raid_jig1_00001_rank_05 | quest_raid_jig1_00001 | Silver | 1 | 30000 | | 202601010 |
| e | quest_raid_jig1_00001_rank_09 | quest_raid_jig1_00001 | Gold | 1 | 150000 | | 202601010 |
| e | quest_raid_jig1_00001_rank_13 | quest_raid_jig1_00001 | Master | 1 | 500000 | | 202601010 |

### MstAdventBattleClearReward シート

| ENABLE | id | mst_advent_battle_id | reward_category | resource_type | resource_id | resource_amount | percentage | sort_order | release_key |
|--------|----|--------------------|----------------|--------------|------------|----------------|-----------|-----------|-------------|
| e | quest_raid_jig1_00001_1 | quest_raid_jig1_00001 | Random | Item | memory_glo_00001 | 3 | 20 | 1 | 202601010 |

### MstAdventBattleRewardGroup シート

| ENABLE | id | mst_advent_battle_id | reward_category | condition_value | release_key |
|--------|----|--------------------|----------------|----------------|-------------|
| e | quest_raid_jig1_reward_group_00001_01 | quest_raid_jig1_00001 | MaxScore | 5000 | 202601010 |
| e | quest_raid_jig1_reward_group_00001_10 | quest_raid_jig1_00001 | RaidTotalScore | 500 | 202601010 |
| e | quest_raid_jig1_reward_group_00001_28 | quest_raid_jig1_00001 | Rank | quest_raid_jig1_00001_rank_01 | 202601010 |
| e | quest_raid_jig1_reward_group_00001_44 | quest_raid_jig1_00001 | Ranking | 1 | 202601010 |
| e | quest_raid_jig1_reward_group_00001_52 | quest_raid_jig1_00001 | Ranking | Participation | 202601010 |

### MstAdventBattleReward シート

| ENABLE | id | mst_advent_battle_reward_group_id | resource_type | resource_id | resource_amount | release_key |
|--------|----|---------------------------------|--------------|------------|----------------|-------------|
| e | quest_raid_jig1_reward_group_00001_01_1 | quest_raid_jig1_reward_group_00001_01 | FreeDiamond | | 20 | 202601010 |
| e | quest_raid_jig1_reward_group_00001_28_1 | quest_raid_jig1_reward_group_00001_28 | FreeDiamond | | 10 | 202601010 |
| e | quest_raid_jig1_reward_group_00001_28_2 | quest_raid_jig1_reward_group_00001_28 | Coin | | 1000 | 202601010 |
| e | quest_raid_jig1_reward_group_00001_28_3 | quest_raid_jig1_reward_group_00001_28 | Item | memoryfragment_glo_00001 | 1 | 202601010 |

### MstEventBonusUnit シート

| ENABLE | id | mst_unit_id | bonus_percentage | event_bonus_group_id | is_pick_up | release_key |
|--------|----|-----------|-----------------|--------------------|-----------|-------------|
| e | 54 | chara_jig_00401 | 20 | raid_jig1_00001 | | 202601010 |
| e | 55 | chara_jig_00001 | 15 | raid_jig1_00001 | | 202601010 |

## 重要なポイント

- **7テーブル構成**: 降臨バトルは複雑な報酬システムを持つ
- **I18nは独立したシート**: MstAdventBattleI18nは独立したシートとして作成
- **16段階のランク設定**: Bronze～Master各4レベル
- **4種類の報酬カテゴリ**: MaxScore、RaidTotalScore、Rank、Ranking
- **特効システム**: 5～10体のキャラに5～20%のボーナス設定
- **開催期間の重要性**: start_atとend_atを必ず`"`で囲む
- **外部キー整合性の徹底**: すべてのリレーションが正しく設定されていることを確認
- **報酬バランスの調整**: プレイヤーのモチベーションを維持する適切な報酬設計

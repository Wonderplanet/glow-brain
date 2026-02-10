# 敵・自動行動 マスタデータ設定手順書

## 概要

敵キャラクター（エネミー）と自動行動パターン（オートプレイヤー）のマスタデータ作成手順を記載します。
本手順書に従うことで、ゲーム内でエラーが発生しない正確なマスタデータを作成できます。

## 対象テーブル

敵・自動行動のマスタデータは、以下の5テーブル構成で作成します。

**敵キャラクター基本情報**:
- **MstEnemyCharacter** - 敵キャラクターの基本情報（ユニットIDとの紐付け）
- **MstEnemyCharacterI18n** - 敵キャラクター名（多言語対応）

**敵のステージ別パラメータ**:
- **MstEnemyStageParameter** - ステージごとの敵のステータス（HP、攻撃力、移動速度等）

**敵拠点情報**:
- **MstEnemyOutpost** - 敵拠点（アウトポスト）の設定

**自動行動パターン**:
- **MstAutoPlayerSequence** - 敵の召喚タイミング、行動パターン、条件分岐等

**重要**: 各I18nテーブルは独立したシートとして作成します。

## 敵・自動行動の構造

### 敵キャラクターの2つの役割

GLOWでは、敵キャラクターは以下の2つの形態で登場します。

1. **プレイアブルキャラの敵バージョン** - ヒーローとして実装されたキャラが敵として登場
   - 例: `chara_jig_00401`（賊王 亜左 弔兵衛）が敵として登場
   - MstUnitで定義されたキャラを敵として流用

2. **敵専用キャラクター** - プレイアブルではない敵専用のキャラ
   - 例: `enemy_jig_00301`（雑魚敵）
   - MstEnemyCharacterのみで定義

### ステージごとの敵パラメータ

同じ敵キャラクターでも、ステージやクエストによって異なるパラメータを設定できます。

- **MstEnemyStageParameter**: ステージ別の敵の強さ（HP、攻撃力、移動速度等）
- **命名規則**: `{base_character_id}_{quest_id}_{role}_{color}`
  - 例: `c_jig_00401_jig1_charaget02_Boss_Red`（賊王、共闘関係編、ボス、赤属性）

### 自動行動シーケンス

敵の召喚タイミングや行動パターンを**MstAutoPlayerSequence**で制御します。

- **シーケンスセットID**: ステージやクエストごとの自動行動グループ
- **条件タイプ**: 経過時間、拠点HP、敵撃破等のトリガー
- **アクションタイプ**: 敵召喚、グループ切り替え等の行動

## 作成フロー

### 1. 仕様書の確認

クエスト設計書（ストーリー、デイリー、チャレンジ、降臨バトル等）から以下の情報を抽出します。

**必要情報**:
- クエストID、ステージID
- 出現する敵キャラクターのリスト（プレイアブル/敵専用の区別）
- 各敵の役割（Normal、Boss）と属性（Red、Blue、Green、Yellow、Colorless）
- 敵のステータス（HP、攻撃力、移動速度、ノックバック耐性等）
- 敵拠点の設定（HP、ダメージ無効化フラグ、アートワーク）
- 敵の召喚タイミング（初期配置、経過時間、条件トリガー）
- 敵の出現パターン（召喚間隔、召喚数、召喚位置）

### 2. MstEnemyCharacter シートの作成

**重要**: プレイアブルキャラの敵バージョンの場合のみ作成します。敵専用キャラの場合は作成不要です。

#### 2.1 シートスキーマ

```
ENABLE,id,mst_unit_id,asset_key,release_key
```

#### 2.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | 敵キャラクターID。命名規則: `enemy_{series_id}_{連番5桁}` または プレイアブルキャラの場合は `mst_unit_id` をそのまま使用 |
| **mst_unit_id** | 対応するプレイアブルキャラのID（MstUnit.id）。例: `chara_jig_00401` |
| **asset_key** | アセットキー。通常は `id` と同じ値 |
| **release_key** | リリースキー。例: `202601010` |

#### 2.3 ID採番ルール

**プレイアブルキャラの敵バージョン**:
```
{mst_unit_id}をそのまま使用
例: chara_jig_00401
```

**敵専用キャラ**:
```
enemy_{series_id}_{連番5桁}
例: enemy_jig_00301
```

#### 2.4 作成例

```
ENABLE,id,mst_unit_id,asset_key,release_key
e,chara_jig_00401,chara_jig_00401,chara_jig_00401,202601010
e,chara_jig_00501,chara_jig_00501,chara_jig_00501,202601010
e,chara_jig_00601,chara_jig_00601,chara_jig_00601,202601010
```

**注**: 敵専用キャラ（enemy_jig_*）はこのテーブルに含まれません。

### 3. MstEnemyCharacterI18n シートの作成

#### 3.1 シートスキーマ

```
ENABLE,id,mst_enemy_character_id,locale,enemy_name,release_key
```

#### 3.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | I18nの一意識別子。命名規則: `{mst_enemy_character_id}_i18n_{locale}` |
| **mst_enemy_character_id** | 敵キャラクターID。MstEnemyCharacter.idと対応 |
| **locale** | 言語コード。`ja`: 日本語、`en`: 英語、`zh-CN`: 中国語（簡体字）、`zh-TW`: 中国語（繁体字） |
| **enemy_name** | 敵キャラクター名（表示名） |
| **release_key** | リリースキー。MstEnemyCharacterと同じ値 |

#### 3.3 作成例

```
ENABLE,id,mst_enemy_character_id,locale,enemy_name,release_key
e,chara_jig_00401_i18n_ja,chara_jig_00401,ja,【賊王】亜左 弔兵衛,202601010
e,chara_jig_00501_i18n_ja,chara_jig_00501,ja,山田浅ェ門 桐馬,202601010
```

### 4. MstEnemyStageParameter シートの作成

#### 4.1 シートスキーマ

```
ENABLE,release_key,id,mst_enemy_character_id,character_unit_kind,role_type,color,sort_order,hp,damage_knock_back_count,move_speed,well_distance,attack_power,attack_combo_cycle,mst_unit_ability_id1,drop_battle_point,mstTransformationEnemyStageParameterId,transformationConditionType,transformationConditionValue
```

#### 4.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **release_key** | リリースキー。例: `202601010` |
| **id** | 敵ステージパラメータID。命名規則: `{base_character_id}_{quest_id}_{character_unit_kind}_{color}` |
| **mst_enemy_character_id** | 敵キャラクターID。MstEnemyCharacter.idまたは敵専用キャラのID（enemy_jig_*） |
| **character_unit_kind** | キャラクター種別。`Normal`: 通常敵、`Boss`: ボス敵 |
| **role_type** | ロールタイプ。`Technical`、`Support`、`Defense`、`Special`、`Attack` |
| **color** | 属性。`Colorless`、`Red`、`Blue`、`Yellow`、`Green` |
| **sort_order** | ソート順序。通常は `1`～連番 |
| **hp** | HP。ステージの難易度に応じて設定（例: Normal: 1000、Boss: 10000） |
| **damage_knock_back_count** | ノックバック耐性回数。`0`: 無限、`1`～: 回数制限 |
| **move_speed** | 移動速度（0～100程度） |
| **well_distance** | ウェル距離（通常は `0.2`～`0.4`程度） |
| **attack_power** | 攻撃力（例: 100～500） |
| **attack_combo_cycle** | 攻撃コンボサイクル（例: 1～7） |
| **mst_unit_ability_id1** | アビリティID（任意）。特殊能力を持つ敵の場合のみ設定 |
| **drop_battle_point** | ドロップBP（撃破時に獲得できるバトルポイント） |
| **mstTransformationEnemyStageParameterId** | 変身先の敵ステージパラメータID（変身する場合のみ） |
| **transformationConditionType** | 変身条件タイプ。通常は `None` |
| **transformationConditionValue** | 変身条件値 |

#### 4.3 ID採番ルール

```
{base_character_id}_{quest_id}_{character_unit_kind}_{color}
```

**パラメータ**:
- `base_character_id`: `c_jig_XXXXX`（プレイアブル）または `e_jig_XXXXX`（敵専用）
  - c = character（プレイアブルキャラ）
  - e = enemy（敵専用キャラ）
- `quest_id`: クエストまたはステージを識別する短縮ID
  - 例: `jig1_charaget02`（地獄楽イベント1、キャラ入手クエスト02）
  - 例: `jig1_advent`（地獄楽イベント1、降臨バトル）
- `character_unit_kind`: `Normal`または`Boss`
- `color`: `Colorless`、`Red`、`Blue`、`Yellow`、`Green`

**採番例**:
```
c_jig_00401_jig1_charaget02_Boss_Red
c_jig_00501_jig1_charaget02_Normal_Red
e_jig_00301_jig1_advent_Boss_Green
e_jig_00601_jig1_advent_Boss_Yellow
```

#### 4.4 作成例

```
ENABLE,release_key,id,mst_enemy_character_id,character_unit_kind,role_type,color,sort_order,hp,damage_knock_back_count,move_speed,well_distance,attack_power,attack_combo_cycle,mst_unit_ability_id1,drop_battle_point,mstTransformationEnemyStageParameterId,transformationConditionType,transformationConditionValue
e,202601010,c_jig_00401_jig1_charaget02_Boss_Red,chara_jig_00401,Boss,Technical,Red,5,10000,2,30,0.31,100,6,,500,,None,
e,202601010,e_jig_00301_jig1_advent_Boss_Green,enemy_jig_00301,Boss,Defense,Green,104,30000,0,35,0.17,400,1,,400,,None,
```

#### 4.5 ステータス設定のポイント

- **hp**: ステージの難易度に応じて設定
  - 通常敵（Normal）: 1,000～10,000
  - ボス敵（Boss）: 10,000～100,000以上
  - 降臨バトルのボス: 100,000～300,000以上

- **damage_knock_back_count**: ノックバック耐性
  - `0`: 無限（ノックバックしない）
  - `1`～`3`: 制限あり（通常敵）

- **move_speed**: 移動速度
  - 低速: 20～30
  - 通常: 30～40
  - 高速: 40～70

- **drop_battle_point**: 撃破時BP
  - 雑魚敵: 50～100
  - 通常敵: 100～300
  - ボス: 300～1000以上

### 5. MstEnemyOutpost シートの作成

#### 5.1 シートスキーマ

```
ENABLE,id,hp,is_damage_invalidation,outpost_asset_key,artwork_asset_key,release_key
```

#### 5.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | 敵拠点ID。命名規則: `{quest_id}_{stage_number}` |
| **hp** | 拠点HP。ステージの難易度に応じて設定 |
| **is_damage_invalidation** | ダメージ無効化フラグ。`0`: 有効、`1`: 無効（降臨バトル等） |
| **outpost_asset_key** | 拠点アセットキー（通常は空欄） |
| **artwork_asset_key** | 原画アセットキー。原画が設定されている場合のみ指定 |
| **release_key** | リリースキー。例: `202601010` |

#### 5.3 ID採番ルール

```
{quest_id}_{stage_number}
```

**パラメータ**:
- `quest_id`: クエストを識別するID
  - 例: `event_jig1_charaget01`（キャラ入手クエスト1）
  - 例: `raid_jig1_00001`（降臨バトル）
- `stage_number`: ステージ番号（5桁ゼロパディング）

**採番例**:
```
event_jig1_charaget01_00001
event_jig1_charaget02_00006
raid_jig1_00001
```

#### 5.4 作成例

```
ENABLE,id,hp,is_damage_invalidation,outpost_asset_key,artwork_asset_key,release_key
e,event_jig1_charaget01_00001,30000,,,event_jig_0001,202601010
e,event_jig1_charaget02_00006,60000,,,event_jig_0002,202601010
e,raid_jig1_00001,1000000,1,,jig_0003,202601010
```

#### 5.5 拠点HP設定のポイント

- **ストーリークエスト（序盤）**: 30,000～50,000
- **ストーリークエスト（後半）**: 50,000～60,000
- **チャレンジクエスト**: 60,000～100,000
- **高難度クエスト**: 100,000～200,000
- **降臨バトル**: 1,000,000以上（ダメージ無効化）

### 6. MstAutoPlayerSequence シートの作成

#### 6.1 シートスキーマ

MstAutoPlayerSequenceは34カラムの複雑なテーブルです。

```
ENABLE,id,sequence_set_id,sequence_group_id,sequence_element_id,priority_sequence_element_id,condition_type,condition_value,action_type,action_value,action_value2,summon_count,summon_interval,summon_animation_type,summon_position,move_start_condition_type,move_start_condition_value,move_stop_condition_type,move_stop_condition_value,move_restart_condition_type,move_restart_condition_value,move_loop_count,is_summon_unit_outpost_damage_invalidation,last_boss_trigger,aura_type,death_type,enemy_hp_coef,enemy_attack_coef,enemy_speed_coef,override_drop_battle_point,defeated_score,action_delay,deactivation_condition_type,deactivation_condition_value,release_key
```

#### 6.2 主要カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | シーケンスID。命名規則: `{sequence_set_id}_{連番}` |
| **sequence_set_id** | シーケンスセットID。ステージやクエストごとのグループID |
| **sequence_group_id** | シーケンスグループID。フェーズやウェーブの区別（任意） |
| **sequence_element_id** | シーケンス要素ID。同じセット内での連番 |
| **priority_sequence_element_id** | 優先度付きシーケンスID（任意） |
| **condition_type** | 条件タイプ。下記の「condition_type一覧」を参照 |
| **condition_value** | 条件値。条件タイプに応じた値 |
| **action_type** | アクションタイプ。下記の「action_type一覧」を参照 |
| **action_value** | アクション値。敵IDやグループIDなど |
| **action_value2** | アクション値2（任意） |
| **summon_count** | 召喚数。一度に召喚する敵の数 |
| **summon_interval** | 召喚間隔（ミリ秒）。連続召喚の間隔 |
| **summon_animation_type** | 召喚アニメーションタイプ。`None`、`Fall0`、`Fall4`等 |
| **summon_position** | 召喚位置。特定のコマインデックスに召喚する場合に指定 |
| **move_start_condition_type** | 移動開始条件タイプ |
| **move_start_condition_value** | 移動開始条件値 |
| **move_stop_condition_type** | 移動停止条件タイプ |
| **move_stop_condition_value** | 移動停止条件値 |
| **move_restart_condition_type** | 移動再開条件タイプ |
| **move_restart_condition_value** | 移動再開条件値 |
| **move_loop_count** | 移動ループ回数 |
| **is_summon_unit_outpost_damage_invalidation** | 召喚ユニットの拠点ダメージ無効化フラグ |
| **last_boss_trigger** | ラストボストリガー。`1`: 最後のボス |
| **aura_type** | オーラタイプ。`Default`、`AdventBoss1`、`AdventBoss2`、`AdventBoss3` |
| **death_type** | 死亡タイプ。`Normal`、`Boss` |
| **enemy_hp_coef** | 敵HP係数。ベース値に対する倍率 |
| **enemy_attack_coef** | 敵攻撃力係数。ベース値に対する倍率 |
| **enemy_speed_coef** | 敵移動速度係数。ベース値に対する倍率 |
| **override_drop_battle_point** | ドロップBPオーバーライド（任意） |
| **defeated_score** | 撃破時スコア（降臨バトル等） |
| **action_delay** | アクション遅延（ミリ秒） |
| **deactivation_condition_type** | 無効化条件タイプ |
| **deactivation_condition_value** | 無効化条件値 |
| **release_key** | リリースキー。例: `202601010` |

#### 6.3 condition_type一覧

| condition_type | 説明 | condition_value |
|---------------|------|----------------|
| **InitialSummon** | 初期召喚（ステージ開始直後） | `0`または`1` |
| **ElapsedTime** | 経過時間（ミリ秒） | ミリ秒数 |
| **ElapsedTimeSinceSequenceGroupActivated** | グループ有効化からの経過時間 | ミリ秒数 |
| **OutpostHpPercentage** | 拠点HP割合 | HP% |
| **OutpostDamage** | 拠点ダメージ発生時 | ダメージ閾値 |
| **FriendUnitDead** | 味方ユニット（敵）の死亡 | `sequence_element_id` |
| **EnterTargetKoma** | プレイヤーユニットがターゲットコマに進入 | コマインデックス |
| **EnterTargetKomaIndex** | プレイヤーユニットが特定コマに進入 | コマインデックス |

#### 6.4 action_type一覧

| action_type | 説明 | action_value |
|------------|------|-------------|
| **SummonEnemy** | 敵を召喚 | 敵ステージパラメータID（MstEnemyStageParameter.id） |
| **SwitchSequenceGroup** | シーケンスグループを切り替え | グループID |

#### 6.5 召喚アニメーションタイプ

| summon_animation_type | 説明 |
|----------------------|------|
| **None** | アニメーションなし（通常召喚） |
| **Fall0** | 落下アニメーション（タイプ0） |
| **Fall4** | 落下アニメーション（タイプ4） |

#### 6.6 オーラタイプ

| aura_type | 説明 |
|----------|------|
| **Default** | 通常のオーラ |
| **Boss** | ボスオーラ |
| **AdventBoss1** | 降臨バトルボスオーラ（レベル1） |
| **AdventBoss2** | 降臨バトルボスオーラ（レベル2） |
| **AdventBoss3** | 降臨バトルボスオーラ（レベル3） |

#### 6.7 作成例

**シンプルな時間経過召喚**:
```
ENABLE,id,sequence_set_id,sequence_group_id,sequence_element_id,priority_sequence_element_id,condition_type,condition_value,action_type,action_value,action_value2,summon_count,summon_interval,summon_animation_type,summon_position,move_start_condition_type,move_start_condition_value,move_stop_condition_type,move_stop_condition_value,move_restart_condition_type,move_restart_condition_value,move_loop_count,is_summon_unit_outpost_damage_invalidation,last_boss_trigger,aura_type,death_type,enemy_hp_coef,enemy_attack_coef,enemy_speed_coef,override_drop_battle_point,defeated_score,action_delay,deactivation_condition_type,deactivation_condition_value,release_key
e,event_jig1_charaget02_00001_1,event_jig1_charaget02_00001,,1,,InitialSummon,0,SummonEnemy,c_jig_00601_jig1_charaget02_Boss_Red,,1,0,None,1.5,Damage,1,None,,None,,,,,Default,Normal,2,4,1,200,0,,None,,202601010
e,event_jig1_charaget02_00001_2,event_jig1_charaget02_00001,,2,,ElapsedTime,500,SummonEnemy,e_jig_00402_jig1_charaget02_Normal_Colorless,,5,900,None,,None,,None,,None,,,,,Default,Normal,6,2,1,70,0,,None,,202601010
```

**降臨バトルの複雑なシーケンス（グループ切り替え）**:
```
e,raid_jig1_00001_3,raid_jig1_00001,,3,,InitialSummon,1,SummonEnemy,e_jig_00301_jig1_advent_Boss_Colorless,,1,0,None,1.7,EnterTargetKoma,1,None,,None,,,,,AdventBoss2,Normal,8,0.6,1,800,700,,None,,202601010
e,raid_jig1_00001_4,raid_jig1_00001,,groupchange_1,,ElapsedTime,200,SwitchSequenceGroup,w1,,,,None,,None,,None,,None,,,,,Default,Normal,1,1,1,,,,None,,202601010
e,raid_jig1_00001_5,raid_jig1_00001,w1,101,,ElapsedTimeSinceSequenceGroupActivated,0,SummonEnemy,e_jig_00401_jig1_advent_Normal_Yellow,,10,450,None,,None,,None,,None,,,,,Default,Normal,5,0.4,1,50,30,,None,,202601010
```

#### 6.8 自動行動パターン設計のポイント

**初期召喚（ステージ開始時）**:
```
condition_type: InitialSummon
condition_value: 0
```

**時間経過での召喚**:
```
condition_type: ElapsedTime
condition_value: 500（ミリ秒）
```

**拠点HPトリガー**:
```
condition_type: OutpostHpPercentage
condition_value: 99（HP99%以下で発動）
```

**敵撃破トリガー**:
```
condition_type: FriendUnitDead
condition_value: {sequence_element_id}（撃破対象のシーケンス要素ID）
```

**プレイヤー進行トリガー**:
```
condition_type: EnterTargetKomaIndex
condition_value: 2（コマインデックス2に進入時）
```

**グループ切り替え（ウェーブ制御）**:
```
action_type: SwitchSequenceGroup
action_value: w1（切り替え先のグループID）
```

**連続召喚**:
```
summon_count: 5（5体召喚）
summon_interval: 900（900ミリ秒間隔）
```

**係数による強化**:
```
enemy_hp_coef: 2（HP2倍）
enemy_attack_coef: 4（攻撃力4倍）
enemy_speed_coef: 1（移動速度変更なし）
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
  - MstEnemyCharacter.id: プレイアブルキャラの場合は `mst_unit_id`、敵専用の場合は `enemy_{series_id}_{連番5桁}`
  - MstEnemyStageParameter.id: `{base_character_id}_{quest_id}_{character_unit_kind}_{color}`
  - MstEnemyOutpost.id: `{quest_id}_{stage_number}`
  - MstAutoPlayerSequence.id: `{sequence_set_id}_{連番}`
  - I18n系テーブルのid: `{親テーブルid}_i18n_{locale}`

- [ ] **リレーションの整合性**
  - `MstEnemyCharacter.mst_unit_id` が `MstUnit.id` に存在する
  - `MstEnemyStageParameter.mst_enemy_character_id` が `MstEnemyCharacter.id` または敵専用キャラID（enemy_jig_*）に存在する
  - `MstEnemyCharacterI18n.mst_enemy_character_id` が `MstEnemyCharacter.id` に存在する
  - `MstAutoPlayerSequence.action_value`（SummonEnemyの場合）が `MstEnemyStageParameter.id` に存在する

- [ ] **enum値の正確性**
  - character_unit_kind: Normal、Boss
  - role_type: Technical、Support、Defense、Special、Attack
  - color: Colorless、Red、Blue、Yellow、Green
  - condition_type: InitialSummon、ElapsedTime、OutpostHpPercentage、FriendUnitDead、EnterTargetKomaIndex等
  - action_type: SummonEnemy、SwitchSequenceGroup
  - summon_animation_type: None、Fall0、Fall4
  - aura_type: Default、Boss、AdventBoss1、AdventBoss2、AdventBoss3
  - death_type: Normal、Boss
  - 大文字小文字が正確に一致している

- [ ] **数値の妥当性**
  - HP、攻撃力、移動速度が正の整数である
  - 係数（enemy_hp_coef、enemy_attack_coef、enemy_speed_coef）が適切な範囲内（0.1～100程度）
  - condition_valueが条件タイプに応じた適切な値である

- [ ] **シーケンスの論理整合性**
  - FriendUnitDeadの条件で参照されるsequence_element_idが同じsequence_set_id内に存在する
  - SwitchSequenceGroupで参照されるグループIDが存在する
  - 初期召喚（InitialSummon）が適切に設定されている

### 推奨チェック項目

- [ ] **命名規則の統一**
  - idのプレフィックスがシリーズIDと一致している

- [ ] **I18n設定の完全性**
  - 日本語（ja）が必須で設定されている
  - 他言語（en、zh-CN、zh-TW）も設定されている

- [ ] **ステータスバランス**
  - 敵のHP、攻撃力がステージの難易度に対して適切である
  - ボス敵と通常敵のステータス差が適切である

- [ ] **召喚タイミングの調整**
  - 敵の召喚間隔が適切である（プレイヤーが対応できる範囲）
  - 難易度が段階的に上昇している

## 出力フォーマット

最終的な出力は以下の5シート構成で行います。

### MstEnemyCharacter シート

| ENABLE | id | mst_unit_id | asset_key | release_key |
|--------|----|-----------|---------|---------||
| e | chara_jig_00401 | chara_jig_00401 | chara_jig_00401 | 202601010 |

### MstEnemyCharacterI18n シート

| ENABLE | id | mst_enemy_character_id | locale | enemy_name | release_key |
|--------|----|-----------------------|----|-----------|-------------|
| e | chara_jig_00401_i18n_ja | chara_jig_00401 | ja | 【賊王】亜左 弔兵衛 | 202601010 |

### MstEnemyStageParameter シート

**注**: 19カラムのため詳細は実際のCSVファイルを参照してください。

### MstEnemyOutpost シート

| ENABLE | id | hp | is_damage_invalidation | outpost_asset_key | artwork_asset_key | release_key |
|--------|----|----|------------------------|-------------------|-------------------|-------------|
| e | event_jig1_charaget01_00001 | 30000 | | | event_jig_0001 | 202601010 |

### MstAutoPlayerSequence シート

**注**: 34カラムのため詳細は実際のCSVファイルを参照してください。

## 重要なポイント

- **5テーブル構成**: 敵・自動行動は複数のテーブルに関連します
- **プレイアブルキャラの敵バージョン**: MstEnemyCharacterを経由してMstUnitを参照
- **敵専用キャラ**: MstEnemyCharacterを作成せず、直接MstEnemyStageParameterで定義
- **ステージごとの敵パラメータ**: 同じキャラでもステージによって異なるステータスを設定可能
- **自動行動シーケンスの複雑性**: 34カラムの詳細な設定が必要
- **条件とアクションの組み合わせ**: 時間経過、拠点HP、敵撃破等の条件でアクションを実行
- **グループ切り替え**: ウェーブ制御やフェーズ切り替えにSwitchSequenceGroupを使用
- **係数による調整**: enemy_hp_coef、enemy_attack_coef、enemy_speed_coefで敵の強さを調整
- **外部キー整合性の徹底**: すべてのリレーションが正しく設定されていることを確認

## 参考資料

### 設計書参照先

- **クエスト設計書**: `マスタデータ/運営仕様/<クエスト名>/要件/`
  - MstAutoPlayerSequence.csv
  - エネミー出現.csv
  - ステージ設計▶︎.csv

### DBスキーマ

- **パス**: `projects/glow-server/api/database/schema/exports/master_tables_schema.json`
- **対象テーブル**: MstEnemyCharacter、MstEnemyCharacterI18n、MstEnemyStageParameter、MstEnemyOutpost、MstAutoPlayerSequence

### 実際のマスタデータ（地獄楽）

- **パス**: `domain/raw-data/masterdata/released/202601010/tables/`
  - MstEnemyStageParameter.csv（51レコード）
  - MstEnemyOutpost.csv（23レコード）
  - MstAutoPlayerSequence.csv（186レコード）

### マッピング分析

- **パス**: `domain/tasks/masterdata-entry/create-masterdata-from-biz-ops-specs/analysis/input-output-mapping.md`
  - グループ11: 敵・自動行動セクション参照

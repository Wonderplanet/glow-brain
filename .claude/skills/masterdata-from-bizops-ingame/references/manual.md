# インゲーム（ゲームプレイ設定） マスタデータ設定手順書

## 概要

インゲーム（ゲームプレイ環境）のマスタデータ作成手順を記載します。
本手順書に従うことで、ゲーム内でエラーが発生しない正確なマスタデータを作成できます。

インゲーム設定は、クエスト・PVP・降臨バトルなど、実際のゲームプレイ画面で使用される以下の要素を定義します:
- BGM・背景の設定
- コマ（ステージ）のレイアウトとコマ効果（毒、突風、火傷など）
- 敵の自動行動パターン
- 特別ルール（HP倍率、スピードアタックなど）
- リザルトTips・ステージ情報

## 対象テーブル

インゲームのマスタデータは、以下の7テーブル構成で作成します。

**インゲーム基本情報**:
- **MstInGame** - インゲームの基本設定（BGM、背景、敵パラメータ係数等）
- **MstInGameI18n** - リザルトTips、ステージ説明（多言語対応）

**コマ・ステージ構成**:
- **MstPage** - ページ（ステージ全体）の定義
- **MstKomaLine** - コマライン（コマの行）とコマ効果の詳細設定

**特別ルール**:
- **MstInGameSpecialRule** - 期間限定の特別ルール（スピードアタック、編成制限など）
- **MstInGameSpecialRuleUnitStatus** - 特別ルールでのユニットステータス補正

**演出**:
- **MstMangaAnimation** - ステージ開始・終了時の原画演出

**重要**: 各I18nテーブルは独立したシートとして作成します。

## 作成フロー

### 1. 仕様書の確認

イベントクエスト設計書・PVP設計書・降臨バトル設計書から以下の情報を抽出します。

**必要情報**:
- インゲームID（例: event_jig1_1day_00001、pvp_jig_01、raid_jig1_00001）
- BGM設定（通常BGM、ボスBGM）
- 背景設定（アセットキー）
- コマ構成（段数、各段のコマ数、コマ幅、コマ効果）
- コマ効果（毒、突風、火傷など）の設定
- 敵パラメータ係数（HP倍率、攻撃倍率、スピード倍率）
- 特別ルール（スピードアタック、編成制限、ステータス補正など）
- リザルトTips（敗北時表示テキスト）
- ステージ説明（属性情報、ギミック情報）
- 原画演出（開始時・終了時・敵出現時）

### 2. MstInGame シートの作成

#### 2.1 シートスキーマ

このシートには、ENABLE行とデータ行が含まれます。

**ENABLEと列名行** - カラム名を示します。

```
ENABLE,id,mst_auto_player_sequence_id,mst_auto_player_sequence_set_id,bgm_asset_key,boss_bgm_asset_key,loop_background_asset_key,player_outpost_asset_key,mst_page_id,mst_enemy_outpost_id,mst_defense_target_id,boss_mst_enemy_stage_parameter_id,boss_count,normal_enemy_hp_coef,normal_enemy_attack_coef,normal_enemy_speed_coef,boss_enemy_hp_coef,boss_enemy_attack_coef,boss_enemy_speed_coef,release_key
```

#### 2.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | インゲームの一意識別子。命名規則: `{content_type}_{series_id}{連番}_{ステージ連番5桁}` |
| **mst_auto_player_sequence_id** | 自動行動シーケンスID。MstAutoPlayerSequence.sequence_set_idと対応。PVPでは空欄 |
| **mst_auto_player_sequence_set_id** | 自動行動シーケンスセットID。MstAutoPlayerSequence.sequence_set_idと対応。PVPでは空欄 |
| **bgm_asset_key** | BGMアセットキー。例: `SSE_SBG_003_001`（通常BGM）、`SSE_SBG_003_007`（PVP BGM） |
| **boss_bgm_asset_key** | ボスBGMアセットキー。ボス戦で切り替わるBGM。ボスがいない場合は空欄 |
| **loop_background_asset_key** | 背景アセットキー。通常は空欄（MstKomaLineで指定） |
| **player_outpost_asset_key** | プレイヤータワーアセットキー。通常は空欄 |
| **mst_page_id** | ページID。MstPage.idと対応。インゲームIDと同じ値 |
| **mst_enemy_outpost_id** | 敵拠点ID。MstEnemyOutpost.idと対応。インゲームIDと同じ値（PVPの場合は`pvp`） |
| **mst_defense_target_id** | 防衛対象ID。通常は空欄 |
| **boss_mst_enemy_stage_parameter_id** | ボスの敵ステージパラメータID。MstEnemyStageParameter.idと対応。降臨バトルでは空欄 |
| **boss_count** | ボス出現数。通常は`1`。降臨バトルでは空欄 |
| **normal_enemy_hp_coef** | 通常敵のHP倍率。基本は`1`。特別な調整がある場合は変更 |
| **normal_enemy_attack_coef** | 通常敵の攻撃倍率。基本は`1`。特別な調整がある場合は変更 |
| **normal_enemy_speed_coef** | 通常敵のスピード倍率。基本は`1`。特別な調整がある場合は変更 |
| **boss_enemy_hp_coef** | ボス敵のHP倍率。基本は`1`。特別な調整がある場合は変更 |
| **boss_enemy_attack_coef** | ボス敵の攻撃倍率。基本は`1`。特別な調整がある場合は変更 |
| **boss_enemy_speed_coef** | ボス敵のスピード倍率。基本は`1`。特別な調整がある場合は変更 |
| **release_key** | リリースキー。例: `202601010` |

#### 2.3 ID採番ルール

インゲームのIDは、コンテンツタイプとシリーズIDに基づいて採番します。

```
{content_type}_{series_id}{イベント連番}_{ステージ連番5桁}
```

**パラメータ**:
- `content_type`: コンテンツタイプ
  - `event` = イベントクエスト
  - `pvp` = PVP（ランクマッチ）
  - `raid` = 降臨バトル
- `series_id`: シリーズ識別子（3文字）
  - `jig` = 地獄楽
  - `osh` = 推しの子
  - `kai` = 怪獣8号
- `イベント連番`: イベント内での連番（1~）
- `ステージ連番5桁`: ステージ番号（00001~）

**採番例**:
```
event_jig1_1day_00001        (地獄楽イベント1 デイリークエスト ステージ1)
event_jig1_charaget01_00001  (地獄楽イベント1 ストーリー1 ステージ1)
event_jig1_challenge01_00001 (地獄楽イベント1 チャレンジ1 ステージ1)
raid_jig1_00001              (地獄楽イベント1 降臨バトル)
pvp_jig_01                   (地獄楽 PVP設定1)
pvp_jig_02                   (地獄楽 PVP設定2)
```

#### 2.4 作成例

**イベントクエストの例**:
```
ENABLE,id,mst_auto_player_sequence_id,mst_auto_player_sequence_set_id,bgm_asset_key,boss_bgm_asset_key,loop_background_asset_key,player_outpost_asset_key,mst_page_id,mst_enemy_outpost_id,mst_defense_target_id,boss_mst_enemy_stage_parameter_id,boss_count,normal_enemy_hp_coef,normal_enemy_attack_coef,normal_enemy_speed_coef,boss_enemy_hp_coef,boss_enemy_attack_coef,boss_enemy_speed_coef,release_key
e,event_jig1_1day_00001,event_jig1_1day_00001,event_jig1_1day_00001,SSE_SBG_003_001,,,,event_jig1_1day_00001,event_jig1_1day_00001,,1,,1,1,1,1,1,1,202601010
e,event_jig1_charaget01_00001,event_jig1_charaget01_00001,event_jig1_charaget01_00001,SSE_SBG_003_003,SSE_SBG_003_004,,,event_jig1_charaget01_00001,event_jig1_charaget01_00001,,1,,1,1,1,1,1,1,202601010
```

**PVPの例**:
```
ENABLE,id,mst_auto_player_sequence_id,mst_auto_player_sequence_set_id,bgm_asset_key,boss_bgm_asset_key,loop_background_asset_key,player_outpost_asset_key,mst_page_id,mst_enemy_outpost_id,mst_defense_target_id,boss_mst_enemy_stage_parameter_id,boss_count,normal_enemy_hp_coef,normal_enemy_attack_coef,normal_enemy_speed_coef,boss_enemy_hp_coef,boss_enemy_attack_coef,boss_enemy_speed_coef,release_key
e,pvp_jig_01,,,SSE_SBG_003_007,,,,pvp_jig_01,pvp,,1,,1,1,1,1,1,1,202601010
e,pvp_jig_02,,,SSE_SBG_003_007,,,,pvp_jig_02,pvp,,1,,1,1,1,1,1,1,202601010
```

**降臨バトルの例**:
```
ENABLE,id,mst_auto_player_sequence_id,mst_auto_player_sequence_set_id,bgm_asset_key,boss_bgm_asset_key,loop_background_asset_key,player_outpost_asset_key,mst_page_id,mst_enemy_outpost_id,mst_defense_target_id,boss_mst_enemy_stage_parameter_id,boss_count,normal_enemy_hp_coef,normal_enemy_attack_coef,normal_enemy_speed_coef,boss_enemy_hp_coef,boss_enemy_attack_coef,boss_enemy_speed_coef,release_key
e,raid_jig1_00001,raid_jig1_00001,raid_jig1_00001,SSE_SBG_003_008,,,,raid_jig1_00001,raid_jig1_00001,,,,1,1,1,1,1,1,202601010
```

#### 2.5 BGMアセットキーの設定

地獄楽イベントで使用されているBGMアセットキーの例:

| BGMアセットキー | 用途 |
|--------------|------|
| **SSE_SBG_003_001** | 地獄楽 通常BGM（デイリー、ストーリー） |
| **SSE_SBG_003_003** | 地獄楽 ストーリーBGM（特定クエスト） |
| **SSE_SBG_003_004** | 地獄楽 ボスBGM |
| **SSE_SBG_003_007** | 地獄楽 PVP BGM |
| **SSE_SBG_003_008** | 地獄楽 降臨バトル BGM |
| **SSE_SBG_003_009** | 地獄楽 高難度BGM |

### 3. MstInGameI18n シートの作成

#### 3.1 シートスキーマ

```
ENABLE,release_key,id,mst_in_game_id,language,result_tips,description
```

#### 3.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **release_key** | リリースキー。MstInGameと同じ値 |
| **id** | I18nの一意識別子。命名規則: `{mst_in_game_id}_{language}` |
| **mst_in_game_id** | インゲームID。MstInGame.idと対応 |
| **language** | 言語コード。`ja`: 日本語 |
| **result_tips** | リザルトTips（敗北時表示テキスト）。PVPでは空欄 |
| **description** | ステージ情報。属性情報、ギミック情報、コマ効果情報などを記載 |

#### 3.3 作成例

**イベントクエストの例**:
```
ENABLE,release_key,id,mst_in_game_id,language,result_tips,description
e,202601010,event_jig1_1day_00001_ja,event_jig1_1day_00001,ja,キャラを強化してみよう!,"【属性情報】
無属性の『がらんの画眉丸』が登場するぞ!

毎日1回クエストをクリアして、「プリズム」をGETしよう!"
```

**PVPの例**:
```
ENABLE,release_key,id,mst_in_game_id,language,result_tips,description
e,202601010,pvp_jig_01_ja,pvp_jig_01,ja,,
e,202601010,pvp_jig_02_ja,pvp_jig_02,ja,,
```

**降臨バトルの例**:
```
ENABLE,release_key,id,mst_in_game_id,language,result_tips,description
e,202601010,raid_jig1_00001_ja,raid_jig1_00001,ja,,"このステージは、3段で構成されているぞ!

【ギミック情報】
体力と攻撃の高い強敵が多く登場するぞ!
高ダメージを与えられるキャラを編成しよう!

敵を多く倒して、スコアを稼ごう!"
```

#### 3.4 descriptionの書式ルール

descriptionは以下のセクションで構成されます:

**基本構成**:
```
【属性情報】
赤属性の敵が登場するので青属性のキャラは有利に戦うこともできるぞ!

【コマ効果情報】
突風コマが登場するぞ!
特性で突風コマ無効化を持っているキャラを編成しよう!

【ギミック情報】
毒攻撃をしてくる敵が登場するぞ!
特性で毒ダメージ軽減を持っているキャラを編成しよう!
```

**セクションタイプ**:
- `【属性情報】`: 登場する敵の属性と有利な味方属性を説明
- `【コマ効果情報】`: コマ効果（毒コマ、突風コマ、火傷コマ）の説明
- `【ギミック情報】`: ステージギミック（敵の特殊能力、ファントムゲート、時間経過出現など）の説明

### 4. MstPage シートの作成

#### 4.1 シートスキーマ

```
ENABLE,id,release_key
```

#### 4.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | ページの一意識別子。MstInGame.idと同じ値 |
| **release_key** | リリースキー。MstInGameと同じ値 |

#### 4.3 作成例

```
ENABLE,id,release_key
e,pvp_jig_01,202601010
e,pvp_jig_02,202601010
e,event_jig1_1day_00001,202601010
e,raid_jig1_00001,202601010
e,event_jig1_charaget01_00001,202601010
```

### 5. MstKomaLine シートの作成

#### 5.1 シートスキーマ

MstKomaLineは、コマライン（コマの行）とコマ効果の詳細を定義する複雑なテーブルです。各行に最大4つのコマ（koma1～koma4）を設定できます。

```
ENABLE,id,mst_page_id,row,height,koma_line_layout_asset_key,koma1_asset_key,koma1_width,koma1_back_ground_offset,koma1_effect_type,koma1_effect_parameter1,koma1_effect_parameter2,koma1_effect_target_side,koma1_effect_target_colors,koma1_effect_target_roles,koma2_asset_key,koma2_width,koma2_back_ground_offset,koma2_effect_type,koma2_effect_parameter1,koma2_effect_parameter2,koma2_effect_target_side,koma2_effect_target_colors,koma2_effect_target_roles,koma3_asset_key,koma3_width,koma3_back_ground_offset,koma3_effect_type,koma3_effect_parameter1,koma3_effect_parameter2,koma3_effect_target_side,koma3_effect_target_colors,koma3_effect_target_roles,koma4_asset_key,koma4_width,koma4_back_ground_offset,koma4_effect_type,koma4_effect_parameter1,koma4_effect_parameter2,koma4_effect_target_side,koma4_effect_target_colors,koma4_effect_target_roles,release_key
```

#### 5.2 各カラムの設定ルール

##### 5.2.1 基本設定

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | コマラインの一意識別子。命名規則: `{mst_page_id}_{row}` |
| **mst_page_id** | ページID。MstPage.idと対応 |
| **row** | 行番号。`1`から順番に採番。ステージ下から上への順序 |
| **height** | コマ列の高さ。通常は`0.55`（標準）、`1`（2倍高さ） |
| **koma_line_layout_asset_key** | コマレイアウトアセットキー。レイアウトパターンの番号（1~12） |
| **release_key** | リリースキー。MstInGameと同じ値 |

##### 5.2.2 各コマ（koma1～koma4）の設定

各コマには以下のカラムがあります（koma2～koma4も同様）:

| カラム名 | 設定ルール |
|---------|-----------|
| **komaN_asset_key** | コマアセットキー。背景画像キー（例: `jig_00001`、`jig_00002`、`jig_00003`）。空の場合は`__NULL__` |
| **komaN_width** | コマ幅。`0.25`（1/4幅）、`0.33`（1/3幅）、`0.4`（2/5幅）、`0.5`（1/2幅）、`0.6`（3/5幅）、`0.75`（3/4幅）、`1`（全幅） |
| **komaN_back_ground_offset** | コマ背景オフセット。背景画像のずらし量（`-1`～`1`）。空の場合は`__NULL__` |
| **komaN_effect_type** | コマ効果タイプ。下記の「effect_type設定一覧」を参照 |
| **komaN_effect_parameter1** | コマ効果パラメータ1。効果値（ダメージ量、ノックバック距離など） |
| **komaN_effect_parameter2** | コマ効果パラメータ2。効果持続時間（ミリ秒） |
| **komaN_effect_target_side** | コマ効果影響対象。`All`、`Player`、`Enemy` |
| **komaN_effect_target_colors** | コマ効果影響カラー。通常は`All`。特定色指定の場合は`Red,Blue`のようにカンマ区切り |
| **komaN_effect_target_roles** | コマ効果影響ロール。通常は`All`。特定ロール指定の場合は`Attack,Defense`のようにカンマ区切り |

**重要**: コマが存在しない場合（koma2～koma4が空の場合）:
- `komaN_asset_key`: 空欄または`__NULL__`
- `komaN_width`: 空欄
- `komaN_back_ground_offset`: `__NULL__`
- `komaN_effect_type`: `None`
- `komaN_effect_parameter1`: 空欄
- `komaN_effect_parameter2`: 空欄
- `komaN_effect_target_side`: `__NULL__`
- `komaN_effect_target_colors`: 空欄
- `komaN_effect_target_roles`: 空欄

#### 5.3 effect_type設定一覧

| effect_type | 説明 | parameter1 | parameter2 |
|-------------|------|-----------|-----------|
| **None** | 効果なし | `0` | `0` |
| **Poison** | 毒ダメージ | ダメージ量 | 持続時間（ミリ秒） |
| **Gust** | 突風（ノックバック） | ノックバック力 | ノックバック距離 |
| **Burn** | 火傷ダメージ | ダメージ量 | 持続時間（ミリ秒） |

**毒・火傷の設定例**:
- `Poison,150,5`: 毒ダメージ150、持続時間5ミリ秒
- `Poison,200,5`: 毒ダメージ200、持続時間5ミリ秒
- `Burn,300,1000`: 火傷ダメージ300、持続時間1000ミリ秒
- `Burn,600,1500`: 火傷ダメージ600、持続時間1500ミリ秒

**突風の設定例**:
- `Gust,250,0.3`: ノックバック力250、距離0.3
- `Gust,500,0.2`: ノックバック力500、距離0.2
- `Gust,1000,0.5`: ノックバック力1000、距離0.5
- `Gust,1500,0.3`: ノックバック力1500、距離0.3

#### 5.4 koma_line_layout_asset_key設定一覧

コマレイアウトのパターン番号:

| レイアウト番号 | コマ構成 | 用途 |
|------------|---------|------|
| **1** | 全幅1コマ | シンプルな1コマ構成 |
| **2** | 6:4分割 | 左大きめ、右小さめの2コマ |
| **3** | 4:6分割 | 左小さめ、右大きめの2コマ |
| **4** | 3:1分割 | 左大きめ、右極小の2コマ |
| **5** | 1:3分割 | 左極小、右大きめの2コマ |
| **6** | 1:1分割 | 均等な2コマ |
| **7** | 1:1:1分割（3コマ） | 均等な3コマ |
| **8** | 1:1:1分割（3コマ、上段寄せ） | 均等な3コマ（位置調整版） |
| **9** | 1:2:1分割（3コマ） | 中央大きめの3コマ |
| **10** | 1:1:2分割（3コマ） | 右大きめの3コマ |
| **12** | 1:1:1:1分割（4コマ） | 均等な4コマ |

#### 5.5 背景アセットキー設定一覧

地獄楽イベントで使用されている背景アセットキー:

| アセットキー | 説明 |
|-----------|------|
| **jig_00001** | 地獄楽 背景1（通常） |
| **jig_00002** | 地獄楽 背景2（ストーリー1） |
| **jig_00003** | 地獄楽 背景3（ストーリー2、チャレンジ、高難度） |

#### 5.6 作成例

**2段構成・2コマの例（デイリークエスト）**:
```
ENABLE,id,mst_page_id,row,height,koma_line_layout_asset_key,koma1_asset_key,koma1_width,koma1_back_ground_offset,koma1_effect_type,koma1_effect_parameter1,koma1_effect_parameter2,koma1_effect_target_side,koma1_effect_target_colors,koma1_effect_target_roles,koma2_asset_key,koma2_width,koma2_back_ground_offset,koma2_effect_type,koma2_effect_parameter1,koma2_effect_parameter2,koma2_effect_target_side,koma2_effect_target_colors,koma2_effect_target_roles,koma3_asset_key,koma3_width,koma3_back_ground_offset,koma3_effect_type,koma3_effect_parameter1,koma3_effect_parameter2,koma3_effect_target_side,koma3_effect_target_colors,koma3_effect_target_roles,koma4_asset_key,koma4_width,koma4_back_ground_offset,koma4_effect_type,koma4_effect_parameter1,koma4_effect_parameter2,koma4_effect_target_side,koma4_effect_target_colors,koma4_effect_target_roles,release_key
e,event_jig1_1day_00001_1,event_jig1_1day_00001,1,0.55,2,jig_00001,0.6,0.3,None,0,0,All,All,All,jig_00001,0.4,0.3,None,0,0,All,All,All,,,__NULL__,None,,,__NULL__,,,,,__NULL__,None,,,__NULL__,,,202601010
e,event_jig1_1day_00001_2,event_jig1_1day_00001,2,0.55,3,jig_00001,0.4,0.3,None,0,0,All,All,All,jig_00001,0.6,0.3,None,0,0,All,All,All,,,__NULL__,None,,,__NULL__,,,,,__NULL__,None,,,__NULL__,,,202601010
```

**3段構成・コマ効果あり（降臨バトル）**:
```
e,raid_jig1_00001_1,raid_jig1_00001,1,0.55,3,jig_00001,0.4,-1,None,0,0,All,All,All,jig_00001,0.6,-1,None,0,0,All,All,All,,,__NULL__,None,,,__NULL__,,,,,__NULL__,None,,,__NULL__,,,202601010
e,raid_jig1_00001_2,raid_jig1_00001,2,0.55,1,jig_00001,1,-0.4,None,0,0,All,All,All,,,__NULL__,None,0,0,All,All,All,,,__NULL__,None,,,__NULL__,,,,,__NULL__,None,,,__NULL__,,,202601010
e,raid_jig1_00001_3,raid_jig1_00001,3,0.55,3,jig_00001,0.4,0.7,None,0,0,All,All,All,jig_00001,0.6,0.7,None,0,0,All,All,All,,,__NULL__,None,,,__NULL__,,,,,__NULL__,None,,,__NULL__,,,202601010
```

**PVP・毒コマと突風コマの例**:
```
e,pvp_jig_01_1,pvp_jig_01,1,0.55,5,jig_00001,0.25,-1,None,0,0,All,All,All,jig_00001,0.75,-1,Poison,150,5,All,All,All,,,__NULL__,None,,,__NULL__,,,,,__NULL__,None,,,__NULL__,,,202601010
e,pvp_jig_01_2,pvp_jig_01,2,0.55,7,jig_00001,0.33,-1,None,0,0,All,All,All,jig_00001,0.34,-1,None,0,0,All,All,All,jig_00001,0.33,-1,None,0,0,All,All,All,,,__NULL__,None,,,__NULL__,,,202601010
e,pvp_jig_01_3,pvp_jig_01,3,0.55,4,jig_00001,0.75,-1,Poison,150,5,All,All,All,jig_00001,0.25,-1,None,0,0,All,All,All,,,__NULL__,None,,,__NULL__,,,,,__NULL__,None,,,__NULL__,,,202601010
```

**突風コマ（プレイヤーのみ影響）の例**:
```
e,pvp_jig_02_1,pvp_jig_02,1,0.55,1,jig_00001,1,-1,Gust,250,0.3,Player,All,All,,,__NULL__,None,,,__NULL__,,,,,__NULL__,None,,,__NULL__,,,,,__NULL__,None,,,__NULL__,,,202601010
```

**4コマ構成の例**:
```
e,event_jig1_charaget01_00006_3,event_jig1_charaget01_00006,3,0.55,12,jig_00003,0.25,0.3,Gust,500,0.2,Player,All,All,jig_00003,0.25,0.3,None,0,0,All,All,All,jig_00003,0.25,0.3,None,0,0,All,All,All,jig_00003,0.25,0.6,None,0,0,All,All,All,202601010
```

**火傷コマの例**:
```
e,event_jig1_savage_00003_2,event_jig1_savage_00003,2,0.55,6,jig_00002,0.5,-0.6,None,0,0,All,All,All,jig_00002,0.5,-0.6,Burn,600,1500,Player,All,All,,,__NULL__,None,,,__NULL__,,,,,__NULL__,None,,,__NULL__,,,202601010
```

#### 5.7 コマ構成のポイント

- **段数**: 通常は2~3段構成。PVPや降臨バトルは3段が多い
- **コマ数**: 1行に1~4コマ。シンプルな構成は2コマが多い
- **コマ幅**: 合計が`1`（100%）になるように設定。例: `0.6 + 0.4 = 1`、`0.33 + 0.34 + 0.33 = 1`
- **コマ効果**: 毒、突風、火傷を組み合わせて難易度を調整
- **影響対象**: `Player`（プレイヤー側のみ）、`Enemy`（敵側のみ）、`All`（両方）
- **背景オフセット**: 背景画像のスクロール位置を調整（`-1`～`1`）

### 6. MstInGameSpecialRule シートの作成

特別ルールは、ステージに期間限定の特殊条件を設定します（スピードアタック、コンティニュー禁止、編成制限、ステータス補正など）。

#### 6.1 シートスキーマ

```
ENABLE,id,content_type,target_id,rule_type,rule_value,start_at,end_at,release_key
```

#### 6.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | 特別ルールの一意識別子。命名規則: `{target_id}_{連番}` または `{ルール名}` |
| **content_type** | コンテンツタイプ。`Stage`、`AdventBattle`、`Pvp` |
| **target_id** | 対象ID。ステージID、降臨バトルID、PVP IDなど |
| **rule_type** | ルールタイプ。下記の「rule_type設定一覧」を参照 |
| **rule_value** | ルール値。rule_typeに応じた設定値。`1`（有効）、シリーズIDなど |
| **start_at** | 開始日時。例: `2026-01-16 15:00:00` |
| **end_at** | 終了日時。例: `2026-02-16 10:59:59` |
| **release_key** | リリースキー。MstInGameと同じ値 |

#### 6.3 rule_type設定一覧

| rule_type | 説明 | rule_value |
|----------|------|-----------|
| **SpeedAttack** | スピードアタックルール。早くクリアすると報酬獲得 | `1000`（ミリ秒単位の判定時間） |
| **NoContinue** | コンティニュー禁止 | `1`（有効） |
| **PartySeries** | 編成シリーズ制限。特定シリーズのキャラのみ編成可能 | シリーズID（例: `jig`、`osh`、`kai`） |
| **UnitStatus** | ユニットステータス補正。MstInGameSpecialRuleUnitStatusと連携 | グループID（例: `pvp_jig_specialrule_001_1`） |

#### 6.4 作成例

**スピードアタック + コンティニュー禁止の例**:
```
ENABLE,id,content_type,target_id,rule_type,rule_value,start_at,end_at,release_key
e,event_jig1_challenge01_00001_1,Stage,event_jig1_challenge01_00001,SpeedAttack,1000,"2026-01-16 15:00:00","2026-02-16 10:59:59",202601010
e,event_jig1_challenge01_00001_2,Stage,event_jig1_challenge01_00001,NoContinue,1,"2026-01-16 15:00:00","2026-02-16 10:59:59",202601010
```

**編成シリーズ制限の例（複数シリーズ）**:
```
e,event_jig1_savage_00003_3,Stage,event_jig1_savage_00003,PartySeries,jig,"2026-01-16 15:00:00","2026-02-16 10:59:59",202601010
e,event_jig1_savage_00003_4,Stage,event_jig1_savage_00003,PartySeries,osh,"2026-01-16 15:00:00","2026-02-16 10:59:59",202601010
e,event_jig1_savage_00003_5,Stage,event_jig1_savage_00003,PartySeries,sur,"2026-01-16 15:00:00","2026-02-16 10:59:59",202601010
e,event_jig1_savage_00003_6,Stage,event_jig1_savage_00003,PartySeries,kai,"2026-01-16 15:00:00","2026-02-16 10:59:59",202601010
```

**PVPステータス補正の例**:
```
e,pvp_jig_specialrule_001,Pvp,2026004,UnitStatus,pvp_jig_specialrule_001_1,"2026-01-01 12:00:00","2026-02-28 23:59:59",202601010
e,pvp_jig_specialrule_002,Pvp,2026005,UnitStatus,pvp_jig_specialrule_001_1,"2026-01-01 12:00:00","2026-02-28 23:59:59",202601010
```

#### 6.5 特別ルール設定のポイント

- **SpeedAttack**: チャレンジクエストや高難度クエストで使用。クリア時間報酬を設定する場合に必須
- **NoContinue**: 高難度クエストで使用。コンティニュー禁止により難易度を上げる
- **PartySeries**: 特定シリーズのキャラのみ編成可能にする。複数シリーズを許可する場合は複数行作成
- **UnitStatus**: PVPで使用。全ユニットのHP倍率などを調整

### 7. MstInGameSpecialRuleUnitStatus シートの作成

ユニットステータス補正の詳細を定義します。MstInGameSpecialRule（rule_type=UnitStatus）と連携して使用します。

#### 7.1 シートスキーマ

```
ENABLE,release_key,id,group_id,target_type,target_value,status_parameter_type,effect_value
```

#### 7.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **release_key** | リリースキー。MstInGameと同じ値 |
| **id** | 連番。`1`から順番に採番 |
| **group_id** | グループID。MstInGameSpecialRule.rule_valueと対応 |
| **target_type** | 対象タイプ。`All`（全ユニット）、特定ユニットID、属性、ロールなど |
| **target_value** | 対象値。target_typeに応じた設定値。`All`の場合は空欄 |
| **status_parameter_type** | ステータスパラメータタイプ。`Hp`（HP倍率）、`Attack`（攻撃倍率）など |
| **effect_value** | 効果値。倍率（%）。例: `200`（200%=2倍）、`300`（300%=3倍） |

#### 7.3 作成例

**PVPで全ユニットのHP 200%UP（2倍）の例**:
```
ENABLE,release_key,id,group_id,target_type,target_value,status_parameter_type,effect_value
e,202601010,4,pvp_jig_specialrule_001_1,All,,Hp,200
```

#### 7.4 ステータス補正のポイント

- **PVPでの使用**: ランクマッチなどで、全キャラのHP倍率を上げてバランス調整
- **倍率の指定**: `200` = 2倍、`300` = 3倍。通常は100%（等倍）からの増減
- **対象指定**: `All`（全ユニット）が一般的。特定キャラのみ補正する場合はユニットIDを指定

### 8. MstMangaAnimation シートの作成

ステージ開始・終了時、または特定条件での原画演出を定義します。

#### 8.1 シートスキーマ

```
ENABLE,id,mst_stage_id,condition_type,condition_value,animation_start_delay,animation_speed,is_pause,can_skip,asset_key,release_key
```

#### 8.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | 原画演出の一意識別子。命名規則: `genga_{series_id}_{クエストタイプ}_{連番2桁}_{条件}` |
| **mst_stage_id** | ステージID。MstStage.idと対応 |
| **condition_type** | 演出条件タイプ。`Start`（開始時）、`Victory`（勝利時）、`EnemySummon`（敵出現時） |
| **condition_value** | 演出条件値。ステージ番号または敵出現回数 |
| **animation_start_delay** | 演出開始遅延（秒）。通常は`0` |
| **animation_speed** | 演出速度。通常は`0.7` |
| **is_pause** | 演出中にゲームを一時停止するか。`1`: する、`0`: しない |
| **can_skip** | 演出をスキップ可能か。`1`: 可能、`0`: 不可 |
| **asset_key** | アセットキー。原画演出のアセット |
| **release_key** | リリースキー。MstInGameと同じ値 |

#### 8.3 ID採番ルール

```
genga_{series_id}_{クエストタイプ}_{連番2桁}_{条件}
```

**パラメータ**:
- `series_id`: シリーズ識別子（例: `jig`）
- `クエストタイプ`: `event_1day`（デイリー）、`event_story`（ストーリー）、`event_challenge`（チャレンジ）、`event_savage`（高難度）
- `連番2桁`: 01から順番に採番
- `条件`: `start`（開始時）、`victory`（勝利時）、`appear`（敵出現時）

#### 8.4 作成例

**デイリークエスト開始時の例**:
```
ENABLE,id,mst_stage_id,condition_type,condition_value,animation_start_delay,animation_speed,is_pause,can_skip,asset_key,release_key
e,genga_jig_event_1day_00001_01_start,event_jig1_1day_00001,Start,1,0,0.7,1,1,genga_jig_event_1day_00001_01_start,202601010
```

**ストーリークエスト開始時・勝利時の例**:
```
e,genga_jig_event_story_00001_01_start,event_jig1_charaget01_00001,Start,1,0,0.7,1,1,genga_jig_event_story_00001_01_start,202601010
e,genga_jig_event_story_00001_03_victory,event_jig1_charaget01_00003,Victory,3,0,0.7,1,1,genga_jig_event_story_00001_03_victory,202601010
```

**チャレンジクエスト敵出現時の例**:
```
e,genga_jig_event_challenge_00001_01_appear,event_jig1_challenge01_00001,EnemySummon,1,0,0.7,1,1,genga_jig_event_challenge_00001_01_appear,202601010
e,genga_jig_event_challenge_00002_02_appear,event_jig1_challenge01_00002,EnemySummon,1,0,0.7,1,1,genga_jig_event_challenge_00002_02_appear,202601010
```

**高難度クエスト敵出現時の例**:
```
e,genga_jig_event_savage_00001_01_appear,event_jig1_savage_00001,EnemySummon,1,0,0.7,1,1,genga_jig_event_savage_00001_01_appear,202601010
e,genga_jig_event_savage_00003_03_appear,event_jig1_savage_00003,EnemySummon,2,0,0.7,1,1,genga_jig_event_savage_00003_03_appear,202601010
```

#### 8.5 原画演出設定のポイント

- **開始時演出**: ストーリークエストの各ステージで使用。ストーリー展開を表現
- **勝利時演出**: ストーリークエストの区切りの良いステージで使用。ストーリーの結末を表現
- **敵出現時演出**: チャレンジクエスト・高難度クエストで使用。ボスや強敵の登場を演出
- **is_pause**: 通常は`1`（一時停止する）。演出中にゲームが進行しないようにする
- **can_skip**: 通常は`1`（スキップ可能）。プレイヤーが演出をスキップできるようにする

## データ整合性のチェック

マスタデータ作成後、以下の項目を確認してください。

### 必須チェック項目

- [ ] **ヘッダーの列順が正しいか**
  - スキーマファイルと完全一致している

- [ ] **IDの一意性**
  - すべてのidが一意である
  - 他のリリースのidと重複していない

- [ ] **ID採番ルール**
  - MstInGame.id: `{content_type}_{series_id}{連番}_{ステージ連番5桁}`
  - MstInGameI18n.id: `{mst_in_game_id}_{language}`
  - MstPage.id: MstInGame.idと同じ値
  - MstKomaLine.id: `{mst_page_id}_{row}`
  - MstInGameSpecialRule.id: `{target_id}_{連番}` または `{ルール名}`
  - MstMangaAnimation.id: `genga_{series_id}_{クエストタイプ}_{連番2桁}_{条件}`

- [ ] **リレーションの整合性**
  - `MstInGame.mst_page_id` が `MstPage.id` に存在する
  - `MstInGame.mst_enemy_outpost_id` が `MstEnemyOutpost.id` に存在する
  - `MstInGame.mst_auto_player_sequence_set_id` が `MstAutoPlayerSequence.sequence_set_id` に存在する（イベントクエストの場合）
  - `MstKomaLine.mst_page_id` が `MstPage.id` に存在する
  - `MstInGameSpecialRule.target_id` が該当するステージID・降臨バトルID・PVP IDに存在する
  - `MstInGameSpecialRuleUnitStatus.group_id` が `MstInGameSpecialRule.rule_value` に存在する
  - `MstMangaAnimation.mst_stage_id` が `MstStage.id` に存在する
  - すべてのI18nテーブルの親IDが存在する

- [ ] **コマライン設定の完全性**
  - 各ページに少なくとも1つのコマラインが存在する
  - 各コマラインの行番号が1から連番で設定されている
  - コマ幅の合計が`1`（100%）になっている
  - 空のコマは正しく`__NULL__`や空欄で設定されている

- [ ] **enum値の正確性**
  - content_type: Stage、AdventBattle、Pvp
  - effect_type: None、Poison、Gust、Burn
  - effect_target_side: All、Player、Enemy
  - condition_type: Start、Victory、EnemySummon
  - 大文字小文字が正確に一致している

- [ ] **数値の妥当性**
  - 敵パラメータ係数（normal_enemy_hp_coef等）が正の数である
  - コマ幅（koma_width）の合計が`1`である
  - コマ効果パラメータが適切な範囲内である
  - ステータス補正効果値（effect_value）が妥当である

- [ ] **日付時刻の整合性**
  - MstInGameSpecialRuleのstart_atとend_atが正しい期間である
  - イベント期間内に収まっている

### 推奨チェック項目

- [ ] **命名規則の統一**
  - idのプレフィックスがシリーズIDと一致している
  - BGMアセットキーが適切に選択されている
  - 背景アセットキーが適切に選択されている

- [ ] **I18n設定の完全性**
  - 日本語（ja）が必須で設定されている
  - result_tipsとdescriptionが適切に記載されている

- [ ] **コマ効果の妥当性**
  - 毒・突風・火傷のパラメータが適切な範囲内
  - コマ効果の影響対象（Player、Enemy、All）が意図通り設定されている

- [ ] **特別ルールの設定**
  - スピードアタックとコンティニュー禁止が併用されているか
  - 編成制限が適切に設定されているか
  - ステータス補正が適切に設定されているか

- [ ] **原画演出の設定**
  - ストーリークエストに開始時・勝利時演出が設定されているか
  - チャレンジクエスト・高難度クエストに敵出現時演出が設定されているか

## 出力フォーマット

最終的な出力は以下の7シート構成で行います。

### MstInGame シート

| ENABLE | id | mst_auto_player_sequence_id | mst_auto_player_sequence_set_id | bgm_asset_key | boss_bgm_asset_key | loop_background_asset_key | player_outpost_asset_key | mst_page_id | mst_enemy_outpost_id | mst_defense_target_id | boss_mst_enemy_stage_parameter_id | boss_count | normal_enemy_hp_coef | normal_enemy_attack_coef | normal_enemy_speed_coef | boss_enemy_hp_coef | boss_enemy_attack_coef | boss_enemy_speed_coef | release_key |
|--------|----|-----------------------------|--------------------------------|---------------|-------------------|--------------------------|-------------------------|-------------|---------------------|-----------------------|----------------------------------|-----------|---------------------|-------------------------|------------------------|------------------|----------------------|---------------------|-------------|
| e | event_jig1_1day_00001 | event_jig1_1day_00001 | event_jig1_1day_00001 | SSE_SBG_003_001 | | | | event_jig1_1day_00001 | event_jig1_1day_00001 | | 1 | | 1 | 1 | 1 | 1 | 1 | 1 | 202601010 |

### MstInGameI18n シート

| ENABLE | release_key | id | mst_in_game_id | language | result_tips | description |
|--------|-------------|----|----------------|----------|-------------|-------------|
| e | 202601010 | event_jig1_1day_00001_ja | event_jig1_1day_00001 | ja | キャラを強化してみよう! | 【属性情報】<br>無属性の『がらんの画眉丸』が登場するぞ!<br><br>毎日1回クエストをクリアして、「プリズム」をGETしよう! |

### MstPage シート

| ENABLE | id | release_key |
|--------|----|---------||
| e | event_jig1_1day_00001 | 202601010 |

### MstKomaLine シート

**注**: 41カラムのため詳細は省略。設計書に従って設定してください。

### MstInGameSpecialRule シート

| ENABLE | id | content_type | target_id | rule_type | rule_value | start_at | end_at | release_key |
|--------|----|--------------|-----------|-----------|-----------||--------|--------|-------------|
| e | event_jig1_challenge01_00001_1 | Stage | event_jig1_challenge01_00001 | SpeedAttack | 1000 | 2026-01-16 15:00:00 | 2026-02-16 10:59:59 | 202601010 |

### MstInGameSpecialRuleUnitStatus シート

| ENABLE | release_key | id | group_id | target_type | target_value | status_parameter_type | effect_value |
|--------|-------------|----|-----------|-----------|--------------|-----------------------|--------------|
| e | 202601010 | 4 | pvp_jig_specialrule_001_1 | All | | Hp | 200 |

### MstMangaAnimation シート

| ENABLE | id | mst_stage_id | condition_type | condition_value | animation_start_delay | animation_speed | is_pause | can_skip | asset_key | release_key |
|--------|----|--------------|----------------|----------------|----------------------|----------------|---------|---------|-----------|-------------|
| e | genga_jig_event_1day_00001_01_start | event_jig1_1day_00001 | Start | 1 | 0 | 0.7 | 1 | 1 | genga_jig_event_1day_00001_01_start | 202601010 |

## 重要なポイント

- **7テーブル構成**: インゲームは複数のテーブルで構成され、それぞれが独立した役割を持つ
- **コマライン設定の複雑性**: 最大41カラムの設定が必要（各コマ×4）。設計書に従って慎重に設定
- **コマ効果の多様性**: 毒、突風、火傷などのコマ効果を組み合わせて難易度を調整
- **特別ルールの柔軟性**: スピードアタック、編成制限、ステータス補正などを組み合わせ可能
- **原画演出の条件**: 開始時・勝利時・敵出現時など、条件に応じた演出を設定
- **コンテンツタイプの違い**: イベントクエスト、PVP、降臨バトルで設定が異なる
- **外部キー整合性の徹底**: すべてのリレーションが正しく設定されていることを確認
- **__NULL__の使い方**: 空のコマや空欄のカラムは`__NULL__`または空欄で設定

# MstInGame 詳細説明

> CSVパス: `projects/glow-masterdata/MstInGame.csv`
> i18nテーブル: `projects/glow-masterdata/MstInGameI18n.csv`（多言語設定）

---

## 概要

MstInGame は**バトルステージ1つ分のインゲーム設定を管理する中心テーブル**。

ステージに紐づく BGM・背景・タワーアセットの指定、敵タワー・防衛オブジェクトの参照、オートプレイヤーシーケンスの参照、そして通常敵・ボス敵のステータス倍率（HP・攻撃・スピード）を定義する。

GLOWのインゲームは「敵が自タワーに向かって進軍し、プレイヤーがユニットを召喚して迎え撃つ」タワーディフェンス形式。`MstInGame` はステージ全体の「器」であり、各コンテンツ（ノーマル/ハード/ベリーハード/PvP/レイド/イベント/チュートリアル）それぞれに1レコードが対応する。

```
MstStageDetail（または相当する上位テーブル）
  └─ mst_in_game_id → MstInGame.id
        ├─ mst_page_id                        → MstPage（ステージページ設定）
        ├─ mst_enemy_outpost_id               → MstEnemyOutpost（敵タワー設定）
        ├─ mst_defense_target_id              → MstDefenseTarget（防衛オブジェクト設定、任意）
        ├─ boss_mst_enemy_stage_parameter_id  → MstEnemyStageParameter（ボス敵パラメータ）
        ├─ mst_auto_player_sequence_id        → MstAutoPlayerSequence（敵出現シーケンス）
        └─ mst_auto_player_sequence_set_id    → MstAutoPlayerSequence.sequence_set_id
```

---

## 全カラム一覧

### mst_in_games（本体テーブル）

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | string | - | - | CSVの有効フラグ。`e` = 有効（常に `e` のみ使用） |
| `id` | varchar(255) | 不可 | - | ステージID（主キー）。命名規則は後述 |
| `release_key` | bigint | 不可 | 1 | リリースキー。リリース管理に使用 |
| `mst_auto_player_sequence_id` | varchar(255) | 不可 | "" | 敵出現シーケンスの参照ID（リレーション向け）。PvP系は空文字 |
| `mst_auto_player_sequence_set_id` | varchar(255) | 不可 | - | MstAutoPlayerSequence の sequence_set_id。PvP系は空文字 |
| `bgm_asset_key` | varchar(255) | 不可 | "" | 通常時BGMのアセットキー |
| `boss_bgm_asset_key` | varchar(255) | 不可 | "" | ボス出現時BGMのアセットキー。ボスが存在しないステージは空文字 |
| `loop_background_asset_key` | varchar(255) | 不可 | "" | 背景アセットキー。空文字の場合はデフォルト背景が適用される |
| `player_outpost_asset_key` | varchar(255) | 不可 | "" | プレイヤータワーのアセットキー。現状は空文字（デフォルトアセット使用） |
| `mst_page_id` | varchar(255) | 不可 | "" | ステージページ設定の参照ID（MstPage） |
| `mst_enemy_outpost_id` | varchar(255) | 不可 | "" | 敵タワー設定の参照ID（MstEnemyOutpost） |
| `mst_defense_target_id` | varchar(255) | 許容 | NULL | 防衛オブジェクト設定の参照ID（MstDefenseTarget）。防衛オブジェクトが不要なステージはNULL |
| `boss_mst_enemy_stage_parameter_id` | varchar(255) | 不可 | "" | ボス敵パラメータの参照ID（MstEnemyStageParameter）。ボスが存在しないレイド等は空文字 |
| `boss_count` | int | 許容 | NULL | ボスの出現数。現状すべてNULL（未使用） |
| `normal_enemy_hp_coef` | decimal(10,2) | 不可 | - | 通常敵のHP倍率。基準値は 1.0 |
| `normal_enemy_attack_coef` | decimal(10,2) | 不可 | - | 通常敵の攻撃力倍率。基準値は 1.0 |
| `normal_enemy_speed_coef` | decimal(10,2) | 不可 | - | 通常敵の移動速度倍率。基準値は 1 |
| `boss_enemy_hp_coef` | decimal(10,2) | 不可 | - | ボス敵のHP倍率。基準値は 1.0 |
| `boss_enemy_attack_coef` | decimal(10,2) | 不可 | - | ボス敵の攻撃力倍率。基準値は 1.0 |
| `boss_enemy_speed_coef` | decimal(10,2) | 不可 | - | ボス敵の移動速度倍率。基準値は 1 |

### mst_in_games_i18n（多言語テーブル）

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `id` | varchar(255) | 不可 | - | i18nレコードID（主キー） |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `mst_in_game_id` | varchar(255) | 不可 | - | 対応する MstInGame.id（外部キー） |
| `language` | enum('ja') | 不可 | - | 言語設定（現状 `ja` のみ） |
| `result_tips` | varchar(255) | 不可 | "" | 敗北時に表示するTipsテキスト |
| `description` | text | 不可 | - | ステージ説明文（ステージ情報画面等で使用） |

---

## 主要なenum / フラグの解説

### language（MstInGameI18n）

| 値 | 説明 |
|----|------|
| `ja` | 日本語（現在唯一の対応言語） |

### ステータス倍率カラム（*_coef）

倍率カラムは MstEnemyStageParameter で定義された基本値に乗算して適用される。

| 値の例 | 意味 |
|--------|------|
| `1.0` | 基本値をそのまま使用（標準） |
| `2.0` | 基本値の2倍（例: ハード難易度のHP/攻撃倍率） |
| `3.0` | 基本値の3倍（例: ハードのchiステージ攻撃倍率） |
| `100.0` | 超高倍率（開発用テストステージで使用） |
| `0.01` | 攻撃力を事実上無力化（開発用テストステージで使用） |

---

## 命名規則 / IDの生成ルール

### 基本パターン

```
{難易度またはコンテンツ種別}_{キャラクター/モード識別子}_{連番}
```

### コンテンツ別パターン

| コンテンツ種別 | IDパターン例 | 件数 |
|-----------|------------|------|
| ノーマルステージ | `normal_{キャラ}_{連番5桁}` | 78件 |
| ハードステージ | `hard_{キャラ}_{連番5桁}` | 78件 |
| ベリーハードステージ | `veryhard_{キャラ}_{連番5桁}` | 78件 |
| PvP | `pvp_{識別子}_{連番}` または `default_pvp` | 21件 |
| レイドイベント | `raid_{キャラ}_{連番5桁}` | 11件 |
| イベントステージ | `event_{キャラ識別子}_{種別}_{連番5桁}` | ~200件 |
| チュートリアル | `tutorial`, `tutorial_2`, `tutorial_3` | 3件 |
| 開発用テスト | `develop_001`, `develop_002` | 4件 |
| 企画テスト | `plan_test_{内容}` | 18件 |

### キャラクター識別子（主要なもの）

| 識別子 | 対応キャラクター |
|--------|---------------|
| `spy` | スパイ系キャラ |
| `dan` | ダン系キャラ |
| `mag` | マグ系キャラ |
| `kai` | カイ系キャラ |
| `you` | ユウ系キャラ |
| `yuw` | ユウ系（別表記） |
| `sur` | サー系キャラ |
| `hut` | ハット系キャラ |
| `kim` | キム系キャラ |
| `jig` | ジグ系キャラ |
| `osh` | オシ系キャラ |
| `chi` | チ系キャラ（防衛オブジェクトあり） |
| `glo` | グロー系（コラボ/特殊） |

### 連番ルール

- 通常は5桁ゼロ埋め（`00001`, `00002`, ...）
- 同一キャラ・コンテンツ内で通し番号を付与
- 新規ステージ追加時は既存の最大番号＋1

---

## 他テーブルとの連携

```
MstInGame
├─ mst_page_id
│    └─→ MstPage（mst_pages）
│         「インゲーム内のステージ設定」。ページレイアウト・UI設定を管理
│
├─ mst_enemy_outpost_id
│    └─→ MstEnemyOutpost（mst_enemy_outposts）
│         「敵タワーの設定」。HP・アセットキー・アートワークを管理
│
├─ mst_defense_target_id [NULLABLE]
│    └─→ MstDefenseTarget（mst_defense_targets）
│         「インゲーム向け防衛オブジェクトの設定」。
│         プレイヤーが守るべきオブジェクト（例: nyako）のHP・位置・アセットを管理
│         設定されているステージ: chi系ステージ（normal_chi_00004, hard_chi_00004）
│
├─ boss_mst_enemy_stage_parameter_id
│    └─→ MstEnemyStageParameter（mst_enemy_stage_parameters）
│         「インゲーム向け敵ユニットの設定」。ボス敵の基礎パラメータを管理
│         レイドステージでは空文字（ボス不在）
│
├─ mst_auto_player_sequence_id
│    └─→ MstAutoPlayerSequence（mst_auto_player_sequences）
│         「インゲーム内敵出現ルール」。条件・タイプ・出現位置を管理
│         PvP系では空文字（シーケンス不使用）
│
└─ mst_auto_player_sequence_set_id
     └─→ MstAutoPlayerSequence.sequence_set_id
          同一ステージに属する複数のシーケンス行をセット単位でグルーピングするキー
```

### 逆参照（MstInGameを参照するテーブル）

MstInGame.id は上位のステージ管理テーブル（ステージ詳細・クエスト設定など）から参照される。また `MstInGameI18n.mst_in_game_id` で多言語テキストと1対1に紐づく。

---

## 実データ例

### パターン1: 通常ステージ（normal / hard / veryhard）

| id | bgm_asset_key | loop_background_asset_key | mst_page_id | mst_enemy_outpost_id | normal_enemy_hp_coef | normal_enemy_attack_coef | release_key |
|----|---------------|--------------------------|-------------|---------------------|---------------------|------------------------|-------------|
| `normal_spy_00001` | SSE_SBG_003_002 | spy_00005 | normal_spy_00001 | normal_spy_00001 | 1.0 | 1.0 | 202509010 |
| `hard_chi_00001` | SSE_SBG_003_001 | （空） | hard_chi_00001 | hard_chi_00001 | 2.0 | 2.0 | 202509010 |
| `veryhard_dan_00001` | SSE_SBG_003_001 | （空） | veryhard_dan_00001 | veryhard_dan_00001 | 1.0 | 1.0 | 202509010 |

- hard 系は `normal_enemy_hp_coef` と `normal_enemy_attack_coef` を 2.0 に設定するのが基本
- `loop_background_asset_key` は normal には設定されることが多いが、hard/veryhard では省略されることが多い

### パターン2: PvPステージ

| id | bgm_asset_key | mst_page_id | mst_enemy_outpost_id | mst_auto_player_sequence_id | release_key |
|----|---------------|-------------|---------------------|---------------------------|-------------|
| `pvp_202509010_01` | SSE_SBG_003_007 | pvp_202509010_01 | pvp | （空） | 202509010 |
| `default_pvp` | SSE_SBG_003_007 | default_pvp | pvp | （空） | 202509010 |

- `mst_auto_player_sequence_id` と `mst_auto_player_sequence_set_id` は空文字（PvPはシーケンス不使用）
- `mst_enemy_outpost_id` は共通で `pvp` を使用

### パターン3: レイドステージ（ボスBGMあり）

| id | bgm_asset_key | boss_bgm_asset_key | mst_page_id | mst_enemy_outpost_id | boss_mst_enemy_stage_parameter_id | release_key |
|----|---------------|--------------------|-------------|---------------------|----------------------------------|-------------|
| `raid_kai_00001` | SSE_SBG_003_001 | SSE_SBG_003_004 | raid_kai_00001 | raid_kai_00001 | （空） | 202509010 |
| `raid_spy1_00001` | SSE_SBG_003_007 | （空） | raid_spy1_00001 | raid_spy1_00001 | （空） | 202510010 |

- `boss_bgm_asset_key` は一部のレイドに設定（ボス演出強調時）
- `boss_mst_enemy_stage_parameter_id` はレイドでは空文字

### パターン4: 防衛オブジェクトありステージ（chi系）

| id | mst_page_id | mst_enemy_outpost_id | mst_defense_target_id | normal_enemy_hp_coef | normal_enemy_attack_coef |
|----|-------------|---------------------|----------------------|---------------------|------------------------|
| `normal_chi_00004` | normal_chi_00004 | normal_chi_00004 | normal_chi_00004_nyako | 1.0 | 1.0 |
| `hard_chi_00004` | hard_chi_00004 | hard_chi_00004 | hard_chi_00004_nyako | 2.0 | 3.0 |

- `mst_defense_target_id` に防衛オブジェクトIDを設定（通常はNULL）

### パターン5: イベントステージ（ボスBGMあり）

| id | bgm_asset_key | boss_bgm_asset_key | loop_background_asset_key | release_key |
|----|---------------|--------------------|--------------------------|-------------|
| `event_kai1_charaget01_00001` | SSE_SBG_003_001 | SSE_SBG_003_004 | kai_00001 | 202509010 |

### パターン6: チュートリアル

| id | bgm_asset_key | loop_background_asset_key | mst_page_id | mst_enemy_outpost_id | release_key |
|----|---------------|--------------------------|-------------|---------------------|-------------|
| `tutorial_1` | SSE_SBG_003_002 | glo_00001 | tutorial | tutorial | 202509010 |

---

## 設定時のポイント

### ポイント1: id の命名は難易度・コンテンツ種別を先頭に

`{難易度/種別}_{キャラ}_{連番}` の順を守ることで、データ一覧でのソート・フィルタリングが容易になる。イベントは `event_{キャラ識別子}_{種別}_{連番}` の形式とする。

### ポイント2: PvP系は mst_auto_player_sequence_id を空文字にする

PvP系ステージはシーケンスによる敵出現制御を使用しないため、`mst_auto_player_sequence_id` と `mst_auto_player_sequence_set_id` は空文字で設定する。他コンテンツでこれらを空にすることは原則しない。

### ポイント3: 難易度による倍率設定

難易度差はステータス倍率カラムで表現する。

| 難易度 | normal_enemy_hp_coef | normal_enemy_attack_coef |
|--------|---------------------|------------------------|
| normal | 1.0 | 1.0 |
| hard | 2.0 | 2.0（〜3.0） |
| veryhard | 上位を参考に調整 | 上位を参考に調整 |

ボスのいないステージ（レイド等）では `boss_enemy_*_coef` もすべて 1.0 のままでよい。

### ポイント4: ボスBGMは演出に応じて設定

`boss_bgm_asset_key` はボス登場時に通常BGMから切り替わるBGMを指定する。ボスが存在しないステージ、またはBGM切り替えが不要なステージは空文字にする。レイドステージでも一部のみ設定されている（`raid_kai_00001` など）。

### ポイント5: mst_defense_target_id は防衛オブジェクト専用

`mst_defense_target_id` は敵に倒されると敗北となる防衛オブジェクト（例: nyako）がある場合のみ設定する。通常の「プレイヤータワーを守るだけ」のステージはNULLにする。

### ポイント6: loop_background_asset_key の省略

hard/veryhard/PvP/レイドなど多くのステージで空文字が採用されており、その場合はクライアント側でデフォルト背景が適用される。背景演出にこだわりがない場合は空文字でよい。

### ポイント7: mst_auto_player_sequence_id と mst_auto_player_sequence_set_id の関係

両カラムは通常同じ値（ステージID）を設定する。`mst_auto_player_sequence_set_id` が MstAutoPlayerSequence テーブルの `sequence_set_id` カラムに対応しており、これでシーケンス行をグルーピングして取得する。

### ポイント8: release_key の設定

本番リリース予定のデータは実際のリリース日時を設定する（例: `202509010`）。開発用・テスト用データは `999999999` を設定することで本番環境へのリリースを防ぐ。

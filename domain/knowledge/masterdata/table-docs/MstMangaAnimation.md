# MstMangaAnimation 詳細説明

> CSVパス: `projects/glow-masterdata/MstMangaAnimation.csv`

---

## 1. 概要

`MstMangaAnimation` は**ステージ原画演出の設定テーブル**。ステージ進行中に特定の条件（ステージ開始、勝利、敵召喚など）が発生した際に再生される漫画風アニメーション演出を定義する。

### ゲームプレイへの影響

- ステージ開始時・勝利時・敵召喚時などのタイミングで原画（イラスト）を使ったカットシーンが再生される
- `is_pause` フラグによってゲームを一時停止してアニメーションを見せるか、バックグラウンドで流すかを制御できる
- `can_skip` フラグでプレイヤーがスキップできるかを制御する
- `animation_speed` でアニメーション再生速度を調整できる（0.7が現行の標準速度）

### 関連テーブルとの構造

```
MstStage（ステージ定義）
  └─ mst_stage_id → MstMangaAnimation.mst_stage_id（1:N）
        ├─ condition_type = Start（ステージ開始時）
        ├─ condition_type = Victory（ステージクリア時）
        ├─ condition_type = EnemySummon（敵召喚時）
        └─ condition_type = TransformationStart（変身開始時）など
```

---

## 2. 全カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー（命名規則は後述） |
| `mst_stage_id` | varchar(255) | 不可 | - | 対象ステージID（`mst_stages.id`） |
| `condition_type` | enum | 可 | - | アニメーション再生条件タイプ。`MangaAnimationConditionType` |
| `condition_value` | varchar(255) | 不可 | - | 条件値（条件タイプにより意味が変わる） |
| `animation_start_delay` | int | 不可 | - | 条件発生からアニメーション開始までの遅延時間（ミリ秒） |
| `animation_speed` | decimal(3,2) | 不可 | 1.00 | アニメーション再生速度倍率（1.00が等速） |
| `is_pause` | tinyint | 不可 | - | ゲームを一時停止するか（1=一時停止、0=バックグラウンド再生） |
| `can_skip` | tinyint | 不可 | - | プレイヤーがスキップ可能か（1=可能、0=不可） |
| `asset_key` | varchar(255) | 不可 | - | 再生するアニメーションアセットのキー |
| `release_key` | bigint | 不可 | 1 | リリースキー。マスタデータのバージョン管理に使用 |

---

## 3. MangaAnimationConditionType（再生条件タイプ）

| 値 | 再生タイミング | condition_value の意味 |
|---|---|---|
| `None` | 条件なし | `0` |
| `Start` | ステージ開始時 | `0`（固定） |
| `Victory` | ステージクリア時 | `0`（固定） |
| `EnemySummon` | 敵召喚時 | 対象シーケンスID（例: `sequence_1`） |
| `EnemyMoveStart` | 敵の移動開始時 | シーケンスID |
| `TransformationReady` | 変身準備時 | シーケンスID |
| `TransformationStart` | 変身開始時 | シーケンスID |
| `TransformationEnd` | 変身終了時 | シーケンスID |

---

## 4. 命名規則 / IDの生成ルール

### IDフォーマット

```
{prefix}_{stage_id}_{condition}
```

または

```
genga_{stage_type}_{chapter}_{stage_num}_{animation_condition}
```

#### 具体例

| ID例 | ステージID | 条件 |
|------|-----------|------|
| `manga_animation_develop_001_01` | `develop_001` | Start（開発用） |
| `genga_jig_main_006_01_start` | `normal_jig_00001` | Start |
| `genga_jig_main_006_01_victory` | `normal_jig_00001` | Victory |

---

## 5. 他テーブルとの連携

### 参照するテーブル

| テーブル | カラム | 説明 |
|---------|--------|------|
| `mst.stages` | `mst_manga_animation.mst_stage_id → mst_stages.id` | アニメーションが紐づくステージ |

### 参照されるテーブル

このテーブルは他テーブルから直接参照されることはない。クライアントがステージIDをキーに演出設定を取得する。

---

## 6. 実データ例

### 開発用ステージ演出（複数条件）

| id | mst_stage_id | condition_type | condition_value | animation_start_delay | animation_speed | is_pause | can_skip | asset_key |
|---|---|---|---|---|---|---|---|---|
| manga_animation_develop_001_01 | develop_001 | Start | 0 | 0 | 0.7 | 1 | 1 | develop_001_start |
| manga_animation_develop_001_02 | develop_001 | EnemySummon | sequence_1 | 0 | 0.7 | 1 | 1 | develop_001_start |
| manga_animation_develop_001_03 | develop_001 | EnemySummon | sequence_1 | 0 | 0.7 | 0 | 0 | develop_001_talk_01 |

- 同一ステージに複数の演出を設定可能
- `is_pause=1, can_skip=1` が標準設定

### 通常ステージの演出（開始・勝利）

| id | mst_stage_id | condition_type | condition_value | animation_speed | is_pause | can_skip | asset_key |
|---|---|---|---|---|---|---|---|
| genga_jig_main_006_01_start | normal_jig_00001 | Start | 0 | 0.7 | 1 | 1 | genga_jig_main_006_01_start |
| genga_jig_main_006_01_victory | normal_jig_00001 | Victory | 0 | 0.7 | 1 | 1 | genga_jig_main_006_01_victory |

- ステージ開始時と勝利時に別々のアセットを使用

---

## 7. 設定時のポイント

- 1ステージに対して複数の演出レコードを設定でき、`condition_type` と `condition_value` の組み合わせで発火タイミングを制御する
- `animation_speed` は現行データでは `0.7` が標準値（等速より少し遅め）
- ゲームを止めて演出を見せる場合は `is_pause = 1`、バックグラウンド再生の場合は `is_pause = 0` を設定する
- 初見プレイヤー向けストーリー演出は `can_skip = 1` にしてスキップを許可するのが標準
- `asset_key` はアドレッサブルアセットのキーと一致させる必要がある（アセット担当と要確認）
- `EnemySummon` などのシーケンス系条件は `condition_value` にシーケンスIDを設定する
- `Start` / `Victory` の `condition_value` は `0`（固定値）で設定する
- クライアントクラス: `MstMangaAnimationData.cs`（`MangaAnimationConditionType` enum を使用）

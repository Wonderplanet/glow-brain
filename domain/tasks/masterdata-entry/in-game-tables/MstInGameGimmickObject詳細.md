# MstInGameGimmickObject 詳細説明

> CSVパス: `projects/glow-masterdata/MstInGameGimmickObject.csv`

---

## 概要

MstInGameGimmickObject は**バトルフィールド上に配置する特殊オブジェクト（ギミックオブジェクト）の見た目アセットを管理**するテーブル。

「フィールド上にマンホールや障害物を置き、特定条件（プレイヤーユニットが倒されたタイミングなど）でその場所から敵ユニットが出現する」演出に使う。

このテーブル自体はシンプルな3カラムのみで、**見た目（asset_key）だけを定義する**。
「いつ・どこに置くか」「どの敵に変換するか」はすべて `MstAutoPlayerSequence` 側で定義する。

```
MstAutoPlayerSequence（action_type = SummonGimmickObject）
  └─ action_value = MstInGameGimmickObject.id  ← ここで参照
                     └─ asset_key → フィールド上に表示されるオブジェクトの見た目

MstAutoPlayerSequence（action_type = TransformGimmickObjectToEnemy）
  └─ action_value = MstEnemyStageParameter.id（変換後の敵を指定）
     action_value2 = SummonGimmickObjectのsequence_element_id（どのオブジェクトを変換するかを指定）
```

---

## 全カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---------|----|----|------|
| `ENABLE` | string | ○ | `e` = 有効 |
| `id` | string | ○ | ギミックオブジェクトID。命名規則は後述 |
| `asset_key` | varchar(255) | ○ | 表示アセットキー。後述のアセットパス変換に使用 |
| `release_key` | bigint | ○ | リリースキー |

---

## asset_key（アセットキー）

`asset_key` からアセットパスが以下のルールで自動生成される。

```
アセットパス = "ingame_gimmick_object_{asset_key}"
```

例：

| asset_key | 生成されるアセットパス |
|-----------|---------------------|
| `dummy1` | `ingame_gimmick_object_dummy1` |
| `dummy3` | `ingame_gimmick_object_dummy3` |

### 現行の asset_key 一覧

| asset_key | 使用しているレコード | 備考 |
|-----------|-------------------|------|
| `dummy1` | kai_honju_enemy, kai_honju_enemy_vh, raid_kai_00001 | 暫定ダミーアセット |
| `dummy2` | gimmick_dummy2 | 開発用ダミーのみ |
| `dummy3` | mag_manhole_enemy, gimmick_dummy3 | 暫定ダミーアセット |

> 現状は本番用アセットが未実装のため `dummy1`〜`dummy3` で代用されている。
> 本番アセットが実装されたら `asset_key` を差し替える。

---

## IDの命名規則

### 基本パターン

```
{コンテンツ識別子}_{役割・用途}
```

### 現行データの例

| id | コンテンツ | 用途 |
|----|----------|------|
| `mag_manhole_enemy` | mag（マグ）コンテンツ | マンホールから敵が出現するギミック |
| `kai_honju_enemy` | kai（カイ）コンテンツ | ノーマル・ハード難易度 |
| `kai_honju_enemy_vh` | kai（カイ）コンテンツ | ベリーハード難易度専用 |
| `raid_kai_00001` | raidコンテンツ（kai） | ステージ固有のギミック |
| `gimmick_dummy1`〜`3` | なし | 開発用ダミー（release_key=999999999）|

> 難易度別に挙動を変えたい場合（例: VHだけ配置位置や変換タイミングを変える）は、
> 難易度ごとに別IDを作成する（`kai_honju_enemy` と `kai_honju_enemy_vh` のように）。

---

## MstAutoPlayerSequence との連携

ギミックオブジェクトを使うには、MstAutoPlayerSequence に **2種類の行をセット** で作成する。

### ① SummonGimmickObject（フィールドに配置する行）

フィールド上の指定座標にギミックオブジェクトを置く。

| カラム | 設定値 | 説明 |
|--------|--------|------|
| `action_type` | `SummonGimmickObject` | - |
| `action_value` | MstInGameGimmickObject.id | どのギミックオブジェクトを置くか |
| `condition_type` | `InitialSummon` | バトル開始時に配置（固定） |
| `condition_value` | ウェーブ番号など | 配置タイミングの条件値 |
| `summon_count` | `1` | 1体ずつ配置（固定） |
| `summon_position` | 数値（例: `0.9`, `1.8`） | フィールド上のX軸位置 |

### ② TransformGimmickObjectToEnemy（敵に変換する行）

特定条件で①で配置したオブジェクトを敵ユニットに変換する。

| カラム | 設定値 | 説明 |
|--------|--------|------|
| `action_type` | `TransformGimmickObjectToEnemy` | - |
| `action_value` | MstEnemyStageParameter.id | 変換後に出現する敵のID |
| `action_value2` | ①のsequence_element_id | **どのオブジェクトを変換するかの対応付け** |
| `condition_type` | `FriendUnitDead` など | 変換トリガー条件 |
| `condition_value` | 数値 | 変換トリガーの条件値 |

> **`action_value2` の対応付けが重要**
> `action_value2` に ① SummonGimmickObject行の `sequence_element_id` を指定することで、
> 「このTransformはあのGimmickObjectを変換する」という対応が決まる。

### セット設定の具体例（mag_manhole_enemy の場合）

```
① SummonGimmickObject の行:
  id:                     normal_mag_00005_4
  sequence_element_id:    4
  condition_type:         InitialSummon
  condition_value:        2
  action_type:            SummonGimmickObject
  action_value:           mag_manhole_enemy   ← MstInGameGimmickObject.id
  summon_position:        1.3

② TransformGimmickObjectToEnemy の行（その1）:
  id:                     normal_mag_00005_5
  sequence_element_id:    5
  condition_type:         FriendUnitDead
  condition_value:        3                   ← 味方が3体倒されたら変換
  action_type:            TransformGimmickObjectToEnemy
  action_value:           e_mag_00301_general_Normal_Colorless  ← 出現する敵
  action_value2:          4                   ← ①のsequence_element_id と一致
  move_stop_condition_type:   ElapsedTime
  move_stop_condition_value:  25
  override_drop_battle_point: 20

② TransformGimmickObjectToEnemy の行（その2）:
  …同じオブジェクト（sequence_element_id=4）を別条件でまた変換指定
  action_value2:          4                   ← 同じ①を複数回変換できる
  action_value:           e_mag_00301_general_Normal_Blue
  condition_value:        8                   ← 味方8体撃破で別の敵が出現
```

> **1つのギミックオブジェクトに複数回の変換を設定できる**
> `action_value2` が同じ値を持つ TransformGimmickObjectToEnemy 行を複数作ることで、
> 同じマンホールから条件ごとに異なる敵を繰り返し出現させることが可能。

---

## 実データ例

### パターン1: 繰り返し出現するマンホール型ギミック

`mag_manhole_enemy`（使用コンテンツ: normal_mag_00005, hard_mag_00005, event_mag1_savage_00002）

```
id:        mag_manhole_enemy
asset_key: dummy3        → ingame_gimmick_object_dummy3
```

- バトル開始時（InitialSummon）にフィールド3か所に配置
- 味方が倒されるたびにマンホールから敵が出現（TransformGimmickObjectToEnemy）
- 1つのマンホールから複数回・複数種の敵が出現する設定になっている

---

### パターン2: 難易度別に使い分けるギミック

`kai_honju_enemy`（ノーマル・ハード用） / `kai_honju_enemy_vh`（ベリーハード用）

```
id:        kai_honju_enemy
asset_key: dummy1

id:        kai_honju_enemy_vh
asset_key: dummy1        ← 見た目は同じ、出現タイミングや変換先敵が難易度別で異なる
```

- 同じ見た目でも難易度によって変換タイミングや変換後の敵強さを変えたい場合、IDを分けて別のシーケンスから参照する

---

### パターン3: 変換なしの配置型ギミック

`raid_kai_00001`（使用コンテンツ: raid_kai_00001）

```
id:        raid_kai_00001
asset_key: dummy1

シーケンス:
  SummonGimmickObject → InitialSummon でフィールドに配置
  enemy_hp_coef: 10, enemy_attack_coef: 0.01  ← 超高HP・無攻撃設定
  TransformGimmickObjectToEnemy は設定なし
```

- 敵への変換は行わない配置専用のギミックオブジェクト
- `enemy_hp_coef`・`enemy_attack_coef` の倍率設定でギミックの扱いを制御している

---

## 設定時のポイントと注意事項

### ポイント1: 単独では何もしない

MstInGameGimmickObject 自体には「いつ出す」「何に変換する」の情報がない。
必ず MstAutoPlayerSequence の `SummonGimmickObject` 行とセットで設定する。

### ポイント2: TransformGimmickObjectToEnemy の action_value2 は対応付けのキー

`action_value2` に設定する値は、対応する `SummonGimmickObject` 行の `sequence_element_id`。
ここがずれると「違うオブジェクトが変換される」「変換が発動しない」バグになる。

```
SummonGimmickObject の行:    sequence_element_id = 4
TransformGimmickObjectToEnemy: action_value2       = 4  ← 一致させる
```

### ポイント3: 難易度別にIDを分けるかどうかの判断

変換後の敵や変換タイミングが難易度間で同じ → 同じIDを使い回してよい
変換後の敵や変換タイミングが難易度間で異なる → 難易度別にIDを分けてシーケンスを別々に作る

### ポイント4: asset_key は本番アセット実装後に更新

現状はダミーアセット（dummy1〜3）で実装されている。
本番用アセットが用意されたら `asset_key` を差し替えるだけでよい（シーケンス側の変更不要）。

### ポイント5: 1つのオブジェクトから複数回出現させる

同じ `sequence_element_id` を `action_value2` に持つ TransformGimmickObjectToEnemy 行を複数追加することで、
同一ギミックオブジェクトから条件が異なるたびに異なる敵を繰り返し出現させられる。

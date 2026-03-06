# MstUnitAbility 詳細説明

> CSVパス: `projects/glow-masterdata/MstUnitAbility.csv`

---

## 概要

ユニット（キャラ）の特性（アビリティ）の具体的なパラメータを設定するテーブル。各アビリティは汎用的なアビリティ定義（`mst_abilities`）を参照し、そのアビリティを実際のキャラに適用するためのパラメータを最大3つ（`ability_parameter1`〜`3`）で指定する。

- `mst_units.mst_unit_ability_id1/2/3` からこのテーブルの `id` を参照する
- `mst_ability_id` は汎用アビリティ定義のID（例: `ability_guts`・`ability_knockback_block` など）
- `ability_parameter1/2/3` にはアビリティごとに異なる意味の値を設定する

---

## 全カラム一覧

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---|---|---|---|---|
| id | varchar(255) | 不可 | - | アビリティID（例: `ability_dan_00002_01`） |
| mst_ability_id | varchar(255) | 不可 | - | 汎用アビリティ定義ID（`mst_abilities.id`相当） |
| ability_parameter1 | varchar(255) | 可 | - | アビリティパラメータ1（アビリティにより意味が異なる） |
| ability_parameter2 | varchar(255) | 可 | - | アビリティパラメータ2（アビリティにより意味が異なる） |
| ability_parameter3 | varchar(255) | 可 | - | アビリティパラメータ3（アビリティにより意味が異なる） |
| release_key | bigint | 不可 | `1` | リリースキー |

---

## 命名規則 / IDの生成ルール

IDは `ability_{シリーズコード}_{5桁番号}_{2桁スロット番号}` の形式で構成される。

例:
- `ability_dan_00002_01` → ダンダダンシリーズのキャラ00002の、アビリティスロット01
- `ability_dan_00002_02` → ダンダダンシリーズのキャラ00002の、アビリティスロット02

---

## 他テーブルとの連携

| 関連テーブル | カラム | 説明 |
|---|---|---|
| `mst_units` | `mst_unit_ability_id1/2/3` → `mst_unit_abilities.id` | このアビリティを持つキャラの参照 |
| `mst_abilities`（想定） | `mst_ability_id` → アビリティの汎用定義 | アビリティの効果種別と処理ロジック |

---

## 実データ例

### 例1: ガッツアビリティ（HP残り時のサバイバル）

```
id                   | mst_ability_id | ability_parameter1 | ability_parameter2 | ability_parameter3 | release_key
ability_aha_00001_01 | ability_guts   | 1                  | 100                | 0                  | 202509010
```

`ability_guts`：ガッツ系アビリティ。parameter1=1（回数）、parameter2=100（残HP%）など。

### 例2: ノックバック無効アビリティ

```
id                   | mst_ability_id             | ability_parameter1 | ability_parameter2 | ability_parameter3 | release_key
ability_dan_00002_01 | ability_knockback_block    | 0                  | 0                  | 0                  | 202509010
```

`ability_knockback_block`：ノックバックを無効化するアビリティ。追加パラメータは不要のため全て 0。

### 例3: 攻撃力アップアビリティ（コマブースト時）

```
id                   | mst_ability_id                     | ability_parameter1 | ability_parameter2 | ability_parameter3 | release_key
ability_bat_00001_01 | ability_attack_power_up_koma_boost | 15                 | 0                  | 0                  | 202509010
```

`ability_attack_power_up_koma_boost`：コマブースト時に攻撃力が15%アップするアビリティ。

---

## 設定時のポイント

1. **パラメータの意味はアビリティ定義に依存**: `ability_parameter1/2/3` の意味は `mst_ability_id` が示すアビリティの種別によって異なる。アビリティごとのパラメータ仕様を `mst_abilities` の定義で確認すること
2. **パラメータ不要の場合は 0 を設定**: アビリティの動作にパラメータが不要な場合は `0` を設定する（NULL ではなく空文字または 0）
3. **id の命名規則を遵守**: `ability_{シリーズコード}_{5桁番号}_{2桁スロット番号}` の形式で、対応するキャラIDと一致させる
4. **mst_units との整合性**: このテーブルにレコードを追加した場合、対応する `mst_units` の `mst_unit_ability_id1/2/3` にIDを設定すること
5. **クライアントクラス**: `MstUnitAbilityData`（`GLOW.Core.Data.Data`名前空間）。`id`・`mstAbilityId`・`abilityParameter1`・`abilityParameter2`・`abilityParameter3` が配信される（全パラメータは文字列型として配信）
6. **最大3スロット**: キャラに設定できるアビリティは最大3つ（mst_unit_ability_id1〜3）。実データでは1〜2つのアビリティを持つキャラが多い
7. **アビリティの組み合わせでキャラの個性を表現**: 同じ `mst_ability_id` でもパラメータ値を変えることで異なる強度・効果のアビリティを表現できる

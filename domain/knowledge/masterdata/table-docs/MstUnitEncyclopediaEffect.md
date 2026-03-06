# MstUnitEncyclopediaEffect 詳細説明

> CSVパス: `projects/glow-masterdata/MstUnitEncyclopediaEffect.csv`

---

## 概要

キャラ図鑑（ユニット図鑑）の報酬達成時に発動するインゲーム効果（バフ）の設定テーブル。`mst_unit_encyclopedia_rewards` で定義された図鑑ランク報酬に対して、そのランクで付与されるインゲームバフ（HP増加・攻撃力増加など）を設定する。

- `mst_unit_encyclopedia_reward_id` で `mst_unit_encyclopedia_rewards` と 1対1に紐付く
- `effect_type` でバフの種類を指定し、`value` でバフの倍率（小数）を設定する
- 実データでは図鑑ランク5・10・15...と5ランクごとに HP 増加か攻撃力増加が交互に設定されている
- バフは全キャラに対して恒久的に適用される形式

---

## 全カラム一覧

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---|---|---|---|---|
| id | varchar(255) | 不可 | - | エフェクトID（例: `unit_encyclopedia_effect_5`） |
| mst_unit_encyclopedia_reward_id | varchar(255) | 不可 | - | 対応する図鑑報酬ID（`mst_unit_encyclopedia_rewards.id`） |
| effect_type | varchar(255) | 不可 | - | 効果種別（`UnitEncyclopediaEffectType` enum参照） |
| value | double | 不可 | - | 効果値（小数の倍率、例: 0.01 = 1%増加） |
| release_key | bigint | 不可 | `1` | リリースキー |

---

## UnitEncyclopediaEffectType（効果種別）

| 値 | 説明 |
|---|---|
| `Hp` | HP増加（value の倍率でHP上昇） |
| `AttackPower` | 攻撃力増加（value の倍率で攻撃力上昇） |

---

## 命名規則 / IDの生成ルール

IDは `unit_encyclopedia_effect_{図鑑ランク}` の形式で構成される。

例:
- `unit_encyclopedia_effect_5` → 図鑑ランク5達成時のインゲーム効果
- `unit_encyclopedia_effect_10` → 図鑑ランク10達成時のインゲーム効果

---

## 他テーブルとの連携

| 関連テーブル | カラム | 説明 |
|---|---|---|
| `mst_unit_encyclopedia_rewards` | `mst_unit_encyclopedia_reward_id` → `mst_unit_encyclopedia_rewards.id` | どの図鑑ランク報酬に対応するエフェクトかを特定 |

---

## 実データ例

### 例1: 図鑑ランク5〜20のインゲーム効果（HP・攻撃力が交互に増加）

```
id                           | mst_unit_encyclopedia_reward_id  | effect_type | value | release_key
unit_encyclopedia_effect_5   | unit_encyclopedia_reward_rank_5  | Hp          | 0.01  | 202509010
unit_encyclopedia_effect_10  | unit_encyclopedia_reward_rank_10 | AttackPower | 0.01  | 202509010
unit_encyclopedia_effect_15  | unit_encyclopedia_reward_rank_15 | Hp          | 0.01  | 202509010
unit_encyclopedia_effect_20  | unit_encyclopedia_reward_rank_20 | AttackPower | 0.01  | 202509010
```

ランク5でHP+1%、ランク10で攻撃力+1%が交互に付与される。

### 例2: 高ランクでのバフ増加

```
id                           | mst_unit_encyclopedia_reward_id  | effect_type | value | release_key
unit_encyclopedia_effect_45  | unit_encyclopedia_reward_rank_45 | Hp          | 0.01  | 202509010
unit_encyclopedia_effect_50  | unit_encyclopedia_reward_rank_50 | AttackPower | 0.015 | 202509010
```

ランク50以降は攻撃力バフが1.5%（0.015）と増加している。

---

## 設定時のポイント

1. **value は倍率（小数）で設定**: 1%増加であれば `0.01`、1.5%増加であれば `0.015` と小数で設定する
2. **mst_unit_encyclopedia_rewards との1対1対応**: 1つの図鑑報酬レコードに対して1つのエフェクトレコードを設定する
3. **id の命名規則を遵守**: `unit_encyclopedia_effect_{ランク}` の形式で、対応する図鑑ランクと一致させる
4. **新しいランクを追加する場合は mst_unit_encyclopedia_rewards も更新**: このテーブルにレコードを追加する際は `mst_unit_encyclopedia_rewards` にも対応するランク報酬を追加する
5. **クライアントクラス**: `MstUnitEncyclopediaEffectData`（`GLOW.Core.Data.Data`名前空間）。`id`・`mstUnitEncyclopediaRewardId`・`effectType`（`UnitEncyclopediaEffectType` enum）・`value` が配信される
6. **高ランクほどバフ値を大きく**: ゲームデザインとして、低ランク（5〜40）は0.01（1%）、高ランク（50以降）は0.015（1.5%）のようにランクが上がるにつれてバフを強化するパターンが実データで確認できる
7. **HP と攻撃力を交互に設定**: 実データでは5の倍数ランクにHP強化と攻撃力強化が交互に設定されており、バフの種類が偏らないよう設計されている

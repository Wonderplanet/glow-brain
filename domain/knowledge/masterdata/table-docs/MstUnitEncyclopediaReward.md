# MstUnitEncyclopediaReward 詳細説明

> CSVパス: `projects/glow-masterdata/MstUnitEncyclopediaReward.csv`

---

## 概要

キャラ図鑑（ユニット図鑑）のランクごとの報酬設定テーブル。プレイヤーが所持するキャラのグレードを合算した「図鑑ランク」に応じて、マイルストーン報酬（アイテム・コインなど）を定義する。

- `unit_encyclopedia_rank` が図鑑ランクのマイルストーン値で、この値以上になったら報酬が付与される
- 5ランクごとにマイルストーンが設定されている（5、10、15、20...）
- 報酬だけでなく、対応する `mst_unit_encyclopedia_effects` テーブルでインゲームバフも設定される
- `id` は `unit_encyclopedia_reward_rank_{ランク}` の形式で管理される

---

## 全カラム一覧

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---|---|---|---|---|
| id | varchar(255) | 不可 | - | 図鑑報酬ID（例: `unit_encyclopedia_reward_rank_5`） |
| unit_encyclopedia_rank | int unsigned | 不可 | - | 図鑑ランクのマイルストーン値（グレードの合算値） |
| resource_type | enum | 不可 | - | 報酬タイプ（`ResourceType` enum参照） |
| resource_id | varchar(255) | 可 | - | 報酬リソースID（Coin/Expは NULL） |
| resource_amount | int unsigned | 不可 | `0` | 報酬数量 |
| release_key | bigint | 不可 | `1` | リリースキー |

---

## ResourceType（報酬タイプ）

| 値 | 説明 |
|---|---|
| `Exp` | 経験値（resource_idは NULL） |
| `Coin` | コイン（resource_idは NULL） |
| `FreeDiamond` | 無償ダイヤ（resource_idは NULL） |
| `Item` | アイテム（resource_idでアイテムIDを指定） |
| `Emblem` | エンブレム（resource_idでエンブレムIDを指定） |

---

## 命名規則 / IDの生成ルール

IDは `unit_encyclopedia_reward_rank_{ランク}` の形式で構成される。

例:
- `unit_encyclopedia_reward_rank_5` → 図鑑ランク5のマイルストーン報酬
- `unit_encyclopedia_reward_rank_50` → 図鑑ランク50のマイルストーン報酬

---

## 他テーブルとの連携

| 関連テーブル | カラム | 説明 |
|---|---|---|
| `mst_unit_encyclopedia_effects` | `id` → `mst_unit_encyclopedia_effects.mst_unit_encyclopedia_reward_id` | 図鑑ランク達成時のインゲームバフ設定 |
| `mst_items` | `resource_id` → `mst_items.id`（resource_typeがItemの場合） | 報酬アイテムの詳細 |

---

## 実データ例

### 例1: 図鑑ランク5〜10のマイルストーン報酬

```
id                               | unit_encyclopedia_rank | resource_type | resource_id      | resource_amount | release_key
unit_encyclopedia_reward_rank_5  | 5                      | Item          | ticket_glo_90000 | 5               | 202509010
unit_encyclopedia_reward_rank_10 | 10                     | Item          | ticket_glo_90000 | 5               | 202509010
```

ランク5・10でチケット（`ticket_glo_90000`）が5枚ずつ付与される。

### 例2: 高ランクのマイルストーン報酬

```
id                               | unit_encyclopedia_rank | resource_type | resource_id      | resource_amount | release_key
unit_encyclopedia_reward_rank_45 | 45                     | Item          | ticket_glo_90000 | 5               | 202509010
unit_encyclopedia_reward_rank_50 | 50                     | Item          | ticket_glo_90000 | 10              | 202509010
```

ランク50では報酬数量が10枚に増加する。

---

## 設定時のポイント

1. **5ランクごとのマイルストーンが基本**: 実データでは5の倍数ランクにマイルストーンが設定されている。新しいランク帯を追加する場合もこのパターンに従う
2. **対応するエフェクトも同時に追加**: 新しい図鑑ランクのマイルストーンを追加する場合、`mst_unit_encyclopedia_effects` にも対応するインゲームバフを設定する
3. **id の命名規則を遵守**: `unit_encyclopedia_reward_rank_{ランク}` の形式で設定する
4. **高ランクほど報酬を豪華に**: ランク50以降では報酬数量を増やすなど、高ランク達成者への報酬を手厚くするゲームデザインに対応する
5. **クライアントクラス**: `MstUnitEncyclopediaRewardData`（`GLOW.Core.Data.Data`名前空間）。`id`・`unitEncyclopediaRank`・`resourceType`・`resourceId`・`resourceAmount` が配信される
6. **図鑑ランクはグレードの合算値**: `unit_encyclopedia_rank` はプレイヤーが所持するすべてのキャラのグレードを合算した値。このマイルストーンを超えると報酬が解放される
7. **Item の resource_id は mst_items に存在するものを指定**: 報酬アイテムIDが `mst_items` テーブルに登録されていない場合、付与処理でエラーが発生する

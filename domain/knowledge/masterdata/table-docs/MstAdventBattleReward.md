# MstAdventBattleReward 詳細説明

> CSVパス: `projects/glow-masterdata/MstAdventBattleReward.csv`

---

## 概要

降臨バトルにおいて、スコア達成・ランキング・ランク到達・レイド累計スコアなどの条件を満たしたプレイヤーに付与される報酬の詳細を管理するテーブル。

このテーブルは `mst_advent_battle_reward_groups` の子テーブルで、1つのグループに対して複数の報酬レコードを設定できる。報酬タイプにはコイン・無料ダイヤ（プリズム）・アイテム・エンブレムが指定できる。

---

## 全カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---------|----|----|------|
| ENABLE | varchar | YES | 有効フラグ（`e` = 有効） |
| id | varchar(255) | YES | UUID。報酬レコードを一意に識別するID |
| mst_advent_battle_reward_group_id | varchar(255) | YES | 紐付く報酬グループの `mst_advent_battle_reward_groups.id` |
| resource_type | enum | YES | 報酬タイプ（`Coin` / `FreeDiamond` / `Item` / `Emblem`） |
| resource_id | varchar(255) | NO | 報酬のリソースID。Coin 等は不要でNULL/空文字 |
| resource_amount | int unsigned | YES | 報酬の配布数量 |
| release_key | bigint | YES | リリースキー |

---

## resource_type（報酬タイプ）

| 値 | 説明 | resource_id |
|----|------|-------------|
| `Coin` | コイン | 不要（NULL または空文字） |
| `FreeDiamond` | 無料ダイヤ（プリズム） | プリズムアイテムのID |
| `Item` | アイテム | アイテムのID |
| `Emblem` | エンブレム | エンブレムのID |

---

## 命名規則 / IDの生成ルール

- `id`: `{mst_advent_battle_reward_group_id}_{連番（2桁）}`（例: `quest_raid_kai_reward_group_00001_01`）
- ただし、現実のデータでは `mst_advent_battle_reward_group_id` と `id` が同一の場合もある

---

## 他テーブルとの連携

| テーブル | 関係 | 説明 |
|---------|------|------|
| `mst_advent_battle_reward_groups` | 多対1 | 報酬グループ。`mst_advent_battle_reward_group_id` で参照 |
| `mst_items` | 多対1 | resource_type が `Item` の場合に `resource_id` で参照 |
| `mst_emblems` | 多対1 | resource_type が `Emblem` の場合に `resource_id` で参照 |

---

## 実データ例

### パターン1: 無料ダイヤ（プリズム）報酬

```
ENABLE: e
id: quest_raid_kai_reward_group_00001_01
mst_advent_battle_reward_group_id: quest_raid_kai_reward_group_00001_01
resource_type: FreeDiamond
resource_id: prism_glo_00001
resource_amount: 20
release_key: 202509010
```

### パターン2: コイン報酬

```
ENABLE: e
id: quest_raid_kai_reward_group_00001_02
mst_advent_battle_reward_group_id: quest_raid_kai_reward_group_00001_02
resource_type: Coin
resource_id: （空文字）
resource_amount: 1500
release_key: 202509010
```

### パターン3: 高スコア達成時のプリズム大量報酬

```
ENABLE: e
id: quest_raid_kai_reward_group_00001_05
mst_advent_battle_reward_group_id: quest_raid_kai_reward_group_00001_05
resource_type: FreeDiamond
resource_id: prism_glo_00001
resource_amount: 50
release_key: 202509010
```

---

## 設定時のポイント

1. **report_group_id との1対1対応が多い**: 実データでは `id` と `mst_advent_battle_reward_group_id` が同じ値になっているケースが多い。1つのグループに1つの報酬を設定するパターンが標準的。
2. **resource_id の省略**: `Coin` タイプでは `resource_id` を空文字またはNULLにする。`FreeDiamond`（プリズム）はプリズムのアイテムIDを指定する。
3. **reward_group との整合性**: このテーブルは必ず `mst_advent_battle_reward_groups` の `id` と対応したレコードを作成する。孤立したレコードを作らないよう注意する。
4. **スコア達成報酬の段階的設計**: スコアが高いほど価値の高い報酬（プリズム大量など）を設定し、プレイヤーの挑戦意欲を高める設計にする。
5. **resource_amount の単位**: コインは通常100〜10000程度、プリズムは10〜100程度の範囲で設定されることが多い。
6. **同一グループで複数報酬**: 1グループに複数の報酬を設定したい場合（例: コイン + アイテム）は、`id` を連番で複数レコード作成する。

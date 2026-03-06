# MstAdventBattleClearReward 詳細説明

> CSVパス: `projects/glow-masterdata/MstAdventBattleClearReward.csv`

---

## 概要

降臨バトルをクリアした際のプレイヤーへの報酬設定を管理するテーブル。報酬には「毎回クリア時」「初回クリア時」「ランダム選択」の3つのカテゴリーがあり、アイテム・コイン・プリズム・エンブレム・ユニットなどを報酬として設定できる。

`mst_advent_battle_clear_rewards` は1回のバトルクリアに対して複数のレコードで報酬を構成する。例えば、毎回もらえる報酬（Always）と初回クリア報酬（FirstClear）、ランダム報酬（Random）を組み合わせて設定できる。

---

## 全カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---------|----|----|------|
| ENABLE | varchar | YES | 有効フラグ（`e` = 有効） |
| id | varchar(255) | YES | UUID。報酬レコードを一意に識別するID |
| mst_advent_battle_id | varchar(255) | YES | 紐付く降臨バトルの `mst_advent_battles.id` |
| reward_category | enum | YES | 報酬カテゴリー（`Always` / `FirstClear` / `Random`） |
| resource_type | enum | YES | 報酬タイプ（`Exp` / `Coin` / `FreeDiamond` / `Item` / `Emblem` / `Unit`） |
| resource_id | varchar(255) | NO | 報酬のリソースID。アイテムIDやユニットIDを指定。コイン・EXP等は不要でNULL |
| resource_amount | int unsigned | NO | 報酬の配布数量 |
| percentage | int unsigned | YES | Random時の出現比重（重み付け抽選に使用） |
| sort_order | int unsigned | YES | 報酬の表示ソート順 |
| release_key | bigint | YES | リリースキー |

---

## reward_category（報酬カテゴリー）

| 値 | 説明 |
|----|------|
| `Always` | 毎回クリア時に必ず付与される報酬 |
| `FirstClear` | 初回クリア時のみ付与される報酬 |
| `Random` | 設定された報酬の中からランダムで選択されて付与される報酬。`percentage` で重み付け抽選 |

## resource_type（報酬タイプ）

| 値 | 説明 | resource_id |
|----|------|-------------|
| `Exp` | リーダーEXP | 不要（NULL） |
| `Coin` | コイン | 不要（NULL） |
| `FreeDiamond` | 無料ダイヤ（プリズム） | プリズムアイテムのID |
| `Item` | アイテム | アイテムのID |
| `Emblem` | エンブレム | エンブレムのID |
| `Unit` | ユニット | ユニットのID |

---

## 命名規則 / IDの生成ルール

- `id`: `{mst_advent_battle_id}_{連番（2桁）}`（例: `quest_raid_kai_00001_01`、`quest_raid_kai_00001_02`）

---

## 他テーブルとの連携

| テーブル | 関係 | 説明 |
|---------|------|------|
| `mst_advent_battles` | 多対1 | 対象の降臨バトル。`mst_advent_battle_id` で参照 |
| `mst_items` | 多対1 | resource_type が `Item` の場合に `resource_id` で参照 |
| `mst_emblems` | 多対1 | resource_type が `Emblem` の場合に `resource_id` で参照 |
| `mst_units` | 多対1 | resource_type が `Unit` の場合に `resource_id` で参照 |

---

## 実データ例

### パターン1: ランダム報酬（メモリーアイテム群）

```
ENABLE: e
id: quest_raid_kai_00001_01
mst_advent_battle_id: quest_raid_kai_00001
reward_category: Random
resource_type: Item
resource_id: memory_glo_00001
resource_amount: 3
percentage: 20
sort_order: 1
release_key: 202509010
```

```
ENABLE: e
id: quest_raid_kai_00001_02
mst_advent_battle_id: quest_raid_kai_00001
reward_category: Random
resource_type: Item
resource_id: memory_glo_00002
resource_amount: 3
percentage: 20
sort_order: 2
release_key: 202509010
```

### パターン2: 複数の報酬カテゴリーを組み合わせた構成（初回クリア + ランダム）

初回クリア時は特定のアイテムを確定付与し、通常クリア時はランダム選択の構成:

```
# 初回クリア報酬
ENABLE: e
id: quest_raid_kai_00001_fc_01
mst_advent_battle_id: quest_raid_kai_00001
reward_category: FirstClear
resource_type: FreeDiamond
resource_id: prism_glo_00001
resource_amount: 50
percentage: 1
sort_order: 1
release_key: 202509010

# ランダム報酬
ENABLE: e
id: quest_raid_kai_00001_01
mst_advent_battle_id: quest_raid_kai_00001
reward_category: Random
resource_type: Item
resource_id: memory_glo_00001
resource_amount: 3
percentage: 20
sort_order: 1
release_key: 202509010
```

---

## 設定時のポイント

1. **percentage の合計**: `Random` カテゴリーの `percentage` は報酬ごとの「重み」であり、合計が100である必要はない。抽選は比重（重み付き抽選）で行われる。
2. **sort_order の連番管理**: 同一 `mst_advent_battle_id` 内で `sort_order` を連番で振ることで、UI上の表示順を制御できる。
3. **resource_id の省略**: `Exp` や `Coin` のように数量のみを指定すれば済むリソースタイプでは `resource_id` に NULL（または空文字）を設定する。
4. **FirstClear の単独設定**: `FirstClear` は1回しか受け取れない。初回限定で特別報酬を設定する際に使用し、通常クリア報酬（Always/Random）と組み合わせて設定する。
5. **同一バトルの全カテゴリーを確認**: 1つの降臨バトルに対して Always、FirstClear、Random が混在するケースがある。全カテゴリーを把握した上で設定する。
6. **報酬数量の統一**: 同じ `reward_category` 内であれば、`resource_amount` を統一してゲームバランスを保つことが推奨される。

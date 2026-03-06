# MstStageClearTimeReward 詳細説明

> CSVパス: `projects/glow-masterdata/MstStageClearTimeReward.csv`

---

## 概要

ステージごとのクリアタイムに応じた報酬設定を管理するテーブル。各ステージには複数のタイム目標（上限タイム）を設定でき、プレイヤーがそのタイム以内にクリアすると対応した報酬を獲得できる。いわゆる「タイムアタック報酬」機能を実現するためのデータ。

- 1ステージにつき複数レコード（タイム段階ごとに1件）を設定できる
- `upper_clear_time_ms` が小さいほど難しい目標であり、より速いタイムが求められる
- 実データではほぼすべてのステージに3段階の目標タイム（140秒・200秒・300秒）が設定されている

---

## 全カラム一覧

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---|---|---|---|---|
| id | varchar(255) | 不可 | - | 一意識別子（例: `normal_spy_00001_1`） |
| mst_stage_id | varchar(255) | 不可 | - | 対象ステージID（`mst_stages.id`） |
| upper_clear_time_ms | int unsigned | 不可 | - | 目標タイム上限（ミリ秒） |
| resource_type | enum | 不可 | - | 報酬タイプ（`ResourceType` enum参照） |
| resource_id | varchar(255) | 可 | - | 報酬リソースのID（Coin/FreeDiamondはNULL） |
| resource_amount | int unsigned | 不可 | - | 報酬数量 |
| release_key | bigint | 不可 | - | リリースキー |

---

## ResourceType（報酬タイプ）

| 値 | 説明 |
|---|---|
| `Coin` | コイン |
| `FreeDiamond` | 無償ダイヤ |
| `Item` | アイテム（resource_idでアイテムIDを指定） |
| `Emblem` | エンブレム（resource_idでエンブレムIDを指定） |
| `Unit` | キャラ（resource_idでキャラIDを指定） |

---

## 命名規則 / IDの生成ルール

IDは `{mst_stage_id}_{連番}` の形式で構成される。

例:
- `normal_spy_00001_1` → ステージ `normal_spy_00001` の1つ目のタイム目標
- `normal_spy_00001_2` → ステージ `normal_spy_00001` の2つ目のタイム目標
- `normal_spy_00001_3` → ステージ `normal_spy_00001` の3つ目のタイム目標

---

## 他テーブルとの連携

| 関連テーブル | カラム | 説明 |
|---|---|---|
| `mst_stages` | `mst_stage_id` → `mst_stages.id` | どのステージのタイム報酬かを特定 |
| `mst_items` | `resource_id` → `mst_items.id`（resource_typeがItemの場合） | 報酬アイテムの詳細 |

---

## 実データ例

### 例1: ノーマルステージ（3段階タイム目標）

```
id                   | mst_stage_id     | upper_clear_time_ms | resource_type | resource_id | resource_amount | release_key
normal_spy_00001_1   | normal_spy_00001 | 140000              | FreeDiamond   | NULL        | 25              | 202509010
normal_spy_00001_2   | normal_spy_00001 | 200000              | FreeDiamond   | NULL        | 25              | 202509010
normal_spy_00001_3   | normal_spy_00001 | 300000              | FreeDiamond   | NULL        | 25              | 202509010
```

140秒・200秒・300秒の3段階があり、それぞれ無償ダイヤ25個が報酬。

### 例2: 別のノーマルステージ

```
id                   | mst_stage_id     | upper_clear_time_ms | resource_type | resource_id | resource_amount | release_key
normal_spy_00002_1   | normal_spy_00002 | 140000              | FreeDiamond   | NULL        | 25              | 202509010
normal_spy_00002_2   | normal_spy_00002 | 200000              | FreeDiamond   | NULL        | 25              | 202509010
normal_spy_00002_3   | normal_spy_00002 | 300000              | FreeDiamond   | NULL        | 25              | 202509010
```

---

## 設定時のポイント

1. **タイム単位はミリ秒**: `upper_clear_time_ms` はミリ秒指定のため、140秒 = `140000` と設定する
2. **同一ステージに複数設定**: 1ステージに対して難易度別に複数レコードを追加できる。実データでは3段階が標準
3. **IDの命名規則を遵守**: `{mst_stage_id}_{連番}` の形式でIDを生成する
4. **タイムの昇順並び**: `upper_clear_time_ms` の値が小さいほど難しい目標。設定時は昇順（小さい順）に並べると管理しやすい
5. **resource_idの扱い**: `resource_type` が `Coin` または `FreeDiamond` の場合は `resource_id` を NULL にする
6. **クライアントクラス**: `MstStageClearTimeRewardData`（`GLOW.Core.Data.Data`名前空間）。クライアントへは `mstStageId`・`upperClearTimeMs`・`resourceType`・`resourceId`・`resourceAmount` が配信される（idとrelease_keyは含まれない）
7. **リリースキーの統一**: 同一ステージの全タイム段階は同じ `release_key` を使用すること

# MstBattlePointLevel 詳細説明

> CSVパス: `projects/glow-masterdata/MstBattlePointLevel.csv`

---

## 概要

インゲームで使用するバトルポイントのレベル別設定（初期ポイント、貯まる速度、最大ポイントなど）を定義するマスタテーブル。

**注意: このテーブルは現在未使用。** 当初はバトルポイントが貯まるとレベルアップしてさらにポイントが貯まる速度が強化される仕様だったが、現在はその仕様は採用されていない。バトルポイントに関する実際の設定は `mst_configs` テーブルの `IN_GAME_MAX_BATTLE_POINT`・`IN_GAME_BATTLE_POINT_CHARGE_AMOUNT`・`IN_GAME_BATTLE_POINT_CHARGE_INTERVAL` キーで管理されている。

CSVファイルは存在しない（未使用のため）。

---

## 全カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---|---|---|---|
| id | varchar(255) | YES | レコードID（主キー） |
| release_key | bigint | YES | リリースキー（デフォルト: 1） |
| level | int | YES | バトルポイントレベル（1から順番） |
| required_level_up_battle_point | int | YES | レベルを上げるために必要なポイント数 |
| max_battle_point | int | YES | そのレベルでの上限ポイント |
| charge_amount | int | YES | 1回でチャージされるポイント量 |
| charge_interval | int | YES | 何フレームごとにポイントが貯まるかの設定 |

---

## 他テーブルとの連携

現在未使用のため、他テーブルとの連携はない。バトルポイントの実設定は `mst_configs` テーブルを参照。

| 関連テーブル | 関連キー | 内容 |
|---|---|---|
| `mst_configs` | `IN_GAME_MAX_BATTLE_POINT` | 実際のバトルポイント上限値（現在2000） |
| `mst_configs` | `IN_GAME_BATTLE_POINT_CHARGE_AMOUNT` | 実際のチャージ量（現在3） |
| `mst_configs` | `IN_GAME_BATTLE_POINT_CHARGE_INTERVAL` | 実際のチャージ間隔（現在5フレーム） |

---

## 実データ例

CSVファイルが存在しないため、DBスキーマから推定される設定例を示す。

**例1: レベル1の設定（想定）**

| id | level | required_level_up_battle_point | max_battle_point | charge_amount | charge_interval |
|---|---|---|---|---|---|
| battle_point_level_1 | 1 | 500 | 1000 | 3 | 5 |

**例2: レベル2の設定（想定）**

| id | level | required_level_up_battle_point | max_battle_point | charge_amount | charge_interval |
|---|---|---|---|---|---|
| battle_point_level_2 | 2 | 1000 | 2000 | 5 | 4 |

---

## 設定時のポイント

1. このテーブルは現在未使用であり、実際のバトルポイント設定は `mst_configs` テーブルで管理されている。
2. バトルポイントの上限・チャージ量・チャージ間隔を変更する場合は `mst_configs` テーブルの該当キーを修正すること。
3. 将来的にこのテーブルが有効化される場合は、`level` を1から順番に設定し連続したレベル定義を作成する。
4. `charge_interval` はフレーム単位（一般的に60FPS）で設定する。
5. `required_level_up_battle_point` はそのレベルでのレベルアップ条件であり、`max_battle_point` 以下の値にすること。
6. レベルアップ機能が実装される場合は、クライアント実装との連携が必要になる点に注意する。

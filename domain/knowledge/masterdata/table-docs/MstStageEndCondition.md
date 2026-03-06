# MstStageEndCondition 詳細説明

> CSVパス: `projects/glow-masterdata/MstStageEndCondition.csv`

---

## 概要

インゲームにて、特殊な勝利・敗北・終了条件を持つステージの終了条件を設定するテーブル。通常のステージは拠点破壊で勝利・敗北が決まるが、このテーブルを使うことで「時間切れで勝利」「特定の敵を討伐したら勝利」などのカスタム終了条件を設定できる。

- 1ステージに複数の終了条件を設定可能（例: 勝利条件と敗北条件を別レコードで設定）
- `stage_end_type` で勝利・敗北・終了のどれかを指定し、`condition_type` で具体的な判定方法を指定する
- `id` カラムは UUID ではなく、ステージIDと同じ値が設定されている（実データより）

---

## 全カラム一覧

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---|---|---|---|---|
| id | varchar(255) | 不可 | - | ステージIDと同じ値が入る（一意ではなく同ステージの条件が複数行になる） |
| mst_stage_id | varchar(255) | 不可 | - | 対象ステージID |
| stage_end_type | varchar(255) | 不可 | `Victory` | バトル終了結果タイプ（`StageEndType` enum参照） |
| condition_type | varchar(255) | 不可 | `PlayerOutpostBreakDown` | 終了条件の種別（`StageEndConditionType` enum参照） |
| condition_value1 | varchar(255) | 不可 | `` | 条件の補足値1（不要な場合は空文字） |
| condition_value2 | varchar(255) | 不可 | `` | 条件の補足値2（不要な場合は空文字） |
| release_key | bigint | 不可 | `1` | リリースキー |

---

## StageEndType（バトル終了タイプ）

| 値 | 説明 |
|---|---|
| `Victory` | 勝利（この条件が満たされるとプレイヤーが勝利） |
| `Defeat` | 敗北（この条件が満たされるとプレイヤーが敗北） |
| `Finish` | 終了（勝敗を問わずバトルが終了する） |

---

## StageEndConditionType（終了条件種別）

| 値 | 説明 | condition_value1 | condition_value2 |
|---|---|---|---|
| `EnemyOutpostBreakDown` | 敵拠点が破壊された | 空 | 空 |
| `PlayerOutpostBreakDown` | 自拠点が破壊された | 空 | 空 |
| `TimeOver` | 時間切れ（制限秒数を指定） | 制限秒数（例: `120`） | 空 |
| `DefeatUnit` | 特定ユニットの討伐 | 対象ユニットID | 討伐数 |

---

## 他テーブルとの連携

| 関連テーブル | カラム | 説明 |
|---|---|---|
| `mst_stages` | `mst_stage_id` → `mst_stages.id` | 終了条件を持つステージ |

---

## 実データ例

### 例1: 制限時間付きサバイバルステージ（勝利=時間生き残り、敗北=拠点破壊）

```
id                | mst_stage_id     | stage_end_type | condition_type         | condition_value1 | condition_value2 | release_key
normal_sur_00003  | normal_sur_00003 | Victory        | TimeOver               | 120              | NULL             | 202509010
normal_sur_00003  | normal_sur_00003 | Defeat         | PlayerOutpostBreakDown | NULL             | NULL             | 202509010
```

120秒間を自拠点破壊されずに生き残ると勝利。自拠点が破壊されたら敗北。

### 例2: 特定ユニット討伐ステージ

```
id                          | mst_stage_id               | stage_end_type | condition_type | condition_value1 | condition_value2 | release_key
develop_plan_test_stage004  | develop_plan_test_stage004 | Victory        | DefeatUnit     | enemy_jig_00401  | 1                | 999999999
```

`enemy_jig_00401` を1体討伐すると勝利。

---

## 設定時のポイント

1. **複数レコードで1ステージの条件を構成**: 勝利条件と敗北条件は別レコードとして設定する。通常のステージでは必要ないが、特殊ルールのステージでのみ使用する
2. **condition_valueの型は文字列**: `condition_value1` と `condition_value2` は varchar 型なので、数値もテキストとして格納する（例: `120`、`1`）
3. **不要な condition_value は空文字または NULL**: `EnemyOutpostBreakDown` や `PlayerOutpostBreakDown` などの条件では値不要なので空文字か NULL にする
4. **TimeOver の value1 は秒数（整数）**: ミリ秒ではなく秒で指定する点に注意
5. **idはmst_stage_idと同じ値を設定**: 実データのパターンから、同一ステージの複数条件は同じ id を共有している
6. **クライアントクラス**: `MstStageEndConditionData`（`GLOW.Core.Data.Data`名前空間）。`StageEndType` および `StageEndConditionType` は `GLOW.Core.Domain.Constants` の enum として定義されている
7. **通常ステージでは不要**: 標準的な拠点破壊ルールのステージにはレコードを追加する必要がない。特殊ルールが必要なステージのみ設定する

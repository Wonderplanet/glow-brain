# MstStageEnhanceRewardParam 詳細説明

> CSVパス: `projects/glow-masterdata/MstStageEnhanceRewardParam.csv`

---

## 概要

強化クエスト（Enhance Quest）ステージクリア時の報酬量を算出するための係数設定テーブル。プレイヤーがステージで獲得したスコアに基づき、適用するコイン報酬量を段階的に定義する。

- スコアが `min_threshold_score` 以上のレコードのうち、最もスコアが高いものが適用される
- `id` は連番の整数値で管理されている
- 実データでは16段階のスコア閾値が定義されており、スコアに応じたコイン報酬量とサイズタイプを設定している

---

## 全カラム一覧

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---|---|---|---|---|
| id | varchar(255) | 不可 | - | 連番ID（例: `1`、`2`、...） |
| min_threshold_score | bigint unsigned | 不可 | - | このパラメータが適用されるスコアの下限値 |
| coin_reward_amount | bigint unsigned | 不可 | - | 付与するコイン報酬量 |
| coin_reward_size_type | varchar(255) | 不可 | `` | 報酬表示サイズタイプ（`CoinRewardSizeType` enum参照） |
| release_key | bigint unsigned | 不可 | - | リリースキー |

**ユニークインデックス**: `uk_min_threshold_score`（`min_threshold_score`）

---

## CoinRewardSizeType（コイン報酬サイズタイプ）

| 値 | 説明 |
|---|---|
| `Small` | 小サイズの報酬演出 |
| `Medium` | 中サイズの報酬演出 |
| `Large` | 大サイズの報酬演出 |

---

## 命名規則 / IDの生成ルール

`id` は 1 から始まる連番の整数値（文字列型として格納）。スコア下限値が重複しないようにユニーク制約が設定されている。

---

## 他テーブルとの連携

このテーブルは直接外部キーを持たないが、強化クエストステージのスコア計算ロジックから参照される。

| 関連テーブル | 説明 |
|---|---|
| `mst_stages` | 強化クエストステージでプレイヤーが取得したスコアに対してこのテーブルのパラメータを適用する |

---

## 実データ例

### 例1: スコア低帯のパラメータ設定

```
id | min_threshold_score | coin_reward_amount | coin_reward_size_type | release_key
1  | 1                   | 100                | Small                 | 202509010
2  | 5000                | 250                | Small                 | 202509010
3  | 10000               | 400                | Small                 | 202509010
4  | 20000               | 600                | Small                 | 202509010
5  | 30000               | 800                | Small                 | 202509010
```

スコア1以上でコイン100、5000以上で250、10000以上で400と段階的に増加。

### 例2: スコア高帯のパラメータ設定

```
id | min_threshold_score | coin_reward_amount | coin_reward_size_type | release_key
6  | 40000               | 1000               | Medium                | 202509010
7  | 50000               | 1250               | Medium                | 202509010
8  | 75000               | 1500               | Medium                | 202509010
9  | 100000              | 2000               | Large                 | 202509010
10 | 150000              | 2500               | Large                 | 202509010
```

スコア4万以上からMediumサイズ、10万以上からLargeサイズの演出が適用される。

---

## 設定時のポイント

1. **スコア閾値は一意**: `min_threshold_score` にはユニーク制約があるため、同じ値を複数設定することはできない
2. **下限値の意味を正確に理解する**: スコアが `min_threshold_score` 以上かつ次のレコードの閾値未満のプレイヤーにそのパラメータが適用される
3. **必ず最小スコア（1以上）のレコードを設定**: スコアが極めて低い場合でもマッチするレコードが存在するよう、`min_threshold_score = 1` のようなフォールバックレコードを必ず含める
4. **coin_reward_size_type はUIとの整合性に注意**: サイズタイプはUIの演出に影響するため、報酬量に合わせた適切なサイズを選択する
5. **クライアントクラス**: `MstStageEnhanceRewardParamData`（`GLOW.Core.Data.Data`名前空間）。`id`・`minThresholdScore`・`coinRewardAmount`・`coinRewardSizeType` が配信される
6. **段階設定の粒度を検討**: スコア帯を細かく設定することで報酬量の変化が滑らかになるが、設定レコードが増えるため運用コストも考慮する

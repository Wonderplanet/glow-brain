# MstPvpRank 詳細説明

> CSVパス: `projects/glow-masterdata/MstPvpRank.csv`

---

## 概要

`MstPvpRank` は**PvPのランク体系を定義するマスタテーブル**。ランク区分（Bronze/Silver/Gold/Platinum）とレベルの組み合わせごとに、ランク到達に必要なバトルポイントの下限値・勝利時のポイント加算量・敗北時のポイント減算量・アイコンアセットを設定する。

現在は4ランク × 最大5レベル（Bronze: 0〜4、Silver/Gold/Platinum: 1〜4）、合計17種類のランクが定義されている。Bronzeのみレベル0が存在し、初期状態（ランク未到達）を表している。

### ゲームプレイへの影響

- `required_lower_score`: プレイヤーのバトルポイントがこの値以上の場合、このランクに到達する（各ランクの下限スコア）
- `win_add_point`: バトル勝利時に加算されるポイント。ランクが上がるほど多い（5 → 10 → 20 → 30）
- `lose_sub_point`: バトル敗北時に減算されるポイント。Bronzeは0（降格なし）、Silver以上は減算あり
- `asset_key`: ランクアイコンのアセットID（現状は全てNULL）

---

## 全カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|---------|-----------|----|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `rank_class_type` | enum('Bronze','Silver','Gold','Platinum') | 不可 | - | PVPランク区分 |
| `rank_class_level` | int unsigned | 不可 | 1 | ランクレベル（Bronze: 0〜4、他: 1〜4） |
| `required_lower_score` | bigint | 不可 | 1 | このランクへの到達に必要な最小スコア |
| `win_add_point` | int unsigned | 不可 | 0 | 勝利時のスコア加算値 |
| `lose_sub_point` | int unsigned | 不可 | 0 | 敗北時のスコア減算値 |
| `asset_key` | varchar(255) | 不可 | '' | ランクアイコンアセットキー |

---

## ランク構成一覧

| ランク区分 | レベル | 必要スコア下限 | 勝利加算 | 敗北減算 |
|----------|--------|-------------|---------|---------|
| Bronze | 0 | 0 | 5 | 0 |
| Bronze | 1 | 5 | 5 | 0 |
| Bronze | 2 | 10 | 5 | 0 |
| Bronze | 3 | 20 | 5 | 0 |
| Bronze | 4 | 30 | 5 | 0 |
| Silver | 1 | 50 | 10 | 5 |
| Silver | 2 | 100 | 10 | 5 |
| Silver | 3 | 150 | 10 | 5 |
| Silver | 4 | 200 | 10 | 5 |
| Gold | 1 | 400 | 20 | 15 |
| Gold | 2 | 600 | 20 | 15 |
| Gold | 3 | 800 | 20 | 15 |
| Gold | 4 | 1000 | 20 | 15 |
| Platinum | 1 | 1250 | 30 | 25 |
| Platinum | 2 | 1500 | 30 | 25 |
| Platinum | 3 | 1750 | 30 | 25 |
| Platinum | 4 | 2000 | 30 | 25 |

---

## 命名規則 / IDの生成ルール

- `id`: `default_{ランク区分}_{ランクレベル}` 形式（例: `default_Bronze_0`、`default_Platinum_4`）

---

## 他テーブルとの連携

```
MstPvpRank
  └─ (rank_class_type, rank_class_level) → MstPvpMatchingScoreRange（マッチングスコア範囲）
  └─ (rank_class_type, rank_class_level) → MstPvpDummy（ダミー対戦相手の選択）
  └─ ranking_min_pvp_rank_class ← MstPvp.ranking_min_pvp_rank_class（ランキング対象最小区分）
```

ユニーク制約 `mst_pvp_ranks_unique` が `(rank_class_type, rank_class_level)` に設定されており、同一の組み合わせで複数レコードを持てない。

---

## 実データ例

**パターン1: Bronzeランク（降格なし）**

| ENABLE | id | rank_class_type | rank_class_level | required_lower_score | win_add_point | lose_sub_point |
|--------|-----|----------------|----------------|---------------------|--------------|---------------|
| e | default_Bronze_0 | Bronze | 0 | 0 | 5 | 0 |
| e | default_Bronze_4 | Bronze | 4 | 30 | 5 | 0 |

**パターン2: 上位ランク（降格あり、加算量大）**

| ENABLE | id | rank_class_type | rank_class_level | required_lower_score | win_add_point | lose_sub_point |
|--------|-----|----------------|----------------|---------------------|--------------|---------------|
| e | default_Gold_1 | Gold | 1 | 400 | 20 | 15 |
| e | default_Platinum_4 | Platinum | 4 | 2000 | 30 | 25 |

---

## 設定時のポイント

1. **required_lower_scoreは連続して設定**: 各ランク・レベルの `required_lower_score` が昇順で重複なく設定されることを確認する。スコアの境界値が正しくないとランク判定がずれる
2. **Bronzeのlose_sub_pointは0**: ブロンズはランクが下がらない設計になっている。初心者保護のため意図的な仕様
3. **win_add_pointはランクごとに統一**: 現状は Bronze=5、Silver=10、Gold=20、Platinum=30 と区分ごとに一定値
4. **ユニーク制約に注意**: (rank_class_type, rank_class_level) の組み合わせは重複不可
5. **asset_keyは後から追加可能**: 現状はNULLだがランクアイコンのアセットが用意できたら設定する
6. **MstPvpMatchingScoreRangeと合わせて設計**: ランク体系を変更した場合はマッチングスコア範囲テーブルも合わせて更新が必要

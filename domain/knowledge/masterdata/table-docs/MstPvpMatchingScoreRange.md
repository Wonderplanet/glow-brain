# MstPvpMatchingScoreRange 詳細説明

> CSVパス: `projects/glow-masterdata/MstPvpMatchingScoreRange.csv`

---

## 概要

`MstPvpMatchingScoreRange` は**PvPマッチングに使用するスコア幅の設定テーブル**。ランク区分（`rank_class_type`）とランクレベル（`rank_class_level`）の組み合わせごとに、格上・同格・格下相手とのマッチングに使用するスコア加算範囲（最小値・最大値）を定義する。

マッチングシステムはプレイヤーのバトルポイントに対して、各カテゴリのスコア範囲をランダムに足し込むことで対戦相手の探索スコアを生成し、マッチングを実現する。スコアが負の値になりうる設計（格下相手には負のスコア加算）になっている。

### ゲームプレイへの影響

- `upper_rank_max_score` / `upper_rank_min_score`: 格上相手を探す際のスコア加算範囲（正の値、自分より高いスコアを探す）
- `same_rank_max_score` / `same_rank_min_score`: 同格相手を探す際のスコア加算範囲（小さい値）
- `lower_rank_max_score` / `lower_rank_min_score`: 格下相手を探す際のスコア加算範囲（負の値、自分より低いスコアを探す）
- ランクが上がるほど格上・格下の幅が広がる傾向がある

---

## 全カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|---------|-----------|----|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `rank_class_type` | enum('Bronze','Silver','Gold','Platinum') | 不可 | - | ランク区分 |
| `rank_class_level` | int | 不可 | - | ランクレベル（Bronze: 0〜4、Silver/Gold/Platinum: 1〜4） |
| `upper_rank_max_score` | int | 不可 | - | 格上スコア加算上限 |
| `upper_rank_min_score` | int | 不可 | - | 格上スコア加算下限 |
| `same_rank_max_score` | int | 不可 | - | 同格スコア加算上限 |
| `same_rank_min_score` | int | 不可 | - | 同格スコア加算下限 |
| `lower_rank_max_score` | int | 不可 | - | 格下スコア加算上限 |
| `lower_rank_min_score` | int | 不可 | - | 格下スコア加算下限 |

---

## スコア幅の読み方

```
プレイヤーのバトルポイント = 100 のとき（例: Bronze_1）
  格上マッチング: 100 + [41〜70] のスコアを持つ相手を探す
  同格マッチング: 100 + [0〜40] のスコアを持つ相手を探す
  格下マッチング: 100 + [-9〜-1] のスコアを持つ相手を探す
```

---

## 命名規則 / IDの生成ルール

- `id`: `{ランク区分}_{ランクレベル}` 形式（例: `Bronze_0`、`Silver_1`、`Platinum_2`）
- Bronze のみレベル0から始まり（`Bronze_0`〜`Bronze_4`）、Silver・Gold・Platinum はレベル1から始まる（`Silver_1`〜`Silver_4`）

---

## 他テーブルとの連携

```
MstPvpRank
  └─ (rank_class_type, rank_class_level) → MstPvpMatchingScoreRange の絞り込みキー

PvPマッチングサーバーロジック
  └─ プレイヤーのランク + レベル → MstPvpMatchingScoreRange からスコア範囲を取得
  └─ スコア範囲から対戦相手（実プレイヤー or ダミー）を選択
```

---

## 実データ例

**パターン1: Bronzeランクのスコア範囲**

| ENABLE | id | rank_class_type | rank_class_level | upper_rank_max_score | upper_rank_min_score | same_rank_max_score | same_rank_min_score | lower_rank_max_score | lower_rank_min_score |
|--------|-----|----------------|----------------|---------------------|---------------------|--------------------|--------------------|---------------------|---------------------|
| e | Bronze_0 | Bronze | 0 | 70 | 46 | 45 | 1 | 0 | -4 |
| e | Bronze_1 | Bronze | 1 | 70 | 41 | 40 | 0 | -1 | -9 |
| e | Bronze_4 | Bronze | 4 | 70 | 31 | 30 | -20 | -21 | -49 |

**パターン2: 高ランクのスコア範囲（幅が広い）**

| ENABLE | id | rank_class_type | rank_class_level | upper_rank_max_score | upper_rank_min_score | same_rank_max_score | same_rank_min_score | lower_rank_max_score | lower_rank_min_score |
|--------|-----|----------------|----------------|---------------------|---------------------|--------------------|--------------------|---------------------|---------------------|
| e | Gold_1 | Gold | 1 | 200 | 100 | 99 | -50 | -51 | -150 |
| e | Platinum_1 | Platinum | 1 | 300 | 200 | 199 | -50 | -51 | -200 |

---

## 設定時のポイント

1. **スコア範囲の連続性を確保**: `upper_rank_min_score` の直下が `same_rank_max_score`、`same_rank_min_score` の直下が `lower_rank_max_score` となるように設計する（例: upper_min=41、same_max=40）
2. **ランクが上がるほど幅を広げる**: 高ランクプレイヤーは相手が少ないため、スコア幅を広げてマッチングしやすくする
3. **格下スコアは負の値**: 格下相手を探すために負の値を加算する。絶対値が大きいほど格下の幅が広がる
4. **全ランク・全レベルのレコードが必要**: Bronze(0〜4)、Silver/Gold/Platinum(1〜4) の全組み合わせを設定する
5. **クライアント側クラスは存在しない**: このテーブルはサーバー側のマッチングロジック専用で、クライアントには配信されない
6. **バランス調整時は他テーブルとの整合性を確認**: `MstPvpRank` のランク構成と合わせて調整する

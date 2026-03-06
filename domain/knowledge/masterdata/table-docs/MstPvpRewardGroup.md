# MstPvpRewardGroup 詳細説明

> CSVパス: `projects/glow-masterdata/MstPvpRewardGroup.csv`

---

## 概要

`MstPvpRewardGroup` は**PVP（プレイヤー対プレイヤー）の報酬グループ定義テーブル**。PVP終了後にどの条件でどの報酬グループを付与するかを管理する。

1レコードが「特定のPVP（`mst_pvp_id`）における報酬カテゴリと条件値の組み合わせ」を表し、実際の報酬内容は別テーブル（`MstPvpReward`）と紐付いて解決される。

### ゲームへの影響

- **ランキング報酬** (`reward_category = Ranking`): シーズン終了時の最終順位に応じた報酬。`condition_value` に「1位」「100位以内」などの順位閾値を文字列で指定する。
- **ランククラス報酬** (`reward_category = RankClass`): プレイヤーが到達したランククラス（ブロンズ・シルバーなど）に応じた報酬。`condition_value` にクラス名を指定する。
- **トータルスコア報酬** (`reward_category = TotalScore`): 累計スコアが一定値に達したときの報酬。`condition_value` に数値を指定する。

### テーブル連携図

```
MstPvp（PVP設定）
  └─ id → MstPvpRewardGroup.mst_pvp_id（1:N）
              └─ id → MstPvpReward（報酬内容）
```

---

## 全カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|---------|-----------|----|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー。命名規則は後述 |
| `release_key` | bigint | 不可 | 1 | リリースキー。マスタデータのバージョン管理に使用 |
| `mst_pvp_id` | varchar(16) | 不可 | - | 対象PVPのID（`mst_pvps.id`） |
| `reward_category` | enum | 不可 | - | 報酬カテゴリ。`Ranking` / `RankClass` / `TotalScore` |
| `condition_value` | varchar(255) | 不可 | - | 報酬条件値。カテゴリによって意味が変わる |

---

## PvpRewardCategory（enum）

| 値 | 説明 | `condition_value` の意味 |
|----|------|------------------------|
| `Ranking` | シーズン最終ランキング順位による報酬 | 順位の閾値（例: `1`, `50`, `100`, `1000`） |
| `RankClass` | 到達ランククラスによる報酬 | ランククラスID（例: `default_Bronze_1`, `default_Silver_1`） |
| `TotalScore` | 累計スコアによる報酬 | スコアの閾値（数値文字列） |

---

## 命名規則 / IDの生成ルール

`id` は以下のパターンで命名する:

```
{mst_pvp_id}_reward_{reward_category_lowercase}_{連番}
```

例:
- `default_reward_ranking_1` → default_pvp の Ranking カテゴリ 1番目
- `default_reward_rank_1` → default_pvp の RankClass カテゴリ 1番目

ユニーク制約: `(mst_pvp_id, reward_category, condition_value)` の組み合わせは一意。

---

## 他テーブルとの連携

| 連携先テーブル | 結合キー | 用途 |
|-------------|--------|------|
| `MstPvp` | `MstPvpRewardGroup.mst_pvp_id = MstPvp.id` | PVP設定情報の取得 |
| `MstPvpReward` | `MstPvpReward.mst_pvp_reward_group_id = MstPvpRewardGroup.id` | 実際の報酬内容（アイテム・ダイヤ等）を解決 |

---

## 実データ例

### パターン1: ランキング報酬グループ（1位・50位以内）

```csv
ENABLE,id,release_key,mst_pvp_id,reward_category,condition_value
e,default_reward_ranking_1,202509010,default_pvp,Ranking,1
e,default_reward_ranking_4,202509010,default_pvp,Ranking,50
```

- `condition_value = 1` は1位のみ対象
- `condition_value = 50` は50位以内全員が対象

### パターン2: ランククラス報酬グループ

```csv
ENABLE,id,release_key,mst_pvp_id,reward_category,condition_value
e,default_reward_rank_1,202509010,default_pvp,RankClass,default_Bronze_1
e,default_reward_rank_2,202509010,default_pvp,RankClass,default_Bronze_2
```

- `condition_value` はランクのクラス名文字列で指定する

---

## 設定時のポイント

1. **`mst_pvp_id` は `MstPvp.csv` に存在するIDを指定する**。存在しないIDを指定するとPVP終了時に報酬が解決できなくなる。
2. **`reward_category` によって `condition_value` の形式が異なる**。`Ranking` は整数文字列、`RankClass` はランク名文字列を使用する。
3. **ユニーク制約** `(mst_pvp_id, reward_category, condition_value)` を意識して設定する。同一PVP・カテゴリ・条件値の重複はエラーになる。
4. **ランキング閾値は「以内」の上限として機能する**。例えば `condition_value = 100` のレコードは「100位以内」全員が対象。同じ範囲に複数のグループを設定しないように注意する。
5. **`release_key` は対応するリリース日に合わせて設定する**。新PVPシーズン追加時は適切なリリースキーを割り当てる。
6. **1つのPVPに対して複数の報酬グループを設定できる**。Ranking・RankClass・TotalScore のすべてのカテゴリを同時に設定可能。
7. **実際の報酬内容は `MstPvpReward` テーブルで管理する**。このテーブルはグループの定義のみ行い、報酬の中身（アイテム・ダイヤ等）は別途設定が必要。

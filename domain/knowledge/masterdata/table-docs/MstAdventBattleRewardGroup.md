# MstAdventBattleRewardGroup 詳細説明

> CSVパス: `projects/glow-masterdata/MstAdventBattleRewardGroup.csv`

---

## 概要

降臨バトルの報酬グループを管理するテーブル。プレイヤーがスコア達成・ランキング・ランク到達・レイド累計スコアなどの条件を満たした場合に付与される報酬の条件をグループ単位で定義する。

このテーブルは `mst_advent_battle_rewards`（実際の報酬内容）の親テーブルにあたる。1つの降臨バトルに対して複数の報酬グループを設定でき、それぞれ `reward_category` と `condition_value` で「どの条件を達成したら」を定義する。

---

## 全カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---------|----|----|------|
| ENABLE | varchar | YES | 有効フラグ（`e` = 有効） |
| id | varchar(255) | YES | UUID。報酬グループを一意に識別するID |
| mst_advent_battle_id | varchar(255) | YES | 紐付く降臨バトルの `mst_advent_battles.id` |
| reward_category | enum | YES | 報酬カテゴリー（`MaxScore` / `Ranking` / `Rank` / `RaidTotalScore`） |
| condition_value | varchar(255) | YES | 報酬を受け取るための条件値（スコア・順位・ランクタイプなど） |
| release_key | bigint | YES | リリースキー |

---

## reward_category（報酬カテゴリー）

| 値 | 説明 | condition_value の意味 |
|----|------|----------------------|
| `MaxScore` | 最大スコア達成報酬 | 達成が必要なスコア値（数値文字列） |
| `Ranking` | ランキング報酬 | 対象となる順位範囲（例: "1"、"2-10" 等） |
| `Rank` | ランク（段位）到達報酬 | ランクタイプ名（例: "Bronze"、"Silver"） |
| `RaidTotalScore` | レイド累計スコア報酬 | 累計スコアの閾値（数値文字列） |

---

## 命名規則 / IDの生成ルール

- `id`: `{mst_advent_battle_id}_reward_group_{連番（5桁）}`（例: `quest_raid_kai_reward_group_00001_01`）
- `condition_value` に応じた連番で管理する

---

## 他テーブルとの連携

| テーブル | 関係 | 説明 |
|---------|------|------|
| `mst_advent_battles` | 多対1 | 対象の降臨バトル。`mst_advent_battle_id` で参照 |
| `mst_advent_battle_rewards` | 1対多 | 実際の報酬内容。`mst_advent_battle_reward_group_id` で紐付く |

---

## 実データ例

### パターン1: スコア達成（MaxScore）報酬グループ

```
ENABLE: e
id: quest_raid_kai_reward_group_00001_01
mst_advent_battle_id: quest_raid_kai_00001
reward_category: MaxScore
condition_value: 5000
release_key: 202509010

ENABLE: e
id: quest_raid_kai_reward_group_00001_02
mst_advent_battle_id: quest_raid_kai_00001
reward_category: MaxScore
condition_value: 7500
release_key: 202509010

ENABLE: e
id: quest_raid_kai_reward_group_00001_03
mst_advent_battle_id: quest_raid_kai_00001
reward_category: MaxScore
condition_value: 10000
release_key: 202509010
```

### パターン2: 高スコア達成の段階的報酬

```
ENABLE: e
id: quest_raid_kai_reward_group_00001_04
mst_advent_battle_id: quest_raid_kai_00001
reward_category: MaxScore
condition_value: 15000
release_key: 202509010

ENABLE: e
id: quest_raid_kai_reward_group_00001_05
mst_advent_battle_id: quest_raid_kai_00001
reward_category: MaxScore
condition_value: 30000
release_key: 202509010
```

---

## 設定時のポイント

1. **condition_value のデータ型**: `condition_value` は文字列型だが、`MaxScore` や `RaidTotalScore` では数値を文字列として格納する。`Ranking` では順位範囲文字列を、`Rank` ではランクタイプ名を格納する。
2. **スコア閾値の段階的設計**: `MaxScore` の場合、プレイヤーが徐々に目標を達成できるよう、低い値から高い値へ段階的に設定する。
3. **mst_advent_battle_rewards との対応**: このグループの `id` が `mst_advent_battle_rewards.mst_advent_battle_reward_group_id` として参照される。グループ作成時は必ず対応する報酬レコードも作成する。
4. **reward_category の選択**: 開催するイベントのルールに合わせてカテゴリーを選択する。スコアチャレンジ型は `MaxScore`、ランキング型は `Ranking`、ランク段位型は `Rank` が基本。
5. **グループIDの命名統一**: 1つの降臨バトル内で報酬グループ名が重複しないよう、連番を用いて一意のIDを作成する。
6. **複数カテゴリーの組み合わせ**: 1つの降臨バトルで `MaxScore` と `Rank` を混在させることも可能。プレイヤーに複数の報酬獲得経路を用意できる。

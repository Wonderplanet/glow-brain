# MstMissionAchievementDependency 詳細説明

> CSVパス: `projects/glow-masterdata/MstMissionAchievementDependency.csv`

---

## 1. 概要

`MstMissionAchievementDependency` は**アチーブメントミッション同士の開放順序を定義するテーブル**。あるミッションの前提として別ミッションの達成を要求する場合に設定する。グループIDでミッションをまとめ、`unlock_order` の順序に従って段階的に開放される。

### ゲームプレイへの影響

- `group_id` でまとめられたミッション群は、`unlock_order` が小さい順に順番に開放される
- `unlock_order = 1` のミッションが達成されると、`unlock_order = 2` のミッションが開放される
- 同じ `group_id` 内で `unlock_order` は重複禁止（ユニーク制約あり）
- 同一ミッションは1グループにしか属せない（`group_id + mst_mission_achievement_id` がユニーク）

### テーブル間の関係

```
MstMissionAchievementDependency（依存関係設定）
  └─ mst_mission_achievement_id → MstMissionAchievement.id（達成順序に従って開放）

group_id = "Achievement_LoginCount"
  ├─ unlock_order 1: achievement_2_4（10日ログイン）
  ├─ unlock_order 2: achievement_2_5（20日ログイン）
  ├─ unlock_order 3: achievement_2_6（30日ログイン）
  └─ ...（順番に開放）
```

---

## 2. 全カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー（CSVでは連番整数） |
| `release_key` | int | 不可 | 1 | リリースキー |
| `group_id` | varchar(255) | 不可 | - | 依存関係グルーピングID |
| `mst_mission_achievement_id` | varchar(255) | 不可 | - | 対象アチーブメントID（`mst_mission_achievements.id`） |
| `unlock_order` | int unsigned | 不可 | - | グループ内での開放順序（1から始まる連番） |

### ユニーク制約

| インデックス名 | カラム | 説明 |
|---|---|---|
| `uk_group_id_mst_mission_achievement_id` | (group_id, mst_mission_achievement_id) | 1ミッションは1グループにのみ属する |
| `uk_group_id_unlock_order` | (group_id, unlock_order) | グループ内の開放順序は重複不可 |

---

## 3. 他テーブルとの連携

### 参照するテーブル

| テーブル | カラム | 説明 |
|---------|--------|------|
| `mst_mission_achievements` | `mst_mission_achievement_id → mst_mission_achievements.id` | 開放順を制御されるアチーブメント |

---

## 6. 実データ例

### パターン1: ログイン累計の段階的開放

| id | release_key | group_id | mst_mission_achievement_id | unlock_order |
|---|---|---|---|---|
| 1 | 202509010 | Achievement_LoginCount | achievement_2_4 | 1 |
| 2 | 202509010 | Achievement_LoginCount | achievement_2_5 | 2 |
| 3 | 202509010 | Achievement_LoginCount | achievement_2_6 | 3 |
| 4 | 202509010 | Achievement_LoginCount | achievement_2_7 | 4 |
| 5 | 202509010 | Achievement_LoginCount | achievement_2_8 | 5 |

- `achievement_2_4`（10日ログイン）を達成すると `achievement_2_5`（20日ログイン）が開放される
- 同グループ内で完全な順序チェーンを形成する

### パターン2: グループを跨いだ独立した依存チェーン

各 `group_id` は独立した開放チェーンを表す。例えば「SNSフォロー系」「ログイン系」「クエスト系」などを別々のグループIDで管理することで、独立して進行させることができる。

---

## 7. 設定時のポイント

- `group_id` は意味のある名前をつける（例: `Achievement_LoginCount`、`Achievement_QuestClear`）
- `unlock_order` は 1 から始まる連番で設定し、欠番を作らない
- 依存関係を設定するミッションは `MstMissionAchievement.unlock_criterion_type` に依存条件を設定しなくてよい（このテーブルで管理する）
- グループ内のミッション数に制限はないが、開放チェーンが長すぎるとプレイヤーの可視性が悪くなる
- 既存グループに新しいミッションを末尾に追加する場合は `unlock_order` を最大値+1 にする
- `group_id + mst_mission_achievement_id` はユニーク制約があるため、同じミッションを複数グループに登録することはできない
- 依存関係が不要なミッション（常時表示・個別開放）はこのテーブルにレコードを追加しない

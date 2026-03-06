# MstMissionLimitedTermDependency 詳細説明

> CSVパス: `projects/glow-masterdata/MstMissionLimitedTermDependency.csv`

---

## 1. 概要

`MstMissionLimitedTermDependency` は**期間限定ミッション同士の開放順序を定義するテーブル**。あるミッションの前提として別ミッションの達成を要求する場合に設定する。グループIDでミッションをまとめ、`unlock_order` の順序に従って段階的に開放される。アチーブメントやイベントミッションの依存関係テーブルと同じ構造。

### ゲームプレイへの影響

- `group_id` でまとめられたミッション群は `unlock_order` が小さい順に順番に開放される
- `unlock_order = 1` のミッションが達成されると `unlock_order = 2` のミッションが開放される
- 期間限定ミッションの段階的な難易度上昇を実現する

### テーブル間の関係

```
MstMissionLimitedTermDependency（依存関係設定）
  └─ mst_mission_limited_term_id → MstMissionLimitedTerm.id（開放順序で制御する期間限定ミッション）

group_id = "group1"
  ├─ unlock_order 1: limited_term_1（5回挑戦）
  ├─ unlock_order 2: limited_term_2（10回挑戦）
  ├─ unlock_order 3: limited_term_3（20回挑戦）
  └─ unlock_order 4: limited_term_4（30回挑戦）
```

---

## 2. 全カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー（CSVでは連番整数） |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `group_id` | varchar(255) | 不可 | - | 依存関係グルーピングID |
| `mst_mission_limited_term_id` | varchar(255) | 不可 | - | 対象期間限定ミッションID（`mst_mission_limited_terms.id`） |
| `unlock_order` | int unsigned | 不可 | - | グループ内での開放順序（1から始まる連番） |

### ユニーク制約

| インデックス名 | カラム | 説明 |
|---|---|---|
| `group_id_mst_mission_limited_term_id_unique` | (group_id, mst_mission_limited_term_id) | 1ミッションは1グループにのみ属する |
| `group_id_unlock_order_unique` | (group_id, unlock_order) | グループ内の開放順序は重複不可 |

---

## 5. 他テーブルとの連携

### 参照するテーブル

| テーブル | カラム | 説明 |
|---------|--------|------|
| `mst_mission_limited_terms` | `mst_mission_limited_term_id → mst_mission_limited_terms.id` | 開放順を制御される期間限定ミッション |

---

## 6. 実データ例

### 現行データ

現行の `MstMissionLimitedTermDependency.csv` はデータが 0 件（ヘッダーのみ）の状態。

```
ENABLE, id, release_key, group_id, mst_mission_limited_term_id, unlock_order
```

期間限定ミッションの依存関係機能は実装済みだが、現時点では開放順制御を必要とするミッション設定がない状態。（`MstMissionLimitedTerm` の各ミッションは `progress_group_key` でグループ化されているが、依存順序は設定されていない。）

---

## 7. 設定時のポイント

- `group_id` には意味のある名前を使う（例: `MstMissionLimitedTerm` の `progress_group_key` と合わせると分かりやすい）
- `unlock_order` は 1 から始まる連番で設定し、欠番を作らない
- `(group_id, mst_mission_limited_term_id)` の組み合わせはユニーク制約があるため、同じミッションを複数グループに登録できない
- 依存関係が不要なミッション（全て同時開放）はこのテーブルにレコードを追加しない
- `release_key` は対応する `MstMissionLimitedTerm` のリリースキーと統一する
- アチーブメント系（`MstMissionAchievementDependency`）、イベント系（`MstMissionEventDependency`）と同じ設計パターンのため、それらの設定例を参考にすると良い

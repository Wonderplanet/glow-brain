# MstMissionEventDependency 詳細説明

> CSVパス: `projects/glow-masterdata/MstMissionEventDependency.csv`

---

## 1. 概要

`MstMissionEventDependency` は**イベントミッション同士の開放順序を定義するテーブル**。あるイベントミッションの前提として別ミッションの達成を要求する場合に設定する。グループIDでミッションをまとめ、`unlock_order` の順序に従って段階的に開放される。アチーブメントミッションの依存関係テーブル（`MstMissionAchievementDependency`）と同じ構造。

### ゲームプレイへの影響

- `group_id` でまとめられたミッション群は `unlock_order` が小さい順に順番に開放される
- `unlock_order = 1` のミッションが達成されると `unlock_order = 2` のミッションが開放される
- 同一グループ内での `unlock_order` および `(group_id, mst_mission_event_id)` の組み合わせはユニーク制約がある
- イベントミッションの段階的な難易度上昇を表現するために使用する

### テーブル間の関係

```
MstMissionEventDependency（依存関係設定）
  └─ mst_mission_event_id → MstMissionEvent.id（開放順序で制御するイベントミッション）

group_id = "event_kai_00001_1"
  ├─ unlock_order 1: event_kai_00001_1（グレード2達成後）
  ├─ unlock_order 2: event_kai_00001_2（グレード3達成後）
  ├─ unlock_order 3: event_kai_00001_3（グレード4達成後）
  └─ unlock_order 4: event_kai_00001_4（グレード5達成後）
```

---

## 2. 全カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー（CSVでは連番整数） |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `group_id` | varchar(255) | 不可 | - | 依存関係グルーピングID |
| `mst_mission_event_id` | varchar(255) | 不可 | - | 対象イベントミッションID（`mst_mission_events.id`） |
| `unlock_order` | int unsigned | 不可 | - | グループ内での開放順序（1から始まる連番） |
| `備考` | varchar | 可 | - | CSVのみの運営メモ列（DBには存在しない） |

### ユニーク制約

| インデックス名 | カラム | 説明 |
|---|---|---|
| `group_id_mst_mission_event_id_unique` | (group_id, mst_mission_event_id) | 1ミッションは1グループにのみ属する |
| `group_id_unlock_order_unique` | (group_id, unlock_order) | グループ内の開放順序は重複不可 |

---

## 5. 他テーブルとの連携

### 参照するテーブル

| テーブル | カラム | 説明 |
|---------|--------|------|
| `mst_mission_events` | `mst_mission_event_id → mst_mission_events.id` | 開放順を制御されるイベントミッション |

---

## 6. 実データ例

### パターン1: グレードアップ段階の連続開放

| id | release_key | group_id | mst_mission_event_id | unlock_order |
|---|---|---|---|---|
| 1 | 202509010 | event_kai_00001_1 | event_kai_00001_1 | 1 |
| 2 | 202509010 | event_kai_00001_1 | event_kai_00001_2 | 2 |
| 3 | 202509010 | event_kai_00001_1 | event_kai_00001_3 | 3 |
| 4 | 202509010 | event_kai_00001_1 | event_kai_00001_4 | 4 |

- グレード2→3→4→5へのアップグレードをシリーズとして開放順制御

### パターン2: レベルアップ段階の連続開放

| id | release_key | group_id | mst_mission_event_id | unlock_order |
|---|---|---|---|---|
| 5 | 202509010 | event_kai_00001_5 | event_kai_00001_5 | 1 |
| 6 | 202509010 | event_kai_00001_5 | event_kai_00001_6 | 2 |
| 7 | 202509010 | event_kai_00001_5 | event_kai_00001_7 | 3 |

- Lv.20→Lv.30→Lv.40へのレベルアップをシリーズとして開放順制御

---

## 7. 設定時のポイント

- `group_id` はそのグループの意味が分かる名前にする（例: イベントIDを含む形式）
- `unlock_order` は 1 から始まる連番で設定し、欠番を作らない
- `(group_id, mst_mission_event_id)` の組み合わせはユニーク制約があるため、同じミッションを複数グループに登録できない
- 同一グループ内では `unlock_order` が重複禁止
- 依存関係が不要な独立ミッションはこのテーブルにレコードを追加しない
- `備考` 列はDBスキーマに存在しないCSV専用の列。管理用メモとして活用する
- `release_key` はそのイベントのリリースキーと統一する

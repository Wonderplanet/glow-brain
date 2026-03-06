# MstQuestEventBonusSchedule 詳細説明

> CSVパス: `projects/glow-masterdata/MstQuestEventBonusSchedule.csv`

---

## 概要

`MstQuestEventBonusSchedule` は**クエストにおけるイベントボーナス期間の設定テーブル**。特定のクエスト（主にレイドクエスト）に対して、イベントボーナスグループを紐付け、そのボーナスが有効になる期間を管理する。

`event_bonus_group_id` を介して `MstStageEventBonusUnit`（ステージイベントボーナスユニット）と連携し、期間中に特定ユニットを使用することでボーナスが発動する仕組みを実現する。

### ゲームへの影響

- イベント期間中、対象クエストに `event_bonus_group_id` と一致するボーナスユニットを編成すると、ステージのスコアや報酬にボーナスが付与される。
- **有効期間** (`start_at` / `end_at`) でイベント開催期間を精密に制御する。終了後はボーナスが非表示になる。
- レイドイベントはほぼ毎月開催されており、本テーブルにその開催スケジュールが蓄積されていく。

### テーブル連携図

```
MstQuest（クエスト）
  └─ id → MstQuestEventBonusSchedule.mst_quest_id（1:N）
              └─ event_bonus_group_id → MstStageEventBonusUnit.event_bonus_group_id
                                            （ボーナス対象ユニット一覧）
```

---

## 全カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|---------|-----------|----|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー（連番整数） |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `mst_quest_id` | varchar(255) | 不可 | `""` | ボーナス対象クエストID（`mst_quests.id`） |
| `event_bonus_group_id` | varchar(255) | 不可 | `""` | ボーナスグループID（`mst_stage_event_bonus_units.event_bonus_group_id`） |
| `start_at` | timestamp | 不可 | - | ボーナス有効開始日時 |
| `end_at` | timestamp | 不可 | - | ボーナス有効終了日時 |

---

## 命名規則 / IDの生成ルール

`id` は連番整数で管理する。

`event_bonus_group_id` の命名規則:
```
raid_{作品略称}_{連番}
```

例: `raid_kai_00001` → 怪獣８号のレイドイベント第1回

---

## 他テーブルとの連携

| 連携先テーブル | 結合キー | 用途 |
|-------------|--------|------|
| `MstQuest` | `MstQuestEventBonusSchedule.mst_quest_id = MstQuest.id` | クエスト情報を取得 |
| `MstStageEventBonusUnit` | `event_bonus_group_id` が共通 | ボーナス対象ユニット一覧を取得 |

---

## 実データ例

### パターン1: 怪獣８号レイドイベント（第1回）

```csv
ENABLE,id,mst_quest_id,event_bonus_group_id,start_at,end_at,release_key
e,1,quest_raid_kai_00001,raid_kai_00001,2025-10-01 12:00:00,2025-10-08 11:59:59,202509010
```

- 1週間程度の短期イベント
- `raid_kai_00001` グループに属するユニットへのボーナスが有効

### パターン2: 複数イベントの連続スケジュール

```csv
ENABLE,id,mst_quest_id,event_bonus_group_id,start_at,end_at,release_key
e,2,quest_raid_spy1_00001,raid_spy1_00001,2025-10-16 15:00:00,2025-10-22 11:59:59,202510010
e,3,quest_raid_dan1_00001,raid_dan1_00001,2025-10-31 12:00:00,2025-11-06 14:59:59,202510020
e,4,quest_raid_mag1_00001,raid_mag1_00001,2025-11-22 15:00:00,2025-11-28 14:59:59,202511010
```

- 月に複数回のレイドイベントが設定されている
- それぞれ別の `mst_quest_id` と `event_bonus_group_id` を使用

---

## 設定時のポイント

1. **`event_bonus_group_id` は `MstStageEventBonusUnit` の `event_bonus_group_id` と一致させる**。一致しないと対象ユニットが取得できずボーナスが発動しない。
2. **`mst_quest_id` はレイドクエスト（`quest_type = Event` のレイド系）が主な対象**。対象クエストが存在することを事前に確認する。
3. **イベント期間は重複させないのが原則**。同一クエストに対して複数のイベントボーナス期間が重なると、どちらが適用されるか不定になる可能性がある。
4. **`start_at` / `end_at` はJST（日本標準時）で設定する場合が多い**。実際の開始時刻は `start_at` の通りに管理される。
5. **新規レイドイベント追加時はセットで作成するデータが複数ある**。`MstQuestEventBonusSchedule` レコードの他に、`MstStageEventBonusUnit` にもボーナスユニット設定が必要。
6. **`id` は連番整数で管理する**。既存の最大IDを確認してから採番する。

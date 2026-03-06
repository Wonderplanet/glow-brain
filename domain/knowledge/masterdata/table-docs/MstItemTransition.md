# MstItemTransition 詳細説明

> CSVパス: `projects/glow-masterdata/MstItemTransition.csv`

---

## 1. 概要

`MstItemTransition` は**アイテム詳細画面からの画面遷移先を設定するテーブル**。各アイテムに対して最大2つの遷移先（transition1、transition2）を定義し、ユーザーがアイテム詳細を見たときに「どの画面へ移動するか」を制御する。

### ゲームプレイへの影響

- アイテム詳細画面の「使い方」「入手方法」などのボタン押下時の遷移先を設定する
- `transition1` は主要な遷移先（例: メインクエスト、探索）、`transition2` は副次的な遷移先（例: ショップ）として使い分ける
- 遷移先が不要なアイテムは `None` を指定する
- `transition1_mst_id` / `transition2_mst_id` で遷移先の特定レコードを指定できる（クエストIDなど）

### テーブル間の関係

```
MstItem（アイテム本体）
  └─ mst_item_id → MstItemTransition.mst_item_id（1:1）

MstItemTransition
  └─ transition1_mst_id → 各テーブルのID（クエストIDなど）
  └─ transition2_mst_id → 各テーブルのID（ショップアイテムIDなど）
```

---

## 2. 全カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー（UUID、CSVでは連番整数） |
| `mst_item_id` | varchar(255) | 不可 | - | 対象アイテムID（`mst_items.id`） |
| `transition1` | enum | 不可 | - | 主遷移先タイプ。`ItemTransitionType` |
| `transition1_mst_id` | varchar(255) | 不可 | - | 主遷移先の特定レコードID（遷移先により意味が変わる） |
| `transition2` | enum | 可 | - | 副遷移先タイプ。`ItemTransitionType` |
| `transition2_mst_id` | varchar(255) | 可 | - | 副遷移先の特定レコードID |
| `release_key` | bigint | 不可 | 1 | リリースキー。マスタデータのバージョン管理に使用 |

---

## 3. ItemTransitionType（遷移先タイプ）

| 値 | 遷移先 | transition_mst_id の意味 |
|---|---|---|
| `None` | 遷移なし | 設定不要（NULL） |
| `MainQuest` | メインクエスト | クエストID |
| `EventQuest` | イベントクエスト | クエストID |
| `ShopItem` | ショップアイテム | ショップアイテムID |
| `Pack` | パック | パックID |
| `Achievement` | アチーブメント | アチーブメントID |
| `LoginBonus` | ログインボーナス | - |
| `DailyMission` | デイリーミッション | - |
| `WeeklyMission` | ウィークリーミッション | - |
| `Patrol` | 探索 | - |
| `ExchangeShop` | 交換ショップ | 交換ショップID |
| `Etc` | その他 | 任意 |

---

## 4. 命名規則 / IDの生成ルール

CSVでは `id` カラムに連番整数（1, 2, 3...）が設定されている。DBではUUID形式となるが、CSVの管理上は連番整数で問題ない。

---

## 5. 他テーブルとの連携

### 参照するテーブル

| テーブル | カラム | 説明 |
|---------|--------|------|
| `mst.items` | `mst_item_transition.mst_item_id → mst_items.id` | 遷移設定を紐づけるアイテム |

### 参照されるテーブル

このテーブルは他テーブルから直接参照されることはない。クライアントが `mst_item_id` をキーに遷移設定を取得する。

---

## 6. 実データ例

### 遷移なし（ピースアイテム）

| id | mst_item_id | transition1 | transition1_mst_id | transition2 | transition2_mst_id | release_key |
|---|---|---|---|---|---|---|
| 1 | piece_glo_00001 | None | NULL | None | NULL | 999999999 |
| 2 | piece_glo_00002 | None | NULL | None | NULL | 999999999 |

- ピースアイテムは特定の遷移先を持たないため `None` を設定

### 遷移あり（ツール・メモリーアイテム）

| id | mst_item_id | transition1 | transition1_mst_id | transition2 | transition2_mst_id | release_key |
|---|---|---|---|---|---|---|
| 32 | tool_glo_00001 | Patrol | NULL | None | NULL | 202509010 |
| 34 | memory_glo_00001 | MainQuest | quest_main_spy_normal_1 | ShopItem | NULL | 202509010 |

- `tool_glo_00001`（探索ツール）は探索画面へ誘導
- `memory_glo_00001`（メモリーアイテム）はメインクエストとショップの2箇所へ誘導

---

## 7. 設定時のポイント

- アイテムと1:1対応なので、`MstItem` にアイテムを追加した場合は必ず `MstItemTransition` にも対応レコードを追加する
- 遷移先が不要なアイテムは `transition1 = None`、`transition2 = None` を設定し `mst_id` は NULL にする
- `transition1_mst_id` は遷移先タイプが `None` の場合 NULL を設定する（設定があっても無視されるが混乱を避けるため）
- クエスト遷移（`MainQuest` / `EventQuest`）では `transition1_mst_id` にクエストIDを設定することで特定クエストへダイレクト遷移できる
- `Patrol`（探索）など固定画面への遷移は `mst_id` が不要なため NULL のまま設定する
- `transition2` は補助的な遷移先として「入手方法がショップにもある場合」などに活用する
- release_key は `999999999`（永続）またはリリースバージョンのキーを設定する
- クライアントクラス: `MstItemTransitionData.cs`（`ItemTransitionType` enum を使用）

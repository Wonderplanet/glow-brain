# MstArtworkPanelMission 詳細説明

> CSVパス: `projects/glow-masterdata/MstArtworkPanelMission.csv`

---

## 概要

原画パネルミッションの実施期間・対象イベント・対象原画・初期開放フラグメントを定義するマスタテーブル。
ユーザーが原画のかけら（fragment）を収集してパネルを埋めるミッション機能の設定を管理する。
1つのミッションが1つのイベント期間・原画・開放フラグメントに対応する。

---

## 全カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---|---|---|---|
| ENABLE | varchar | YES | 有効フラグ（`e` = 有効） |
| id | varchar(255) | YES | レコードID（主キー） |
| release_key | bigint | YES | リリースキー（デフォルト: 1） |
| mst_artwork_id | varchar(255) | YES | 対象の原画ID（`mst_artworks.id`） |
| mst_event_id | varchar(255) | YES | 対象イベントID（`mst_events.id`） |
| initial_open_mst_artwork_fragment_id | varchar(255) | NO | ミッション開始時に初期開放する原画のかけらID（`mst_artwork_fragments.id`） |
| start_at | timestamp | YES | ミッション開始日時 |
| end_at | timestamp | YES | ミッション終了日時 |

---

## 命名規則 / IDの生成ルール

IDは `artwork_panel_{イベント略称}_{連番2桁}` の形式が一般的。

例:
- `artwork_panel_f05anniv_01` → 5周年アニバーサリーイベントの1番目のパネルミッション
- `artwork_panel_test` → テスト用データ

---

## 他テーブルとの連携

| 参照先テーブル | カラム | 内容 |
|---|---|---|
| `mst_artworks` | `mst_artwork_id` | 対象原画の参照 |
| `mst_events` | `mst_event_id` | 開催イベントの参照 |
| `mst_artwork_fragments` | `initial_open_mst_artwork_fragment_id` | 初期開放フラグメントの参照 |

---

## 実データ例

**例1: テスト用パネルミッション**

| id | release_key | mst_artwork_id | mst_event_id | initial_open_mst_artwork_fragment_id | start_at | end_at |
|---|---|---|---|---|---|---|
| artwork_panel_test | 999999999 | artwork_gom_0003 | test | gom_c_0004 | 2025-11-30 12:00:00 | 2026-10-01 15:00:00 |

**例2: 5周年アニバーサリーイベント用パネルミッション**

| id | release_key | mst_artwork_id | mst_event_id | initial_open_mst_artwork_fragment_id | start_at | end_at |
|---|---|---|---|---|---|---|
| artwork_panel_f05anniv_01 | 202603020 | artwork_event_sur_0003 | event_glo_00002 | artwork_fragment_event_sur_00201 | 2026-03-16 15:00:00 | 2026-04-13 10:59:59 |

---

## 設定時のポイント

1. `mst_artwork_id` には `mst_artworks` テーブルに存在する原画IDを設定する。
2. `mst_event_id` には `mst_events` テーブルに存在するイベントIDを設定する。イベントの開催期間とミッション期間を合わせることが推奨される。
3. `initial_open_mst_artwork_fragment_id` は任意項目（NULL可）。設定するとミッション開始時にそのフラグメントが自動で開放される。
4. `start_at` と `end_at` はJST（日本標準時）基準で入力し、UTCに変換してDBに格納される場合は9時間差に注意する。
5. `end_at` は `start_at` より後の日時を設定すること。
6. テスト用データには `release_key` として `999999999` を使用し、`mst_event_id` に `test` を設定する慣習がある。
7. 本番用データには対応するリリースキー（例: `202603020`）を設定する。
8. 1つのイベントに複数の原画パネルミッションを設定する場合は連番（`_01`, `_02`...）でIDを管理する。

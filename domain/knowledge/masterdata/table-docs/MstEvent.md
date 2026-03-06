# MstEvent 詳細説明

> CSVパス: `projects/glow-masterdata/MstEvent.csv`
> i18n CSVパス: `projects/glow-masterdata/MstEventI18n.csv`

---

## 概要

`MstEvent` は**ゲーム内イベント（いいジャン祭など）の基本設定テーブル**。

期間限定で開催されるイベントの基本情報（関連作品・開催期間・表示設定・アセット）を管理する。1イベント = 1レコードで、関連するクエスト・ガチャ・ミッションなどの各テーブルがこのイベントIDを参照する形で構成される。

`mst_events_i18n` テーブルでイベント名と吹き出しテキストの多言語対応を行う。

CSVの行数は15件（2026年3月現在）。

### ゲームプレイへの影響

- **`mst_series_id`**: イベントの関連作品。ホーム画面でのイベントバナー・UI装飾に使用される
- **`is_displayed_series_logo`**: イベントTOP画面での作品ロゴ表示有無。`1` = 表示、`0` = 非表示
- **`is_displayed_jump_plus`**: 「作品を読む」ボタン（JUMP+連携）の表示有無
- **`start_at`** / **`end_at`**: イベント開催期間。この期間外ではイベントクエストへのアクセスが制限される
- **`asset_key`**: イベントバナー画像やTOP画面の背景アセット

### 関連テーブルとの構造図

```
MstEvent（イベント基本設定）
  └─ id → MstEventI18n.mst_event_id（多言語名称・吹き出しテキスト）
  └─ id → MstEventDisplayUnit.mst_quest_id 経由（イベントTOP表示ユニット）
  └─ id → MstEventDisplayReward.mst_event_id（目玉報酬表示、現在未使用）
  └─ mst_series_id → MstSeries.id（作品マスタ）

イベント関連テーブル（event_bonus_group_id を介した連携）
  └─ MstEventBonusUnit.event_bonus_group_id（ボーナス対象ユニット）
  └─ MstQuestEventBonusSchedule（クエストとボーナスグループの対応）
```

---

## 全カラム一覧

### mst_events カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー。イベントID |
| `mst_series_id` | varchar(255) | 不可 | - | 関連作品ID（`mst_series.id`） |
| `is_displayed_series_logo` | tinyint | 不可 | 0 | 作品ロゴの表示有無。`1` = 表示 |
| `is_displayed_jump_plus` | tinyint | 不可 | 0 | JUMP+読書ボタンの表示有無。`1` = 表示 |
| `start_at` | timestamp | 不可 | - | イベント開始日時（UTC） |
| `end_at` | timestamp | 不可 | - | イベント終了日時（UTC） |
| `asset_key` | varchar(255) | 不可 | - | イベントアセットキー |
| `release_key` | bigint | 不可 | - | リリースキー。マスタデータのバージョン管理に使用 |

### MstEventI18n カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー |
| `release_key` | bigint | 不可 | - | リリースキー |
| `mst_event_id` | varchar(255) | 不可 | - | 参照先イベントID（`mst_events.id`） |
| `language` | enum | 不可 | - | 言語コード。`ja` のみ対応 |
| `name` | varchar(255) | 不可 | - | イベント名称（例: 「SPY×FAMILYいいジャン祭」） |
| `balloon` | varchar(255) | 不可 | - | ホーム画面吹き出し内テキスト（改行は `\n`） |

**ユニーク制約**: `(mst_event_id, language)` の組み合わせが重複不可

---

## 命名規則 / IDの生成ルール

| 項目 | 規則 | 例 |
|------|------|----|
| `id` | `event_{作品略称}_{5桁連番}` | `event_kai_00001`, `event_spy_00001` |
| `asset_key` | `event_{作品略称}_{5桁連番}` または特別アセット | `event_kai_00001`, `event_l05anniv_00001` |
| i18n の `id` | `{event_id}_ja` | `event_kai_00001_ja` |
| `balloon` | 改行を `\n` で表現 | `「{イベント名}」\n開催中！` |

---

## 他テーブルとの連携

| テーブル | 参照方向 | 用途 |
|---------|---------|------|
| `mst_events_i18n` | `id` ← `mst_event_id` | イベント名・吹き出しテキストの多言語対応 |
| `mst_series` | `mst_series_id` → `id` | 関連作品マスタ |
| `mst_event_display_units` | `id` ← `mst_quest_id`（クエスト経由） | イベントTOP画面に表示するユニット |
| `mst_event_display_rewards` | `id` ← `mst_event_id` | 目玉報酬表示（現在未使用） |
| `mst_stage_event_settings` | `event_bonus_group_id` 経由 | イベントステージの設定 |

---

## 実データ例

### パターン1: 作品イベント（怪獣8号いいジャン祭）

```
[MstEvent.csv]
ENABLE: e
id: event_kai_00001
mst_series_id: kai
is_displayed_series_logo: 1
is_displayed_jump_plus: 1
start_at: 2025-09-22 11:00:00
end_at: 2025-10-22 11:59:59
asset_key: event_kai_00001
release_key: 202509010

[MstEventI18n.csv]
ENABLE: e
release_key: 202509010
id: event_kai_00001_ja
mst_event_id: event_kai_00001
language: ja
name: 怪獣８号いいジャン祭
balloon: 怪獣８号いいジャン祭\n開催中!
```

標準的なキャラクターイベント。作品ロゴとJUMP+ボタンを表示し、約1ヶ月間開催。

### パターン2: GLOWオリジナルイベント（周年記念）

```
[MstEvent.csv]
ENABLE: e
id: event_glo_00003
mst_series_id: kai
is_displayed_series_logo: 1
is_displayed_jump_plus: 1
start_at: 2026-03-30 15:00:00
end_at: 2026-04-13 10:59:59
asset_key: event_l05anniv_00001
release_key: 202603025
```

周年記念イベントはGLOW固有の特別アセット（`event_l05anniv_00001`）を使用。IDは `event_glo_00003` で、特定作品に紐づかないオリジナルイベントとして設定。

---

## 設定時のポイント

1. **`start_at` / `end_at` はUTCで設定する**。日本時間（JST = UTC+9）で設計する場合は9時間引いた値を設定すること。例: JST 2025-09-22 20:00 → UTC 2025-09-22 11:00。

2. **イベントIDは `event_{作品略称}_{5桁連番}` の形式を基本とする**。同一作品の2回目以降のイベントは `event_spy_00002` のように連番を増やす。GLOWオリジナルイベントは `event_glo_{連番}` を使用。

3. **`asset_key` はイベント専用のアセット名を設定する**。基本は `id` と同一の値（`event_kai_00001`）だが、周年記念などの特別なアセットを使用する場合は異なるキーを指定できる。

4. **`is_displayed_jump_plus` は作品がJUMP+に掲載されている場合のみ `1` を設定する**。GLOW固有キャラクター（`mst_series_id = glo`）や掲載のない作品は `0` にする。

5. **i18n の `balloon` フィールドは改行を `\n` で表現する**。ホーム画面のキャラクター吹き出しに表示されるため、1〜2行で収まるように設計する。

6. **イベント追加時は関連テーブル（`MstEventBonusUnit`、`MstEventDisplayUnit` 等）も同時に設定する**。イベント本体だけでは機能が動作しない。

7. **`release_key` はリリーススケジュールに合わせて設定する**。既存データでは `YYYYMM0NN` 形式のリリースキーを使用している（例: `202509010` = 2025年9月の第1回リリース）。

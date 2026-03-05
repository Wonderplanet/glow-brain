# イベント基本情報 マスタデータ設定手順書

## 概要

イベント開催に必要な基本情報を設定する手順書。新規シリーズ・イベントの登録から、ホーム表示設定まで一括して管理する。

- **report.md 対応セクション**: `### 1. イベント機能`

---

## 対象テーブル一覧と設定順序

| 作業順 | テーブル名 | 役割 | 必須/任意 |
|-------|-----------|------|---------|
| 1 | MstSeries | シリーズ定義（初回時のみ） | 条件付き必須 |
| 2 | MstSeriesI18n | シリーズ多言語名 | 条件付き必須 |
| 3 | MstEvent | イベント本体 | 必須 |
| 4 | MstEventI18n | イベント多言語名・吹き出し | 必須 |
| 5 | MstEventBonusUnit | イベントボーナスユニット | 必須 |
| 6 | MstEventDisplayUnit | クエスト別表示ユニット | 任意 |
| 7 | MstEventDisplayUnitI18n | 表示ユニット多言語情報 | 任意 |

---

## 前提条件・依存関係

- **MstUnit の登録完了が前提** → `02_unit.md` を先に実施
- MstSeries は同一シリーズの第2回以降のイベントでは追加不要（既存 series_id を再利用）
- MstEventBonusUnit の `event_bonus_group_id` は MstAdventBattle と共有される場合がある（`06_advent-battle.md` 参照）

---

## report.md から読み取る情報チェックリスト

- [ ] イベント ID（例: `event_you_00001`）
- [ ] シリーズ ID（例: `you`）- 新規かどうか確認
- [ ] イベント名（日本語）
- [ ] 開催期間（start_at / end_at）
- [ ] シリーズロゴ表示有無（`is_displayed_series_logo`）
- [ ] ジャンプ+表示有無（`is_displayed_jump_plus`）
- [ ] ボーナスユニット一覧と bonus_percentage（通常 UR:20%, SSR:10%, SR:10%）

---

## テーブル別設定手順

### MstSeries（シリーズ定義）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| id | シリーズ略称（英字小文字） | `you` |
| asset_key | id と同じ | `you` |
| release_key | 今回のリリースキー | `202602015` |
| jump_plus_url | 公式 URL（report.md に記載） | `https://shonenjumpplus.com/...` |
| banner_asset_key | シリーズバナー用アセットキー | `you` |

**過去データ参照クエリ（masterdata-explorer）**

```duckdb
SELECT * FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/MstSeries.csv');
```

**よくある設定パターン**
- id・asset_key・banner_asset_key はすべて同じ略称で統一
- jump_plus_url は report.md の「シリーズ情報」に記載されていることが多い
- 既存シリーズの場合は登録不要

---

### MstEvent（イベント本体）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | `event_{series_id}_{連番5桁}` | `event_you_00001` |
| mst_series_id | シリーズ ID | `you` |
| is_displayed_series_logo | report.md 参照（0 or 1） | `1` |
| is_displayed_jump_plus | report.md 参照（0 or 1） | `1` |
| start_at | 開催開始日時（UTC） | `2026-02-02 15:00:00` |
| end_at | 開催終了日時（UTC） | `2026-03-02 10:59:59` |
| asset_key | id と同じ | `event_you_00001` |
| release_key | 今回のリリースキー | `202602015` |

**過去データ参照クエリ（masterdata-explorer）**

```duckdb
SELECT id, mst_series_id, is_displayed_series_logo, is_displayed_jump_plus,
       start_at, end_at, release_key
FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/MstEvent.csv');
```

**よくある設定パターン**
- id = asset_key が基本
- 日時は JST で 15:00 開始 → UTC は `06:00:00` を引いた値

---

### MstEventI18n（イベント多言語名）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| release_key | 今回のリリースキー | `202602015` |
| id | `{mst_event_id}_{language}` | `event_you_00001_ja` |
| mst_event_id | 対応する MstEvent.id | `event_you_00001` |
| language | 言語コード（通常 `ja`） | `ja` |
| name | イベント表示名 | `幼稚園WARS いいジャン祭` |
| balloon | ホーム画面吹き出しテキスト（改行は `\n`） | `幼稚園WARS いいジャン祭\n開催中！` |

**過去データ参照クエリ**

```duckdb
SELECT * FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/MstEventI18n.csv');
```

---

### MstEventBonusUnit（イベントボーナスユニット）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | 連番（最終 ID + 1 から） | `61` |
| mst_unit_id | ボーナス対象ユニット ID | `chara_you_00001` |
| bonus_percentage | ボーナス倍率（%）通常: UR=20, SSR=10, SR=10 | `20` |
| event_bonus_group_id | `raid_{series}_{連番}_{連番}` | `raid_you1_00001` |
| is_pick_up | ピックアップ表示（NULL or 1） | `NULL` |
| release_key | 今回のリリースキー | `202602015` |

**過去データ参照クエリ**

```duckdb
SELECT id, mst_unit_id, bonus_percentage, event_bonus_group_id, release_key
FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/MstEventBonusUnit.csv');
```

**よくある設定パターン**
- `event_bonus_group_id` は MstAdventBattle の `event_bonus_group_id` と同一グループ名を使う
- UR: 20%, SSR: 10%, SR: 10% が標準
- id は全テーブル通算で採番（`domain/knowledge/masterdata/ID割り振りルール.csv` 参照）

---

### MstEventDisplayUnit（クエスト別表示ユニット）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | `{quest_id}{連番2桁}` | `quest_event_you1_1day01` |
| mst_quest_id | 対応するクエスト ID | `quest_event_you1_1day` |
| mst_unit_id | 表示するユニット ID | `chara_you_00001` |
| release_key | 今回のリリースキー | `202602015` |

**過去データ参照クエリ**

```duckdb
SELECT * FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/MstEventDisplayUnit.csv');
```

---

## 検証方法

- masterdata-csv-validator で整合性確認
  - MstEvent.mst_series_id → MstSeries.id が存在するか
  - MstEventI18n.mst_event_id → MstEvent.id が存在するか
  - MstEventBonusUnit.mst_unit_id → MstUnit.id が存在するか
- 開催期間の UTC/JST 変換ミスがないか確認

---

## 参照リソース

- DBスキーマ: `projects/glow-server/api/database/schema/exports/master_tables_schema.json`
- 利用スキル: `masterdata-explorer`（カラム確認・DuckDB クエリ）, `masterdata-csv-validator`（検証）
- 過去リリース: `domain/raw-data/masterdata/released/202602015/tables/MstEvent.csv`

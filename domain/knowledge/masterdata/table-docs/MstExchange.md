# MstExchange 詳細説明

> CSVパス: `projects/glow-masterdata/MstExchange.csv`
> i18n CSVパス: `projects/glow-masterdata/MstExchangeI18n.csv`

---

## 概要

`MstExchange` は**交換所の開催設定テーブル**。交換所の種類・開催期間・ラインナップグループをまとめ、1レコードが1つの交換所インスタンスに対応する。常設の通常交換所からイベント限定の交換所まで、すべての交換所をこのテーブルで管理する。

### ゲームプレイへの影響

- **ExchangeTradeType** によって交換所の種類（通常コイン交換所・キャラかけらBOX交換所・イベント交換所）が決まり、UI上の表示タブも変わる。
- **start_at / end_at** で交換所の公開期間を制御する。`end_at` が `NULL` の場合は無期限常設。
- **lineup_group_id** で `MstExchangeLineup` のグループを参照し、実際の交換アイテム一覧を決定する。
- **display_order** で交換所一覧画面における表示順を制御する（値が大きいほど後ろに表示）。

### 関連テーブルとの構造図

```
MstExchange（交換所の開催設定）
  ├─ mst_event_id → MstEvent.id（イベント紐付け、NULLの場合は常設）
  └─ lineup_group_id → MstExchangeLineup.group_id（交換アイテム一覧）
         └─ MstExchangeLineup.id → MstExchangeCost.mst_exchange_lineup_id（コスト）
         └─ MstExchangeLineup.id → MstExchangeReward.mst_exchange_lineup_id（報酬）

MstExchangeI18n（多言語名称・アセット）
  └─ mst_exchange_id → MstExchange.id
```

---

## 全カラム一覧

### mst_exchanges カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー。交換所の一意識別子 |
| `mst_event_id` | varchar(255) | 可 | - | 紐付けるイベントID（`mst_events.id`）。常設交換所はNULL |
| `mst_series_id` | varchar(255) | 可 | - | 紐付けるシリーズID（`mst_series.id`）。未使用の場合はNULL |
| `exchange_trade_type` | varchar(255) | 不可 | - | 交換所の種類（`ExchangeTradeType` enum） |
| `release_key` | bigint | 不可 | 1 | リリースキー。マスタデータのバージョン管理に使用 |
| `start_at` | timestamp | 不可 | - | 開催開始日時 |
| `end_at` | timestamp | 可 | - | 開催終了日時。NULLの場合は無期限 |
| `lineup_group_id` | varchar(255) | 不可 | - | ラインナップグループID（`MstExchangeLineup.group_id` と対応） |
| `display_order` | int unsigned | 不可 | 0 | 交換所一覧の表示順（昇順） |

### MstExchangeI18n カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー（整数連番） |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `mst_exchange_id` | varchar(255) | 不可 | - | 対応する交換所ID（`mst_exchanges.id`） |
| `language` | varchar(50) | 不可 | ja | 言語コード |
| `name` | varchar(255) | 不可 | - | 交換所名（UI表示用） |
| `asset_key` | varchar(255) | 不可 | - | バナー画像などのアセットキー |

---

## ExchangeTradeType（交換所種別）

| 値 | 説明 |
|----|------|
| `NormalExchangeTrade` | 通常交換所。コインを消費してアイテムを交換する常設コンテンツ |
| `EventExchangeTrade` | イベント交換所。イベントメダルなど期間限定通貨で交換する |
| `CharacterFragmentExchangeTrade` | キャラかけらBOX交換所。キャラのかけらをBOXアイテムに交換する常設コンテンツ |

---

## 命名規則 / IDの生成ルール

| 種類 | 命名パターン | 例 |
|------|------------|-----|
| 通常交換所 | `normal_{連番}` | `normal_01` |
| キャラかけらBOX交換所 | 固定ID | `chara_piece` |
| イベント交換所 | `event_{イベントコード}_{連番}` | `event_osh_00001_01` |
| lineup_group_id | `{exchange_id}_lineup` | `normal_01_lineup` |

---

## 他テーブルとの連携

| 連携先テーブル | カラム | 関係 |
|-------------|-------|------|
| `mst_events` | `mst_event_id` → `id` | イベント開催情報の紐付け（N:1） |
| `mst_exchange_lineups` | `lineup_group_id` → `group_id` | 交換アイテム一覧（1:N） |
| `mst_exchanges_i18n` | `id` → `mst_exchange_id` | 多言語名称・アセット（1:N） |

---

## 実データ例

**パターン1: 常設通常交換所**
```
ENABLE: e
id: normal_01
mst_event_id: NULL
exchange_trade_type: NormalExchangeTrade
start_at: 2025-12-22 11:30:00
end_at: NULL
lineup_group_id: normal_01_lineup
display_order: 1000
release_key: 202512015
```
- `end_at` がNULLのため無期限常設
- `display_order: 1000` で後方に表示（イベント交換所より後ろ）

**パターン2: イベント限定交換所**
```
ENABLE: e
id: event_hut_00001_01
mst_event_id: event_hut_00001
exchange_trade_type: EventExchangeTrade
start_at: 2026-03-02 15:00:00
end_at: 2026-04-03 10:59:59
lineup_group_id: event_hut_00001_01_lineup
display_order: 1
release_key: 202603010
```
- `mst_event_id` でイベントと紐付け
- `display_order: 1` で交換所一覧の先頭に表示

---

## 設定時のポイント

1. **end_at の NULL 設定**: 常設交換所（通常・キャラかけらBOX）は `end_at` をNULLにする。イベント交換所は必ず終了日時を設定する。
2. **lineup_group_id の命名規則**: `{id}_lineup` の形式で統一する。対応する `MstExchangeLineup` の `group_id` と完全一致させる必要がある。
3. **display_order の設計**: イベント交換所は小さい値（1〜100）、常設交換所は大きい値（1000以上）を設定することでイベントを優先表示させる運用が一般的。
4. **i18nとの対応**: 1つの `MstExchange` レコードに対して、必ず対応する `MstExchangeI18n` レコードを言語分作成する。`asset_key` は交換所バナー画像の識別子として使用する。
5. **mst_event_id の扱い**: `EventExchangeTrade` でも `mst_event_id` がNULLの場合がある（イベントに紐付かない期間限定交換所）。NULLの場合も正常動作する。
6. **mst_series_id**: 現状DBスキーマには定義があるがCSVでは未使用。シリーズ限定交換所向けの予備カラム。
7. **release_key の管理**: リリースキーはリリース日（`YYYYMMXXX` 形式）を使用し、マスタデータのバッチ適用単位を管理する。

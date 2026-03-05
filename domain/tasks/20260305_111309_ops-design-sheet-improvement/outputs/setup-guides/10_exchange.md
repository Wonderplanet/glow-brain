# 交換所 マスタデータ設定手順書

## 概要

イベント交換所の設定手順書。イベントアイテムを使って特定アイテムと交換できる機能のマスタデータをカバーする。

- **report.md 対応セクション**: `機能別データ詳細 > 交換所`
- **対応リリース例**: `202512020`（交換所あり）、`202602015`（交換所なし）

> **注意**: 交換所はリリースによって含まれない場合がある。report.md に「交換所」の記載がない場合は本手順書をスキップ。

---

## 対象テーブル一覧と設定順序

| 作業順 | テーブル名 | 役割 | 必須/任意 |
|-------|-----------|------|---------|
| 1 | MstExchange | 交換所定義 | 必須 |
| 2 | MstExchangeI18n | 交換所多言語名 | 必須 |
| 3 | MstExchangeLineup | 交換ラインナップ | 必須 |
| 4 | MstExchangeCost | 交換コスト | 必須 |
| 5 | MstExchangeReward | 交換報酬 | 必須 |

---

## 前提条件・依存関係

- **MstEvent の登録完了が前提**（`01_event.md` を先に実施）
- **MstItem の登録完了が前提**（交換コストに使用するアイテムが登録済みであること）
- 交換アイテムが Unit の場合は MstUnit も登録済みであること（`02_unit.md`）

---

## report.md から読み取る情報チェックリスト

- [ ] 交換所名（例: `いいジャン祭交換所`）
- [ ] 開催期間（start_at / end_at）
- [ ] 交換可能アイテム一覧（商品・交換回数・コスト）
- [ ] 使用するイベントコインアイテム ID

---

## テーブル別設定手順

### MstExchange（交換所定義）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | `{event_id}_{連番2桁}` | `event_osh_00001_01` |
| mst_event_id | 対応するイベント ID | `event_osh_00001` |
| exchange_trade_type | 交換種別（通常 `EventExchangeTrade`） | `EventExchangeTrade` |
| start_at | 開始日時（UTC） | `2026-01-01 00:00:00` |
| end_at | 終了日時（UTC） | `2026-02-02 10:59:59` |
| lineup_group_id | ラインナップグループ ID | `event_osh_00001_01_lineup` |
| display_order | 表示順 | `1` |
| release_key | 今回のリリースキー | `202512020` |

**過去データ参照クエリ（masterdata-explorer）**

```duckdb
SELECT id, mst_event_id, exchange_trade_type, start_at, end_at, lineup_group_id, display_order
FROM read_csv('domain/raw-data/masterdata/released/202512020/tables/MstExchange.csv');
```

---

### MstExchangeI18n（交換所多言語名）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | 連番（最終 ID + 1 から） | `3` |
| mst_exchange_id | 対応する MstExchange.id | `event_osh_00001_01` |
| language | 言語コード | `ja` |
| asset_key | バナーアセットキー | `osh_exchange_00001` |
| name | 交換所表示名 | `いいジャン祭交換所` |
| release_key | 今回のリリースキー | `202512020` |

---

### MstExchangeLineup（交換ラインナップ）

交換所で交換できる各商品を定義する。

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | `{lineup_group_id}_{連番5桁}` | `event_osh_00001_01_lineup_00001` |
| group_id | MstExchange.lineup_group_id と同値 | `event_osh_00001_01_lineup` |
| tradable_count | 交換可能回数（1=1回のみ） | `1` |
| display_order | 表示順（1 から連番） | `1` |
| release_key | 今回のリリースキー | `202512020` |

**過去データ参照クエリ**

```duckdb
SELECT id, group_id, tradable_count, display_order
FROM read_csv('domain/raw-data/masterdata/released/202512020/tables/MstExchangeLineup.csv')
ORDER BY group_id, display_order;
```

---

### MstExchangeCost（交換コスト）

各ラインナップの交換に必要なコストを定義する。

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | MstExchangeLineup.id と同じ | `event_osh_00001_01_lineup_00001` |
| mst_exchange_lineup_id | 対応する MstExchangeLineup.id | `event_osh_00001_01_lineup_00001` |
| cost_type | コスト種別（Item/Coin/Diamond/...） | `Item` |
| cost_id | コストアイテム ID（Coin/Diamond は NULL） | `item_glo_00001` |
| cost_amount | コスト量 | `10000` |
| release_key | 今回のリリースキー | `202512020` |

**過去データ参照クエリ**

```duckdb
SELECT id, mst_exchange_lineup_id, cost_type, cost_id, cost_amount
FROM read_csv('domain/raw-data/masterdata/released/202512020/tables/MstExchangeCost.csv')
ORDER BY id;
```

---

### MstExchangeReward（交換報酬）

各ラインナップで獲得できる報酬を定義する。

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | MstExchangeLineup.id と同じ | `event_osh_00001_01_lineup_00001` |
| mst_exchange_lineup_id | 対応する MstExchangeLineup.id | `event_osh_00001_01_lineup_00001` |
| resource_type | 報酬種別（Item/Unit/FreeDiamond/Coin） | `Item` |
| resource_id | リソース ID（Coin/Diamond は NULL） | `ticket_osh_10000` |
| resource_amount | 報酬量 | `1` |
| release_key | 今回のリリースキー | `202512020` |

**過去データ参照クエリ**

```duckdb
SELECT e.id, e.mst_exchange_lineup_id, e.resource_type, e.resource_id, e.resource_amount,
       c.cost_type, c.cost_id, c.cost_amount
FROM read_csv('domain/raw-data/masterdata/released/202512020/tables/MstExchangeReward.csv') e
JOIN read_csv('domain/raw-data/masterdata/released/202512020/tables/MstExchangeCost.csv') c
  ON e.mst_exchange_lineup_id = c.mst_exchange_lineup_id
ORDER BY e.id;
```

---

## 検証方法

- MstExchange.lineup_group_id → MstExchangeLineup.group_id が存在するか
- MstExchangeCost.mst_exchange_lineup_id → MstExchangeLineup.id が存在するか
- MstExchangeReward.mst_exchange_lineup_id → MstExchangeLineup.id が存在するか
- MstExchangeCost.cost_id（Item の場合）→ MstItem.id が存在するか
- display_order が連番になっているか

---

## 参照リソース

- DBスキーマ: `projects/glow-server/api/database/schema/exports/master_tables_schema.json`
- 利用スキル: `masterdata-explorer`, `masterdata-csv-validator`
- 過去リリース（交換所あり）: `domain/raw-data/masterdata/released/202512020/tables/`

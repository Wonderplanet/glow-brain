# MstExchangeCost 詳細説明

> CSVパス: `projects/glow-masterdata/MstExchangeCost.csv`

---

## 概要

`MstExchangeCost` は**交換所の各ラインナップアイテムに必要なコスト（消費リソース）を定義するテーブル**。1つのラインナップアイテム（`MstExchangeLineup`）に対して、コインまたはアイテムのいずれかを消費コストとして設定する。

### ゲームプレイへの影響

- **cost_type** によって消費するリソースの種類が決まる。`Coin` の場合はゲーム内通貨コインを消費し、`Item` の場合は指定したアイテムを消費する。
- **cost_amount** が交換に必要な消費数量。コイン交換であれば消費コイン数、アイテム交換であれば消費アイテム個数になる。
- `cost_type = Item` のとき、`cost_id` にアイテムIDを設定してどのアイテムを消費するかを指定する。イベントメダルなど期間限定通貨はアイテムとして管理される。

### 関連テーブルとの構造図

```
MstExchangeLineup（ラインナップ1件）
  └─ id → MstExchangeCost.mst_exchange_lineup_id（コスト定義）
              ├─ cost_type = Coin    → コイン消費（cost_id は NULL）
              └─ cost_type = Item   → アイテム消費（cost_id → mst_items.id）
```

---

## 全カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー。ラインナップIDと同じ値を使う慣習 |
| `mst_exchange_lineup_id` | varchar(255) | 不可 | - | 対応するラインナップID（`mst_exchange_lineups.id`） |
| `cost_type` | enum | 不可 | - | コストタイプ（`ExchangeCostType` enum） |
| `cost_id` | varchar(255) | 可 | - | コストアイテムID（`mst_items.id`）。`cost_type = Coin` の場合はNULL |
| `cost_amount` | int unsigned | 不可 | - | 必要消費数量 |
| `release_key` | bigint | 不可 | 1 | リリースキー |

---

## ExchangeCostType（コスト種別）

| 値 | 説明 |
|----|------|
| `Coin` | ゲーム内通貨コインを消費。`cost_id` はNULL |
| `Item` | アイテムを消費。`cost_id` に `mst_items.id` を設定する |

---

## 命名規則 / IDの生成ルール

| 種類 | 命名パターン | 例 |
|------|------------|-----|
| id | `{mst_exchange_lineup_id}` と同一値を使う慣習 | `normal_01_lineup_00001` |

---

## 他テーブルとの連携

| 連携先テーブル | カラム | 関係 |
|-------------|-------|------|
| `mst_exchange_lineups` | `mst_exchange_lineup_id` → `id` | コストを適用するラインナップ（N:1） |
| `mst_items` | `cost_id` → `id` | 消費アイテムの定義（N:1、Coin時はNULL） |

---

## 実データ例

**パターン1: コイン消費（通常交換所）**
```
ENABLE: e
id: normal_01_lineup_00001
mst_exchange_lineup_id: normal_01_lineup_00001
cost_type: Coin
cost_id: NULL
cost_amount: 30000
release_key: 202512015
```
- コイン30000を消費して交換
- `cost_id` はNULL（コイン消費はID指定不要）

**パターン2: アイテム消費（イベント交換所での想定）**
```
ENABLE: e
id: normal_01_lineup_00005
mst_exchange_lineup_id: normal_01_lineup_00005
cost_type: Coin
cost_id: NULL
cost_amount: 810
release_key: 202512015
```
- コイン810を消費して交換（小コストのアイテム）

---

## 設定時のポイント

1. **id とラインナップIDの統一**: `id` は `mst_exchange_lineup_id` と同じ値を設定するのが慣習。異なる値を設定しても動作するが、管理上統一する。
2. **Coin 時の cost_id**: `cost_type = Coin` のとき `cost_id` は必ずNULLにする。設定しても参照されない。
3. **Item 時の cost_id**: `cost_type = Item` のとき `cost_id` には有効な `mst_items.id` を設定する。存在しないIDを設定するとサーバー側でエラーになる。
4. **1ラインナップ1コスト**: 1つのラインナップIDに対してコストは1レコードのみ設定する。複数コスト（AND条件）はサポートされていない。
5. **イベントメダルの扱い**: イベントで使うメダル通貨は `MstItem` でアイテムとして管理されている。`cost_type = Item` かつ `cost_id = {medal_item_id}` で設定する。
6. **cost_amount の単位**: コインは枚数（整数）、アイテムは個数（整数）で設定する。小数は不可。

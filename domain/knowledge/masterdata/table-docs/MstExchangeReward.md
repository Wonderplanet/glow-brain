# MstExchangeReward 詳細説明

> CSVパス: `projects/glow-masterdata/MstExchangeReward.csv`

---

## 概要

`MstExchangeReward` は**交換所の各ラインナップアイテムで受け取れる報酬を定義するテーブル**。1つのラインナップ枠（`MstExchangeLineup`）に対して、どのリソース・アイテムを何個受け取れるかを設定する。

### ゲームプレイへの影響

- **resource_type** によって付与されるリソースの種類が決まる（コイン・プリズム・アイテム・称号・ユニット・アートワークなど）。
- **resource_id** でアイテムや称号などの具体的なIDを指定する。`Coin` や `FreeDiamond` など数量のみで決まるリソースはNULL。
- **resource_amount** が受け取れる数量。

### 関連テーブルとの構造図

```
MstExchangeLineup（ラインナップ1件）
  └─ id → MstExchangeReward.mst_exchange_lineup_id（報酬定義）
              ├─ resource_type = Coin         → コイン付与（resource_id はNULL）
              ├─ resource_type = FreeDiamond  → 無料プリズム付与
              ├─ resource_type = Item         → アイテム付与（resource_id → mst_items.id）
              ├─ resource_type = Emblem       → 称号付与（resource_id → mst_emblems.id）
              ├─ resource_type = Unit         → ユニット付与（resource_id → mst_units.id）
              └─ resource_type = Artwork      → アートワーク付与（resource_id → mst_artworks.id）
```

---

## 全カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー。ラインナップIDと同じ値を使う慣習 |
| `mst_exchange_lineup_id` | varchar(255) | 不可 | - | 対応するラインナップID（`mst_exchange_lineups.id`） |
| `resource_type` | enum | 不可 | - | 報酬タイプ（`ResourceType` enum） |
| `resource_id` | varchar(255) | 可 | - | 報酬対象のID。タイプによりNULL可 |
| `resource_amount` | int unsigned | 不可 | - | 報酬数量 |
| `release_key` | bigint | 不可 | 1 | リリースキー |

---

## ResourceType（報酬タイプ）

DBスキーマの enum 定義（交換所で使用されるもの）:

| 値 | 説明 | resource_id |
|----|------|-------------|
| `Coin` | ゲーム内通貨コイン | NULL |
| `FreeDiamond` | 無料プリズム（一次通貨） | NULL |
| `Item` | アイテム | `mst_items.id` |
| `Emblem` | 称号 | `mst_emblems.id` |
| `Unit` | ユニット | `mst_units.id` |
| `Artwork` | アートワーク | `mst_artworks.id` |

---

## 命名規則 / IDの生成ルール

| 種類 | 命名パターン | 例 |
|------|------------|-----|
| id | `{mst_exchange_lineup_id}` と同一値を使う慣習 | `normal_01_lineup_00001` |

---

## 他テーブルとの連携

| 連携先テーブル | カラム | 関係 |
|-------------|-------|------|
| `mst_exchange_lineups` | `mst_exchange_lineup_id` → `id` | 対応する交換枠（N:1） |
| `mst_items` | `resource_id` → `id` | 報酬アイテムの定義（N:1） |
| `mst_units` | `resource_id` → `id` | 報酬ユニットの定義（N:1） |
| `mst_emblems` | `resource_id` → `id` | 報酬称号の定義（N:1） |
| `mst_artworks` | `resource_id` → `id` | 報酬アートワークの定義（N:1） |

---

## 実データ例

**パターン1: アイテム（ガチャチケット）を報酬にする**
```
ENABLE: e
id: normal_01_lineup_00001
mst_exchange_lineup_id: normal_01_lineup_00001
resource_type: Item
resource_id: ticket_glo_00002
resource_amount: 1
release_key: 202512015
```
- ガチャチケット1枚をコイン30000で交換
- `resource_id` に `mst_items.id` を指定

**パターン2: アイテム（メモリー素材）を報酬にする**
```
ENABLE: e
id: normal_01_lineup_00005
mst_exchange_lineup_id: normal_01_lineup_00005
resource_type: Item
resource_id: memory_glo_00001
resource_amount: 30
release_key: 202512015
```
- カラーメモリー30個をコイン810で交換

---

## 設定時のポイント

1. **id とラインナップIDの統一**: `id` は `mst_exchange_lineup_id` と同じ値を設定するのが慣習。管理の一貫性を保つために統一する。
2. **resource_id の NULL 設定**: `Coin` や `FreeDiamond` など数量だけで定義できるリソースは `resource_id` をNULLにする。`Item` `Unit` `Emblem` `Artwork` は必ず有効なIDを設定する。
3. **1ラインナップ1報酬**: 1つのラインナップIDに対して報酬は1レコードのみ設定する。複数報酬（OR条件）はサポートされていない。
4. **resource_amount の下限**: 最低1以上の整数を設定する。0や負数はシステムエラーの原因になる。
5. **Unit の resource_amount**: ユニットを報酬にする場合も `resource_amount` は通常1を設定する（1体付与）。
6. **存在確認**: `resource_id` に設定するIDは対応するマスタテーブルに実際に存在するIDであることを確認する。存在しないIDを設定するとサーバー側でエラーが発生する。
7. **アイテムの is_visible 設定**: `resource_type = Item` で設定するアイテムが `MstItem.is_visible = 0` の場合、プレイヤーのアイテム一覧に表示されない。意図しない場合は `is_visible = 1` に設定する。

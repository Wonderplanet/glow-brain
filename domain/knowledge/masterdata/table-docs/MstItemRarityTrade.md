# MstItemRarityTrade 詳細説明

> CSVパス: `projects/glow-masterdata/MstItemRarityTrade.csv`

---

## 概要

`MstItemRarityTrade` は**キャラのかけら（CharacterFragment）をかけらBOXに交換する際のレアリティ別交換設定テーブル**。レアリティごとに「何個のかけらを消費してBOXに交換できるか」「交換上限数」「上限のリセット期間」を定義する。

プレイヤーが余ったかけらをかけらBOXに変換する「かけら→BOX交換機能」の設定を管理するテーブルで、レアリティ別に1レコードずつ設定する。

### ゲームプレイへの影響

- **rarity**: この交換レートが適用されるかけらのレアリティ。かけらアイテム（`MstItem.rarity`）と対応する。
- **cost_amount**: かけらBOX1個を得るために消費するかけらの個数。レアリティが高いほど少ない消費数で交換できる設計（稀少性の価値を反映）。
- **reset_type**: 交換上限のリセット周期。`None` の場合は累計上限（リセットなし）、`Daily` は毎日リセットなど。
- **max_tradable_amount**: 交換できる上限個数。NULL の場合は無制限。

### 関連テーブルとの構造図

```
MstItem（type = CharacterFragment、rarity = R/SR/SSR/UR）
  └─ rarity → MstItemRarityTrade.rarity（レアリティ別交換設定）

MstItemRarityTrade（交換設定）
  └─ 交換結果として付与されるBOXアイテム → MstFragmentBox（ラインナップで管理）
```

---

## 全カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー（UUID）。`rarity_trade_{番号}` 形式 |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `rarity` | enum | 不可 | - | 交換対象かけらのレアリティ。ユニーク制約あり |
| `cost_amount` | int unsigned | 不可 | 1 | かけらBOX1個に必要な消費かけら数 |
| `reset_type` | enum | 不可 | None | 交換上限のリセット周期（`ItemTradeResetType` enum） |
| `max_tradable_amount` | int unsigned | 可 | - | 交換上限個数。NULLは無制限 |

---

## ItemTradeResetType（リセット種別）

| 値 | 説明 |
|----|------|
| `None` | リセットなし（累計上限。`max_tradable_amount` がNULLなら永続無制限） |
| `Daily` | 毎日リセット |
| `Weekly` | 毎週リセット |
| `Monthly` | 毎月リセット |

---

## rarity ごとの現行設定

| rarity | cost_amount | reset_type | max_tradable_amount |
|--------|-------------|------------|---------------------|
| R | 15個 | None | NULL（無制限） |
| SR | 10個 | None | NULL（無制限） |
| SSR | 5個 | None | NULL（無制限） |
| UR | 3個 | None | NULL（無制限） |

レアリティが高いほど少ないかけらでBOXに交換できる設計。

---

## 命名規則 / IDの生成ルール

| 種類 | 命名パターン | 例 |
|------|------------|-----|
| id | `rarity_trade_{番号}` | `rarity_trade_1`, `rarity_trade_4` |

rarity カラムにはユニーク制約があるため、各レアリティのレコードは1件のみ。

---

## 他テーブルとの連携

| 連携先テーブル | カラム | 関係 |
|-------------|-------|------|
| `mst_items` | `rarity` → `rarity`（type=CharacterFragment のアイテム）| かけらアイテムのレアリティと照合（N:1） |

---

## 実データ例

**パターン1: Rレアリティのかけら交換（消費15個）**
```
ENABLE: e
id: rarity_trade_1
release_key: 202509010
rarity: R
cost_amount: 15
reset_type: None
max_tradable_amount: NULL
```
- Rレアかけら15個 → かけらBOX1個
- 交換上限なし（無制限）
- リセットなし（永続設定）

**パターン2: URレアリティのかけら交換（消費3個）**
```
ENABLE: e
id: rarity_trade_4
release_key: 202509010
rarity: UR
cost_amount: 3
reset_type: None
max_tradable_amount: NULL
```
- URレアかけら3個 → かけらBOX1個
- レアリティが高いため少数で交換可能

---

## 設定時のポイント

1. **rarity のユニーク制約**: 各レアリティに対して1件のみレコードを作成できる。既存レコードを更新する場合は新規作成でなく既存レコードの `cost_amount` や `reset_type` を変更する。
2. **N レアリティの設定**: 現在Nレアのかけら交換レコードは存在しない。Nレアかけらが追加された場合はレコードを追加する必要がある。
3. **cost_amount のバランス**: レアリティが高いほど消費数を少なくするのが基本設計。変更時はゲームバランス観点でのレビューが必要。
4. **max_tradable_amount の NULL**: 無制限交換にする場合はNULL（CSVでは空欄）を設定する。上限を設けてガチャチケット経済を制御したい場合は数値を設定する。
5. **reset_type の変更**: `None` → `Daily` などに変更する場合、変更前に交換済みプレイヤーへの影響を確認する。リセットなしから有りへの変更は緊急時以外推奨しない。
6. **交換可能なBOXの種類**: このテーブルはレートのみを管理し、どのBOXに交換するかは別の仕組み（サーバーロジック・クライアント）で管理されている。`MstFragmentBox` と組み合わせて機能する。

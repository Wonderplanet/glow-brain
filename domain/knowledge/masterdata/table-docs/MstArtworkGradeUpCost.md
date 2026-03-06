# MstArtworkGradeUpCost 詳細説明

> CSVパス: `projects/glow-masterdata/MstArtworkGradeUpCost.csv`

---

## 概要

原画グレードアップに必要なコスト（消費リソース）を定義するマスタテーブル。
1つのグレードアップ設定（`mst_artwork_grade_ups`）に対して、必要なアイテム種別・ID・数量を1レコードで管理する。
グレードアップに複数のコストが必要な場合は、同一の `mst_artwork_grade_up_id` に対して複数レコードを用意する。

---

## 全カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---|---|---|---|
| ENABLE | varchar | YES | 有効フラグ（`e` = 有効） |
| id | varchar(255) | YES | レコードID（主キー） |
| mst_artwork_grade_up_id | varchar(255) | YES | 対応するグレードアップ設定のID（`mst_artwork_grade_ups.id`） |
| resource_type | enum('Item') | YES | 消費リソースのタイプ。現在は `Item` のみ |
| resource_id | varchar(255) | NO | 消費アイテムのID（`mst_items.id`）。resource_typeがItemの場合に設定 |
| resource_amount | int unsigned | YES | 消費数量 |
| release_key | bigint | YES | リリースキー（デフォルト: 1） |

---

## ResourceType（resource_type enumの値）

| 値 | 説明 |
|---|---|
| Item | アイテムを消費する（現在唯一のタイプ） |

---

## 命名規則 / IDの生成ルール

IDは `{mst_artwork_grade_up_id}_{連番2桁}` の形式で構成される。

例:
- `spy_01_01` → グレードアップID `spy_01` の1番目のコスト
- `spy_03_02` → グレードアップID `spy_03` の2番目のコスト

グレードアップに必要なコストが複数ある場合は連番を増やして複数レコードを作成する。

---

## 他テーブルとの連携

| 参照先テーブル | カラム | 内容 |
|---|---|---|
| `mst_artwork_grade_ups` | `mst_artwork_grade_up_id` | グレードアップ設定の参照 |
| `mst_items` | `resource_id` | 消費アイテムの参照 |

---

## 実データ例

**例1: グレードアップID `spy_01` の1段階コスト設定**

| id | mst_artwork_grade_up_id | resource_type | resource_id | resource_amount | release_key |
|---|---|---|---|---|---|
| spy_01_01 | spy_01 | Item | artwork_enhance_glo_00001 | 10 | 202603020 |

**例2: グレードアップID `spy_03` の2段階コスト設定（複数コスト）**

| id | mst_artwork_grade_up_id | resource_type | resource_id | resource_amount | release_key |
|---|---|---|---|---|---|
| spy_03_01 | spy_03 | Item | artwork_enhance_glo_00001 | 10 | 202603020 |
| spy_03_02 | spy_03 | Item | artwork_enhance_glo_00002 | 10 | 202603020 |

グレードアップに複数種類のアイテムが必要な場合、同じ `mst_artwork_grade_up_id` に対して連番でレコードを追加する。

---

## 設定時のポイント

1. `mst_artwork_grade_up_id` には必ず `mst_artwork_grade_ups` テーブルに存在するIDを設定すること。
2. 1つのグレードアップに対して複数コストを設定する場合はIDを `_01`, `_02` のように連番で管理する。
3. 現在 `resource_type` は `Item` のみサポートしているため、`Item` 以外は設定不可。
4. `resource_id` には `mst_items.id` に存在するアイテムIDを設定する（resource_type が Item の場合）。
5. `resource_amount` は1以上の正の整数を設定する。0や負の値は設定しないこと。
6. `release_key` は対象リリース時期に対応するリリースキー値を設定する。
7. グレードアップに追加コストが不要な場合は、当該 `mst_artwork_grade_up_id` に対して1レコードのみ作成する。
8. ENABLE カラムは通常 `e`（有効）を設定する。無効にする場合は `e` 以外を設定する。

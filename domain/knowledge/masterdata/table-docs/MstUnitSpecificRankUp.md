# MstUnitSpecificRankUp 詳細説明

> CSVパス: `projects/glow-masterdata/MstUnitSpecificRankUp.csv`

---

## 概要

特定ユニット個別のランクアップ（レベル上限開放）設定を定義するテーブル。
汎用設定テーブルである mst_unit_rank_ups に対して、特定ユニットにのみ異なる必要素材を設定したい場合に本テーブルを使用する。
通常のリミテッドメモリーの代わりに「キャラ個別メモリー（unit_memory_amount）」を使用するユニット向けの設定が中心。

---

## 全カラム一覧（テーブル形式）

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---|---|---|---|---|
| id | varchar(255) | NOT NULL | - | UUID（{mst_unit_id}_rank{rank} 形式） |
| release_key | bigint | NOT NULL | 1 | リリースキー |
| mst_unit_id | varchar(255) | NOT NULL | - | 対象ユニットのID（mst_units.id） |
| rank | int | NOT NULL | - | ランクアップ後のランク（1〜6） |
| amount | int | NOT NULL | - | リミテッドメモリーの必要数 |
| unit_memory_amount | int unsigned | NOT NULL | 0 | キャラ個別メモリーの必要数 |
| require_level | int | NOT NULL | - | ランクアップに必要なユニットの現在レベル |
| sr_memory_fragment_amount | int | NOT NULL | 0 | 初級メモリーフラグメントの必要数 |
| ssr_memory_fragment_amount | int | NOT NULL | 0 | 中級メモリーフラグメントの必要数 |
| ur_memory_fragment_amount | int | NOT NULL | 0 | 上級メモリーフラグメントの必要数 |

---

## 命名規則 / IDの生成ルール

`id` は `{mst_unit_id}_rank{rank}` の形式で命名される。
例: ユニット `chara_kai_00501` のランク1設定は `chara_kai_00501_rank1`

`mst_unit_id` と `rank` の組み合わせが `uk_mst_unit_id_rank` ユニーク制約により保護されている。

---

## 他テーブルとの連携

| 関連テーブル | 連携カラム | 説明 |
|---|---|---|
| mst_units | mst_unit_id | 対象ユニットの参照 |
| mst_unit_rank_ups | unit_label + rank | 汎用ランクアップ設定（本テーブルが優先） |
| mst_unit_rank_coefficients | rank | ランク別ステータス係数の参照 |
| mst_unit_level_ups | unit_label + require_level | レベル上限の定義 |

---

## 実データ例（CSVから取得）

### パターン1: chara_kai_00501（カイキャラ）の個別ランクアップ設定

| id | mst_unit_id | rank | require_level | amount | unit_memory_amount | sr_memory_fragment_amount | ssr_memory_fragment_amount | ur_memory_fragment_amount | release_key |
|---|---|---|---|---|---|---|---|---|---|
| chara_kai_00501_rank1 | chara_kai_00501 | 1 | 20 | 0 | 200 | 0 | 0 | 0 | 202509010 |
| chara_kai_00501_rank2 | chara_kai_00501 | 2 | 30 | 0 | 250 | 5 | 0 | 0 | 202509010 |
| chara_kai_00501_rank3 | chara_kai_00501 | 3 | 40 | 0 | 300 | 10 | 5 | 0 | 202509010 |
| chara_kai_00501_rank4 | chara_kai_00501 | 4 | 50 | 0 | 350 | 15 | 10 | 0 | 202509010 |
| chara_kai_00501_rank5 | chara_kai_00501 | 5 | 60 | 0 | 400 | 20 | 15 | 3 | 202509010 |
| chara_kai_00501_rank6 | chara_kai_00501 | 6 | 70 | 0 | 450 | 25 | 20 | 5 | 202509010 |

### パターン2: chara_kai_00601（別カイキャラ）の個別ランクアップ設定（一部）

| id | mst_unit_id | rank | require_level | amount | unit_memory_amount | release_key |
|---|---|---|---|---|---|---|
| chara_kai_00601_rank1 | chara_kai_00601 | 1 | 20 | 0 | 200 | 202509010 |
| chara_kai_00601_rank2 | chara_kai_00601 | 2 | 30 | 0 | 250 | 202509010 |

---

## 設定時のポイント

1. **id は `{mst_unit_id}_rank{rank}` の命名規則を遵守する**: 他のレコードとの整合性を保つため、この命名規則から逸脱しない。
2. **mst_unit_id と rank の組み合わせはユニーク**: `uk_mst_unit_id_rank` ユニーク制約があるため重複登録不可。
3. **リミテッドメモリー（amount）は0で、キャラ個別メモリー（unit_memory_amount）で代替する設計**: 現行データではこの組み合わせが標準。
4. **全ランク分（1〜最大ランク）のレコードが必要**: ユニットを本テーブルで管理する場合は全ランク分のレコードを登録する。
5. **本テーブルに登録されたユニットは mst_unit_rank_ups より本テーブルが優先される**: 汎用設定から外れる特殊ユニットのみを登録する。
6. **require_level はmst_unit_level_upsに存在するレベルであること**: 設定したレベルがレベルアップテーブルに存在しないと動作しない。
7. **release_key はリリース管理に使用**: 新キャラ追加時のリリースキーと揃えること。
8. **ユニット追加時は6ランク分を一括で登録する**: ランク抜けがあると上限開放できないランクが発生する。

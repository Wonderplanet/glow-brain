# MstBoxGachaGroup 詳細説明

> CSVパス: `projects/glow-masterdata/MstBoxGachaGroup.csv`

---

## 概要

ボックスガチャ（いいジャンくじ）の「箱」単位のグループ設定を管理するマスタテーブル。
ボックスガチャは複数の箱（box_level）で構成されており、各箱は景品セット（グループ）に対応する。
1つのボックスガチャに対して箱レベルの数だけグループレコードを作成する。

クライアントクラス: `MstBoxGachaGroupData.cs`

---

## 全カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---|---|---|---|
| ENABLE | varchar | YES | 有効フラグ（`e` = 有効） |
| id | varchar(255) | YES | グループID（主キー） |
| release_key | bigint | YES | リリースキー（デフォルト: 1） |
| mst_box_gacha_id | varchar(255) | YES | 対応するボックスガチャID（`mst_box_gachas.id`） |
| box_level | int unsigned | YES | 箱レベル（1から順番に設定） |

ユニークキー: `(mst_box_gacha_id, box_level)` の組み合わせで一意となる。

---

## 命名規則 / IDの生成ルール

IDは `{mst_box_gacha_id}_group_{連番3桁}` の形式が一般的。

例:
- `box_gacha_kim_01_group_001` → `box_gacha_kim_01` ガチャの箱レベル1
- `box_gacha_kim_01_group_002` → `box_gacha_kim_01` ガチャの箱レベル2
- `box_gacha_test_group_1` → テスト用ガチャの箱レベル1

---

## 他テーブルとの連携

| 参照先テーブル | カラム | 内容 |
|---|---|---|
| `mst_box_gachas` | `mst_box_gacha_id` | 親ボックスガチャの参照 |

| 参照元テーブル | 用途 |
|---|---|
| `mst_box_gacha_prizes` | `mst_box_gacha_group_id` からグループ内の景品一覧を参照 |

---

## 実データ例

**例1: テスト用ガチャのグループ（1箱のみ）**

| id | release_key | mst_box_gacha_id | box_level |
|---|---|---|---|
| box_gacha_test_group_1 | 202602010 | box_gacha_test | 1 |

**例2: 100カノ いいジャンくじの複数箱設定（一部抜粋）**

| id | release_key | mst_box_gacha_id | box_level |
|---|---|---|---|
| box_gacha_kim_01_group_001 | 202602020 | box_gacha_kim_01 | 1 |
| box_gacha_kim_01_group_002 | 202602020 | box_gacha_kim_01 | 2 |
| box_gacha_kim_01_group_003 | 202602020 | box_gacha_kim_01 | 3 |
| box_gacha_kim_01_group_009 | 202602020 | box_gacha_kim_01 | 9 |

100カノくじは複数の箱（box_level）を持ち、各箱に異なる景品セットが設定される。

---

## 設定時のポイント

1. `mst_box_gacha_id` には `mst_box_gachas` に存在するガチャIDを設定する。
2. `box_level` は1から連続した整数で設定する（飛ばしや重複は不可）。
3. 各グループに対して、`mst_box_gacha_prizes` テーブルに景品レコードを作成する必要がある。
4. 箱の数（グループ数）はガチャの設計によって異なる。シンプルなガチャは1箱のみ、豪華なガチャは複数箱構成にする。
5. IDの命名は `{ガチャID}_group_{3桁連番}` の形式で統一する（3桁: `001`, `002`...）。
6. テスト用データのIDは連番なし（`_group_1`）でも可だが、本番データは3桁連番を推奨。
7. `loop_type` が `Last` の場合、最後の `box_level` の箱が繰り返し使用されるため、最終箱の景品設定を特に慎重に行う。
8. 全てのグループに `release_key` を統一することが推奨される（同一ガチャ内で異なるリリースキーは避ける）。

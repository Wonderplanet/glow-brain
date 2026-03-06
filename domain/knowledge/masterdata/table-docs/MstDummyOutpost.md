# MstDummyOutpost 詳細説明

> CSVパス: `projects/glow-masterdata/MstDummyOutpost.csv`

---

## 概要

ダミーユーザー（CPU対戦相手）のアウトポスト（前線拠点）情報を管理するマスタテーブル。
PVPなどの対戦コンテンツでダミーユーザーが保有するアウトポストの種別・レベルを定義する。
1つのダミーユーザーに対して、複数のアウトポスト強化を設定できる（1ユーザー複数レコード可）。

クライアントクラス: `MstDummyOutpostData.cs`

---

## 全カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---|---|---|---|
| ENABLE | varchar | YES | 有効フラグ（`e` = 有効） |
| id | int | YES | レコードID（主キー、数値型） |
| release_key | bigint | YES | リリースキー（デフォルト: 1） |
| mst_dummy_user_id | varchar(255) | YES | 対応するダミーユーザーID（`mst_dummy_users.id`） |
| mst_outpost_enhancement_id | varchar(255) | YES | アウトポスト強化ID（`mst_outpost_enhancements.id`） |
| level | int | YES | アウトポストのレベル（デフォルト: 1） |

ユニークキー: `(mst_dummy_user_id, mst_outpost_enhancement_id)` の組み合わせで一意となる。

---

## 命名規則 / IDの生成ルール

IDは数値の連番（1, 2, 3...）で管理される。PascalCaseのIDではなく整数値で管理する点が他テーブルと異なる。

---

## 他テーブルとの連携

| 参照先テーブル | カラム | 内容 |
|---|---|---|
| `mst_dummy_users` | `mst_dummy_user_id` | 対応ダミーユーザーの参照 |
| `mst_outpost_enhancements` | `mst_outpost_enhancement_id` | アウトポスト強化設定の参照 |

---

## 実データ例

**例1: ブロンズランク2のダミーユーザーのアウトポスト設定（一部）**

| id | release_key | mst_dummy_user_id | mst_outpost_enhancement_id | level |
|---|---|---|---|---|
| 1 | 202509010 | bronze_2_user1 | enhance_1_5 | 3 |
| 2 | 202509010 | bronze_2_user2 | enhance_1_5 | 3 |
| 3 | 202509010 | bronze_2_user3 | enhance_1_5 | 3 |

**例2: ブロンズランク3のダミーユーザーのアウトポスト設定**

| id | release_key | mst_dummy_user_id | mst_outpost_enhancement_id | level |
|---|---|---|---|---|
| 7 | 202509010 | bronze_3_user1 | enhance_1_5 | 3 |
| 8 | 202509010 | bronze_3_user2 | enhance_1_5 | 3 |

同じランク内の複数ダミーユーザーが同一の `mst_outpost_enhancement_id` と `level` を持つパターンが多い。

---

## 設定時のポイント

1. `id` カラムは数値連番で管理する（他のmstテーブルと異なりvarcharではなく整数型）。
2. `mst_dummy_user_id` には `mst_dummy_users` テーブルに存在するIDを設定する。
3. `mst_outpost_enhancement_id` には `mst_outpost_enhancements` テーブルに存在するIDを設定する。
4. ユニークキーが `(mst_dummy_user_id, mst_outpost_enhancement_id)` の組み合わせのため、同一ユーザーに同じアウトポスト強化を重複設定できない。
5. `level` は対象ランク帯のユーザー強度に合わせて設定する（ランクが高いほど level を大きくする）。
6. 複数のダミーユーザーに同一のアウトポスト設定を適用する場合、ユーザーごとに個別レコードを作成する。
7. 新しいダミーユーザーを追加した場合は、このテーブルにもアウトポスト設定レコードを追加する。
8. `release_key` は同じリリースで追加されるダミーユーザー・アウトポストを統一して設定する。

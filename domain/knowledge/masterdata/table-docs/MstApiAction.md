# MstApiAction 詳細説明

> CSVパス: `projects/glow-masterdata/MstApiAction.csv`

---

## 概要

サーバーAPIの動作設定を管理するテーブル。各APIエンドポイントに対して、アプリバージョンチェック・マスタデータバージョンチェック・日跨ぎチェック・アセットバージョンチェックのスキップ可否を定義する。

通常、クライアントがAPIをリクエストする際にサーバー側でこれらのバージョンチェックが行われるが、特定のAPIパス（例: ストア情報設定API）では一部のチェックをスキップする必要がある。このテーブルにより、API単位で細かく動作を制御できる。

`api_path` にはユニーク制約が設定されており、同一パスで複数のレコードを設定することはできない。

---

## 全カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---------|----|----|------|
| ENABLE | varchar | YES | 有効フラグ（`e` = 有効） |
| release_key | bigint | YES | リリースキー |
| id | varchar(255) | YES | UUID。APIアクションを一意に識別するID |
| api_path | varchar(255) | YES | APIのパス（例: `shop/set_store_info`）。ユニーク制約あり |
| through_app | tinyint unsigned | YES | アプリバージョンチェックをスキップするか（`0` = チェックあり、`1` = スキップ） |
| through_master | tinyint unsigned | YES | マスタデータバージョンチェックをスキップするか（`0` = チェックあり、`1` = スキップ） |
| through_date | tinyint unsigned | YES | 日跨ぎチェックをスキップするか（`0` = チェックあり、`1` = スキップ） |
| through_asset | tinyint unsigned | YES | アセットデータバージョンチェックをスキップするか（`0` = チェックあり、`1` = スキップ） |
| resource | json | NO | API追加情報（JSON形式、NULL可） |

---

## フラグ値の意味

各 `through_*` フラグは `tinyint unsigned` で以下の値を取る:

| 値 | 意味 |
|----|------|
| `0` | チェックを実施する（スキップしない） |
| `1` | チェックをスキップする |

---

## 命名規則 / IDの生成ルール

- `id`: APIパスをアンダースコアに変換した形式（例: `shop/set_store_info` → `shop_set_store_info`）
- `api_path`: スラッシュ区切りのAPIパス（例: `shop/set_store_info`）

---

## 他テーブルとの連携

このテーブルはサーバーのAPIミドルウェア層で参照されるため、直接他のマスタデータテーブルとFKで紐付くものはない。APIパスを基準にサーバーが内部的に参照する。

---

## 実データ例

### パターン1: ストア情報設定API（マスタ・日跨ぎ・アセットチェックをスキップ）

```
ENABLE: e
release_key: 999999999
id: shop_set_store_info
api_path: shop/set_store_info
through_app: 0
through_master: 1
through_date: 1
through_asset: 1
resource: NULL
```

この設定では:
- アプリバージョンチェックは実施する（`through_app: 0`）
- マスタデータ・日跨ぎ・アセットの各チェックはスキップ（値が `1`）

### パターン2: 標準的なAPI（すべてのチェックを実施する場合の想定）

```
ENABLE: e
release_key: 202509010
id: battle_start
api_path: battle/start
through_app: 0
through_master: 0
through_date: 0
through_asset: 0
resource: NULL
```

---

## 設定時のポイント

1. **api_path のユニーク制約**: `api_path` はテーブル内でユニーク。同一パスに対して複数の設定レコードを作ることはできない。
2. **through_app の設定**: アプリのバージョンアップ必須チェックをスキップするかどうか。通常は `0`（スキップしない）。スキップが必要なのは、強制アップデート前でも動作が必要なAPIのみ。
3. **through_master の設定**: マスタデータが最新でなくてもAPIを通す場合は `1` に設定。マスタ更新を待たずに動作させる必要があるAPIが対象。
4. **through_date の設定**: 日跨ぎ処理中でもAPIを通す場合は `1` に設定。日跨ぎ処理と並行して動作が必要なAPIが対象（ショップ情報取得など）。
5. **through_asset の設定**: アセットデータのバージョン不一致でもAPIを通す場合は `1`。アセット更新前に呼び出される可能性のあるAPIに設定する。
6. **release_key が 999999999**: 特殊なリリースキー `999999999` は「常に有効」な設定を示す慣例。通常のリリースサイクルに依存しないAPIアクション設定に使用する。
7. **resource フィールドの活用**: 現状はNULLが多いが、API固有の追加設定をJSON形式で格納できる拡張ポイント。

---
name: Querying Database
description: DB操作が必要な際に使用。glow-server-local-db MCPを使ってテーブル構造確認、データ検索・確認、データ追加・更新・削除を行う。DB接頭辞（mst_/mng_/adm_/usr_/log_/sys_）から正しいdatabase接続先を判定し、間違いを防ぐ。
---

# Database Query

glow-server-local-db MCPを使用して、データベースのテーブル構造確認、データ検索、データ操作を行います。

## Instructions

### 1. DB接続先の判定

テーブル名の接頭辞から正しいdatabase接続先を判定:
参照: **[connection-guide.md](connection-guide.md)**

### 2. 操作タイプに応じたガイド参照

実行したい操作に応じて以下を参照:

- **テーブル構造確認** → **[guides/table-structure.md](guides/table-structure.md)**
- **データ検索・確認** → **[guides/data-query.md](guides/data-query.md)**
- **データ追加・更新・削除** → **[guides/data-modification.md](guides/data-modification.md)**

### 3. 実装例の参照

DB接続先に応じた実装例を参照:

- **MySQL (mst/mng/admin)** → **[examples/mysql-operations.md](examples/mysql-operations.md)**
- **TiDB (usr/log/sys)** → **[examples/tidb-operations.md](examples/tidb-operations.md)**

## 参照ドキュメント

- **[connection-guide.md](connection-guide.md)** - DB接続先の判定ルール
- **[guides/table-structure.md](guides/table-structure.md)** - テーブル構造の確認方法
- **[guides/data-query.md](guides/data-query.md)** - データの検索・確認方法
- **[guides/data-modification.md](guides/data-modification.md)** - データの追加・更新・削除方法
- **[examples/mysql-operations.md](examples/mysql-operations.md)** - MySQL DB (mst/mng/admin)の操作例
- **[examples/tidb-operations.md](examples/tidb-operations.md)** - TiDB (usr/log/sys)の操作例

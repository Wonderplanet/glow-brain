# glow-server-local-db MCP Server

glow-server用のMCP（Model Context Protocol）サーバー。ローカルのMySQL/TiDBデータベースへの安全なアクセスを提供します。

## 特徴

- **デフォルトで読み取り専用**: 安全のため、デフォルトでは`ENABLE_WRITE_QUERIES=false`となっており、SELECT等の読み取りクエリのみ実行可能
- **明示的な書き込み許可**: `ENABLE_WRITE_QUERIES=true`に設定することで、INSERT/UPDATE/DELETE等の書き込みクエリが実行可能になります
- **ローカル接続限定**: localhost接続のみ許可し、リモートデータベースへの接続は制限

## セットアップ

### 1. 依存関係のインストール

```bash
cd mcp/server/glow/glow-server-local-db
npm install
```

### 2. MCP設定

Claude CodeまたはGitHub Copilot Chatの設定ファイルに以下を追加します。

#### Claude Code (.mcp.json)

```json
{
  "mcpServers": {
    "glow-server-local-db": {
      "command": "node",
      "args": ["/absolute/path/to/ai-tools/mcp/server/glow/glow-server-local-db/index.js"],
      "cwd": ".",
      "env": {
        "ENABLE_WRITE_QUERIES": "false",
        "MASTER_DB_HOST": "localhost",
        "MASTER_DB_PORT": "33060",
        "MASTER_DB_DATABASE": "local",
        "MASTER_DB_USERNAME": "root",
        "MASTER_DB_PASSWORD": "root",
        "MANAGE_DB_HOST": "localhost",
        "MANAGE_DB_PORT": "33060",
        "MANAGE_DB_DATABASE": "mng",
        "MANAGE_DB_USERNAME": "root",
        "MANAGE_DB_PASSWORD": "root",
        "ADMIN_DB_HOST": "localhost",
        "ADMIN_DB_PORT": "33060",
        "ADMIN_DB_DATABASE": "admin",
        "ADMIN_DB_USERNAME": "root",
        "ADMIN_DB_PASSWORD": "root",
        "TIDB_HOST": "localhost",
        "TIDB_PORT": "4000",
        "TIDB_DATABASE": "local",
        "TIDB_USERNAME": "root",
        "TIDB_PASSWORD": ""
      }
    }
  }
}
```

#### GitHub Copilot Chat (.vscode/mcp.json)

```json
{
  "servers": {
    "glow-server-local-db": {
      "command": "node",
      "args": ["/absolute/path/to/ai-tools/mcp/server/glow/glow-server-local-db/index.js"],
      "cwd": ".",
      "env": {
        "ENABLE_WRITE_QUERIES": "false",
        "MASTER_DB_HOST": "localhost",
        "MASTER_DB_PORT": "33060",
        "MASTER_DB_DATABASE": "local",
        "MASTER_DB_USERNAME": "root",
        "MASTER_DB_PASSWORD": "root",
        "MANAGE_DB_HOST": "localhost",
        "MANAGE_DB_PORT": "33060",
        "MANAGE_DB_DATABASE": "mng",
        "MANAGE_DB_USERNAME": "root",
        "MANAGE_DB_PASSWORD": "root",
        "ADMIN_DB_HOST": "localhost",
        "ADMIN_DB_PORT": "33060",
        "ADMIN_DB_DATABASE": "admin",
        "ADMIN_DB_USERNAME": "root",
        "ADMIN_DB_PASSWORD": "root",
        "TIDB_HOST": "localhost",
        "TIDB_PORT": "4000",
        "TIDB_DATABASE": "local",
        "TIDB_USERNAME": "root",
        "TIDB_PASSWORD": ""
      }
    }
  },
  "inputs": []
}
```

**重要**:
- `args`のパスは、ai-tools/mcp/server/glow/glow-server-local-db/index.jsへの**絶対パス**を指定してください
- `env`フィールドの接続情報は、実際のデータベース環境に合わせて変更してください

### 環境変数の説明

- **ENABLE_WRITE_QUERIES** (デフォルト: `false`)
  - `false`: 読み取り専用モード。`execute_query`ツールは利用不可（安全）
  - `true`: 書き込み可能モード。`execute_query`ツールが利用可能になり、INSERT/UPDATE/DELETE等のクエリを実行できます

## 利用可能なツール

### 常に利用可能なツール

- `list_databases` - 利用可能なデータベース一覧を表示
- `list_tables` - 指定したデータベースのテーブル一覧を表示
- `describe_table` - テーブル構造（カラム、型、制約）を表示
- `show_indexes` - テーブルのインデックス情報を表示
- `query_database` - SELECTクエリを実行（読み取り専用、自動LIMIT付き）

### ENABLE_WRITE_QUERIES=trueの時のみ利用可能

- `execute_query` - 書き込みクエリを実行（INSERT、UPDATE、DELETE、またはその他のSQLクエリ）

## 対応データベース

- **mst**: MySQL (マスターデータ)
- **mng**: MySQL (管理データ)
- **admin**: MySQL (管理者データ)
- **usr**: TiDB (ユーザーデータ)
- **log**: TiDB (ログデータ)
- **sys**: TiDB (システムデータ)

## セキュリティ

- **ローカル接続のみ**: localhost、127.0.0.1、0.0.0.0、::1 のみ接続可能
- **読み取り専用モード**: デフォルトでは`ENABLE_WRITE_QUERIES=false`により、書き込みクエリは実行不可
- **読み取り専用ツール**: `query_database`ツールはSELECT、SHOW、DESCRIBE、EXPLAINクエリのみ実行可能
- **書き込み可能ツール**: `ENABLE_WRITE_QUERIES=true`に設定した場合のみ、`execute_query`ツールが有効になり、INSERT/UPDATE/DELETE等の書き込みクエリが実行可能
- **自動LIMIT**: `query_database`ツールでのSELECTクエリには自動的にLIMIT 100を追加（既存のLIMITがない場合）

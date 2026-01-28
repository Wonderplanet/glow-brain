# glow-server用 MCP設定方法

## 設定手順

1. glow-serverリポジトリのルートに `.mcp.json` ファイルを作成
2. 下記の設定例をコピー
3. `glow-server-local-db` の `args` 内のパスを**絶対パス**に変更
4. データベース接続情報（ポート番号、パスワード等）を実際の環境に合わせて変更

## Claude Code設定例

```json
{
    "mcpServers": {
        "chrome-devtools": {
            "command": "npx",
            "args": [
                "chrome-devtools-mcp@latest",
                "--headless=true"
            ]
        },
        "glow-server-local-db": {
            "command": "node",
            "args": [
                "/absolute/path/to/glow-server/.ai-context/mcp/glow-server-local-db/index.js"
            ],
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

## MCPサーバー概要

### chrome-devtools
ブラウザ自動化のためのMCPサーバー。ページ操作、スクリーンショット撮影、要素のクリック・入力などが可能です。
管理ツールの動作確認をAIに任せるときなどに使えます。
https://github.com/ChromeDevTools/chrome-devtools-mcp

### glow-server-local-db
glow-serverのローカルMySQL/TiDBデータベースへアクセスするためのMCPサーバー。テーブル構造の確認やSELECTクエリの実行ができます。詳細は [glow-server-local-db/README.md](glow-server-local-db/README.md) を参照してください。

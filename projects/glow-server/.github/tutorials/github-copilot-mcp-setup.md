# GitHub Copilot での MCP サーバー設定

このドキュメントでは、GitHub Copilot（VS Code拡張機能）でMCPサーバーを利用する方法を説明します。

> **公式ドキュメント**: https://docs.github.com/ja/copilot/how-tos/provide-context/use-mcp/extend-copilot-chat-with-mcp

## 前提条件

- VS Code がインストールされていること
- GitHub Copilot 拡張機能がインストールされていること
- GitHub Copilot のサブスクリプションが有効であること
- Node.js がインストールされていること

## 設定方法

GitHub CopilotでMCPサーバーを使用するには、以下の2つの方法があります。

---

### 方法1: 設定ファイルを直接編集する（推奨）

プロジェクトの `.vscode/mcp.json` ファイルに設定を追加します。チームで設定を共有できるため、こちらを推奨します。

#### 手順

1. `.vscode/mcp.json` ファイルを作成（または編集）

   ```bash
   mkdir -p .vscode
   touch .vscode/mcp.json
   ```

2. 以下の内容を記述

   ```json
   {
     "servers": {
       "glow-server-local-db": {
         "command": "node",
         "args": [
           "/absolute/path/to/glow-server/.ai-context/mcp/glow-server-local-db/index.js"
         ],
         "env": {
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
       },
       "chrome-devtools": {
         "command": "npx",
         "args": [
           "chrome-devtools-mcp@latest",
           "--isolated"
         ]
       }
     }
   }
   ```

   > **重要**: `args` 内のパスは、ご自身の環境に合わせて絶対パスで指定してください。

3. VS Code でファイルを開くと、上部に **「Start」** ボタンが表示されるのでクリックしてサーバーを起動

---

### 方法2: VS Code の UI から設定する

VS Code の拡張機能パネルからMCPサーバーを設定します。

#### 手順

1. VS Code の左サイドバーで **拡張機能アイコン** をクリック

2. フィルターから **「MCP Registry」** を選択

3. 追加したいMCPサーバーを検索して選択

4. サーバーの設定ページで **「Install」** をクリック

5. 設定完了後、コマンドパレット（`Cmd+Shift+P` / `Ctrl+Shift+P`）で **「MCP: List Servers」** を実行して確認

> **注意**: glow-server-local-db のようなカスタムMCPサーバーは、方法1の設定ファイル編集が必要です。MCP Registryには公開されているサーバーのみが表示されます。

---

## 設定項目の説明

| 項目 | 説明 | 例 |
|------|------|-----|
| `command` | MCPサーバーを起動するコマンド | `"node"`, `"npx"` |
| `args` | コマンドに渡す引数 | `["/path/to/index.js"]` |
| `env` | 環境変数（DB接続情報など） | `{"DB_HOST": "localhost"}` |

## MCPサーバーの動作確認

1. VS Code を再起動（設定変更後は必要）
2. GitHub Copilot Chat を開く（サイドバーのチャットアイコン、または `Cmd+Shift+I`）
3. チャットで以下のようなプロンプトを試す:

   ```
   @workspace データベースのテーブル一覧を取得して
   ```

   または

   ```
   mst_users テーブルの構造を教えて
   ```

MCPサーバーが正しく設定されていれば、GitHub Copilot がデータベースに接続して情報を取得できます。

## トラブルシューティング

### MCPサーバーが起動しない

1. VS Code の開発者コンソールでエラーを確認
   - `Help` > `Toggle Developer Tools` > Console タブ

2. パスが正しいか確認
   - `args` で指定したパスが絶対パスで正しいか確認

3. Node.js がインストールされているか確認
   ```bash
   node --version
   ```

### データベースに接続できない

1. Dockerコンテナが起動しているか確認
   ```bash
   ./tools/bin/sail-wp ps
   ```

2. ポート番号が環境の `.env` ファイルと一致しているか確認

## Claude Code との違い

| 項目 | Claude Code | GitHub Copilot |
|------|-------------|----------------|
| 設定ファイル | `.mcp.json` | `.vscode/mcp.json` |
| 設定場所 | プロジェクトルート | `.vscode/` ディレクトリ |
| 再読み込み | Claude Code の再起動 | VS Code の再起動 |

## 参考資料

- [GitHub Copilot MCP 公式ドキュメント](https://docs.github.com/ja/copilot/how-tos/provide-context/use-mcp/extend-copilot-chat-with-mcp)
- [Model Context Protocol (MCP)](https://modelcontextprotocol.io/)
- [Claude Code MCP Setup](./../.claude/tutorials/03-mcp-setup-hands-on.md)

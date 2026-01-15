# ハンズオン: MCP（Model Context Protocol）設定

このハンズオンでは、Claude CodeでMCPサーバーを設定し、データベース操作やブラウザ操作を行う方法を学びます。

## このハンズオンで学べること

- MCPとは何か、なぜ必要なのか
- `.mcp.json` 設定ファイルの構造と書き方
- glow-serverで使用するMCPサーバーの設定
- MCPツールを使った実際の操作

## 所要時間

約25分

---

## MCPとは

### 概要

**MCP（Model Context Protocol）** は、Claude Codeが外部ツールやサービスと連携するためのプロトコルです。MCPサーバーを設定することで、Claude Codeは以下のような機能を使えるようになります：

| MCPサーバー | できること |
|------------|-----------|
| **glow-server-local-db** | データベースへのクエリ実行（テーブル確認、データ検索・更新） |
| **chrome-devtools** | ブラウザ操作（ページ遷移、クリック、フォーム入力、スクリーンショット） |

### MCPなしでできないこと

MCPがないと、Claude Codeは以下のことができません：

- ❌ データベースの中身を直接確認する
- ❌ ブラウザを操作してadmin画面をテストする
- ❌ 外部APIやサービスと連携する

---

## 事前準備

### 1. 必要なソフトウェア

- **Node.js**: MCPサーバーの実行に必要
- **Chrome**: ブラウザ操作MCPに必要
- **Docker**: データベースMCPに必要（コンテナ内のDBに接続）

### 2. Node.jsのインストール確認

MCPサーバーの実行にはNode.jsが必要です。まずインストール状況を確認しましょう：

```bash
node --version
```

**Node.jsがインストールされている場合:**
```
v20.x.x  # バージョン番号が表示される
```
→ 次のステップへ進んでください。

**Node.jsがインストールされていない場合:**
```
command not found: node
```
→ 以下の手順でインストールしてください。

#### Node.jsのインストール方法

**macOSの場合（Homebrewを使用）:**
```bash
# Homebrewがインストールされていない場合は先にインストール
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# Node.jsをインストール
brew install node

# インストール確認
node --version
npm --version
```

**nvmを使用する場合（推奨）:**
```bash
# nvmをインストール
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash

# シェルを再起動、または以下を実行
source ~/.zshrc  # または source ~/.bashrc

# Node.js LTS版をインストール
nvm install --lts
nvm use --lts

# インストール確認
node --version
```

### 3. Dockerコンテナの起動確認

データベースMCPを使用するには、DBコンテナが起動している必要があります：

```bash
./tools/bin/sail-wp up -d
./tools/bin/sail-wp ps
```

---

## MCP設定ファイルの構造

### 設定ファイルの場所

MCP設定は **プロジェクトルート** の `.mcp.json` ファイルに記述します：

```
glow-server/
├── .mcp.json          ← ここにMCP設定を記述
├── api/
├── admin/
└── ...
```

### 基本構造

```json
{
    "mcpServers": {
        "サーバー名1": {
            "command": "実行コマンド",
            "args": ["引数1", "引数2"],
            "env": {
                "環境変数名": "値"
            }
        },
        "サーバー名2": {
            // ...
        }
    }
}
```

### 設定項目の説明

| 項目 | 説明 | 例 |
|------|------|-----|
| `command` | MCPサーバーを起動するコマンド | `"node"`, `"npx"` |
| `args` | コマンドの引数 | `["server.js", "--port", "8080"]` |
| `cwd` | 作業ディレクトリ（オプション） | `"."` |
| `env` | 環境変数（オプション） | `{"DB_HOST": "localhost"}` |

---

## ハンズオン手順

### Step 1: 現在のMCP設定を確認する

まず、現在の設定を確認しましょう。Claude Codeに以下のように入力：

```
.mcp.jsonの内容を見せて
```

**glow-serverの標準設定例：**

```json
{
    "mcpServers": {
        "chrome-devtools": {
            "command": "npx",
            "args": [
                "chrome-devtools-mcp@latest",
                "--isolated"
            ]
        },
        "glow-server-local-db": {
            "command": "node",
            "args": [
                "/absolute/path/to/glow-server/.ai-context/mcp/glow-server-local-db/index.js"
            ],
            "env": {
                "MASTER_DB_HOST": "localhost",
                "MASTER_DB_PORT": "33060",
                "MASTER_DB_DATABASE": "local_mst_xxx",
                "MASTER_DB_USERNAME": "root",
                "MASTER_DB_PASSWORD": "root",
                "TIDB_HOST": "localhost",
                "TIDB_PORT": "4000",
                "TIDB_DATABASE": "xxx_local",
                "TIDB_USERNAME": "root",
                "TIDB_PASSWORD": ""
            }
        }
    }
}
```

### Step 2: データベースMCPを使ってみる

設定が完了していれば、データベース操作が可能です。

#### テーブル一覧を確認する

```
mstデータベースのテーブル一覧を見せて
```

Claude Codeは `glow-server-local-db` MCPを使用して、テーブル一覧を表示します。

#### テーブル構造を確認する

```
mst_itemsテーブルの構造を確認して
```

#### データを検索する

```
mst_itemsからidが1のレコードを取得して
```

**注意**: Claude Codeは自動的にテーブル接頭辞（`mst_`, `usr_`, `log_` など）から適切なデータベース接続を判定します。

### Step 3: ブラウザMCPを使ってみる

#### Chromeブラウザを起動する

まず、Chromeを起動します（MCPはChromeに接続して操作します）：

1. Chromeを通常通り起動
2. 開発者向けのリモートデバッグを有効にする場合：
   ```bash
   # macOSの場合
   /Applications/Google\ Chrome.app/Contents/MacOS/Google\ Chrome --remote-debugging-port=9222
   ```

#### admin画面を開く

```
admin画面（http://localhost:8081/admin）を開いて
```

#### スクリーンショットを撮る

```
現在の画面のスクリーンショットを撮って
```

#### ログイン操作を行う

```
admin画面にログインして（メール: admin@wonderpla.net, パスワード: admin）
```

---

## 設定のカスタマイズ

### 新しいMCPサーバーを追加する

例えば、GitHub MCPを追加する場合：

```json
{
    "mcpServers": {
        "github": {
            "command": "npx",
            "args": [
                "@modelcontextprotocol/server-github"
            ],
            "env": {
                "GITHUB_PERSONAL_ACCESS_TOKEN": "your_token_here"
            }
        },
        // 既存の設定...
    }
}
```

### 環境変数の変更

データベース接続情報を変更する場合は、`env` セクションを編集：

```json
"env": {
    "MASTER_DB_HOST": "localhost",
    "MASTER_DB_PORT": "33060",  // ポート番号を変更
    "MASTER_DB_DATABASE": "your_database_name",  // DB名を変更
    // ...
}
```

### 設定変更後の反映

MCP設定を変更した後は、**Claude Codeセッションを再起動**する必要があります：

1. 現在のセッションを終了（`exit` または Ctrl+C）
2. Claude Codeを再度起動

### MCPサーバーを無効化する

特定のMCPサーバーの使用をやめたい場合は、`.mcp.json` からそのサーバーの設定を削除します。

#### 方法: 設定を削除する

```json
{
    "mcpServers": {
        "glow-server-local-db": {
            ...
        }
    }
}
```

#### 重要: 無効化後はセッション再起動が必須

MCPサーバーを無効化した後は、**必ずClaude Codeセッションを再起動**してください：

```bash
# 1. 現在のセッションを終了
/exit  # または Ctrl+C

# 2. Claude Codeを再度起動
claude
```

**注意**: セッションを再起動しないと、無効化したMCPサーバーが引き続き使用可能な状態のままになります。

---

## MCPツールの使い方一覧

### glow-server-local-db

| 操作 | 指示例 |
|------|--------|
| テーブル一覧 | 「mstデータベースのテーブル一覧を見せて」 |
| テーブル構造 | 「usr_usersテーブルの構造を確認して」 |
| データ検索 | 「mst_itemsからidが100以下のデータを取得して」 |
| データ更新 | 「mst_itemsのid=1のnameを'Test'に更新して」 |
| レコード追加 | 「mst_itemsに新しいレコードを追加して」 |

**DB接続の自動判定ルール**:
| 接頭辞 | データベース | 種類 |
|--------|-------------|------|
| `mst_` | mst | MySQL（マスターデータ） |
| `mng_` | mng | MySQL（運用データ） |
| `adm_` | admin | MySQL（管理ツール） |
| `usr_` | usr | TiDB（ユーザーデータ） |
| `log_` | log | TiDB（ログデータ） |
| `sys_` | sys | TiDB（システムデータ） |

### chrome-devtools

| 操作 | 指示例 |
|------|--------|
| ページを開く | 「http://localhost:8081/admin を開いて」 |
| クリック | 「ログインボタンをクリックして」 |
| 入力 | 「メールアドレス欄にadmin@test.comと入力して」 |
| スクリーンショット | 「現在の画面のスクリーンショットを撮って」 |
| 待機 | 「"Dashboard"というテキストが表示されるまで待って」 |
| 画面サイズ変更 | 「画面サイズを1920x1080に設定して」 |

---

## よくある質問とトラブルシューティング

### Q1: MCPツールが使えない

**症状**: 「MCPサーバーに接続できません」などのエラー

**対処法**:
1. `.mcp.json` のJSON構文が正しいか確認
2. Claude Codeセッションを再起動
3. 必要なコマンド（node, npx）がインストールされているか確認

```
.mcp.jsonの内容をチェックしてJSONとして正しいか確認して
```

### Q2: データベースに接続できない

**症状**: 「Connection refused」などのエラー

**対処法**:
1. Dockerコンテナが起動しているか確認
   ```bash
   ./tools/bin/sail-wp ps
   ```
2. ポート番号が正しいか確認
3. 環境変数の設定を確認

### Q3: ブラウザが操作できない

**症状**: 「Cannot connect to Chrome」などのエラー

**対処法**:
1. Chromeが起動しているか確認
2. 他のデバッグセッションが使用中でないか確認
3. Claude Codeセッションを再起動

### Q4: 設定を変更したが反映されない

**対処法**:
1. JSONの構文エラーがないか確認
2. Claude Codeセッションを**完全に終了**して再起動
3. 設定ファイルのパスが正しいか確認（プロジェクトルートの `.mcp.json`）

---

## セキュリティに関する注意

### 機密情報の取り扱い

`.mcp.json` には環境変数としてパスワードやトークンが含まれることがあります：

```json
"env": {
    "MASTER_DB_PASSWORD": "root",  // パスワード
    "GITHUB_TOKEN": "ghp_xxx..."   // トークン
}
```

**推奨事項**:
- `.mcp.json` を `.gitignore` に追加（glow-serverでは設定済み）
- 本番環境のパスワード・トークンは記載しない
- ローカル開発用の値のみ使用

### .gitignoreの確認

```bash
cat .gitignore | grep mcp
```

`.mcp.json` が含まれていることを確認してください。

---

## まとめ

| 目的 | 方法 |
|------|------|
| MCP設定を確認 | 「.mcp.jsonの内容を見せて」 |
| DB操作を行う | 「mst_xxxテーブルを確認して」 |
| ブラウザ操作 | 「admin画面を開いて」 |
| 設定を追加 | `.mcp.json` を編集してセッション再起動 |

---

## 関連スキル・ドキュメント

- [database-query スキル](../skills/database-query/SKILL.md) - データベース操作の詳細
- [admin-browser-tester スキル](../skills/admin-browser-tester/SKILL.md) - ブラウザテストの詳細
- [chrome-devtools MCPガイド](../skills/admin-browser-tester/guides/chrome-devtools-mcp.md) - ブラウザ操作の詳細

---

## 次のステップ

- [ハンズオン: テスト実行・自動修正](./01-api-test-hands-on.md) - テスト実行の基本
- [ハンズオン: コード品質チェック](./02-sail-check-fixer-hands-on.md) - 品質チェックの基本

# Claude Code ガイド相談レポート

**日付**: 2025年12月31日
**トピック**: Marketplace/Plugin設定ファイルの作成・反映・バージョン管理

## 相談内容

- marketplace.jsonとplugin.jsonの設定ファイルの作成方法
- スキル、プラグイン名、マーケットプレイス名を変更した時の反映方法
- バージョン管理の仕組みと運用方法
- 変更パターン別の対応方法
- **ローカルリポジトリのmarketplaceを追加する方法**
- **パスの書き方の詳細なルールと制限**
- **インストールスコープ（user/project/local）の使い分け**
- **開発中のmarketplaceのテスト方法**

## 重要な結論・学び

### 1. Marketplace.json の構造と作成

マーケットプレイスは複数のプラグインをまとめて配布するための仕組みです。`.claude-plugin/marketplace.json`に配置します。

> 出典: [Plugin marketplaces documentation](https://code.claude.com/docs/en/plugin-marketplaces.md#create-the-marketplace-file)

**コード例**:
```json
{
  "name": "company-tools",
  "owner": {
    "name": "DevTools Team",
    "email": "devtools@example.com"
  },
  "metadata": {
    "description": "Enterprise plugin collection for DevTools",
    "version": "1.0.0",
    "pluginRoot": "./plugins"
  },
  "plugins": [
    {
      "name": "code-formatter",
      "source": "./plugins/formatter",
      "description": "Automatic code formatting on save",
      "version": "2.1.0",
      "author": {
        "name": "DevTools Team"
      }
    },
    {
      "name": "deployment-tools",
      "source": {
        "source": "github",
        "repo": "company/deploy-plugin"
      },
      "description": "Deployment automation tools"
    }
  ]
}
```

**必須フィールド**:
- `name`: マーケットプレイスの識別子（kebab-case）
- `owner`: メンテナーの情報（`name`は必須）
- `plugins`: プラグインのリスト

> 出典: [Marketplace schema](https://code.claude.com/docs/en/plugin-marketplaces.md#marketplace-schema)

### 2. Plugin.json の構造と作成

各プラグインは`.claude-plugin/plugin.json`を必ず含める必要があります。

> 出典: [Plugins reference](https://code.claude.com/docs/en/plugins-reference.md#plugin-manifest-schema)

**コード例**:
```json
{
  "name": "my-plugin",
  "version": "1.0.0",
  "description": "Brief plugin description",
  "author": {
    "name": "Your Name",
    "email": "your@example.com"
  },
  "homepage": "https://docs.example.com/plugin",
  "repository": "https://github.com/user/plugin",
  "license": "MIT",
  "keywords": ["keyword1", "keyword2"]
}
```

**必須フィールド**:
- `name`: プラグインの識別子（kebab-case）

### 3. スキルの追加・変更時の反映方法

スキルは`skills/`ディレクトリに配置し、SKILL.mdファイルで定義します。

> 出典: [Add skills to your plugin](https://code.claude.com/docs/en/plugins.md#add-skills-to-your-plugin)

**ディレクトリ構造**:
```
my-plugin/
├── .claude-plugin/
│   └── plugin.json
└── skills/
    └── code-review/
        └── SKILL.md
```

**SKILL.md の形式**:
```yaml
---
name: code-review
description: Reviews code for best practices and potential issues. Use when reviewing code, checking PRs, or analyzing code quality.
---

When reviewing code, check for:
1. Code organization and structure
2. Error handling
3. Security concerns
4. Test coverage
```

**反映手順**:
1. `skills/`ディレクトリにスキルを追加
2. Claude Codeを再起動
3. スキルが自動的に読み込まれます

> 出典: [Skills documentation](https://code.claude.com/docs/en/skills.md)

**ベストプラクティス**:
- スキルの説明は具体的にユースケースを含める
- "Use when..."のパターンでトリガー条件を明記する
- Claude Codeが自動的にスキルを選択できるよう詳細に記述する

**注意点**:
- スキルの追加・変更後は必ずClaude Codeを再起動する
- インストール済みプラグインの場合、キャッシュからロードされるため再インストールが必要な場合がある

### 4. プラグイン名の変更時の反映方法

プラグイン名を変更する際は複数のファイルを更新する必要があります。

> 出典: [Plugin manifest schema](https://code.claude.com/docs/en/plugins-reference.md#plugin-manifest-schema)

**変更すべきファイル**:

1. `.claude-plugin/plugin.json`の`name`フィールド:
```json
{
  "name": "new-plugin-name",
  "version": "1.1.0",  // バージョンもインクリメント推奨
  "description": "..."
}
```

2. marketplace.json内のプラグイン参照（該当する場合）:
```json
{
  "plugins": [
    {
      "name": "new-plugin-name",
      "source": "./plugins/new-plugin-name",
      "description": "..."
    }
  ]
}
```

**反映方法**:
```bash
# 旧プラグインをアンインストール
/plugin uninstall old-plugin-name

# 新プラグインをインストール
/plugin install new-plugin-name@marketplace-name
```

**ベストプラクティス**:
- プラグイン名変更時は必ずバージョンをインクリメント（MINOR以上）
- CHANGELOG.mdに名前変更を明記する
- ユーザーへの移行ガイドを提供する

**注意点**:
- 名前変更は破壊的変更になりうるため、慎重に行う
- 旧プラグイン名を使っている設定ファイルがないか確認する

### 5. マーケットプレイス名の変更時の反映方法

マーケットプレイス名を変更した場合、ユーザーは再登録が必要です。

> 出典: [Manage marketplaces](https://code.claude.com/docs/en/discover-plugins.md#manage-marketplaces)

**変更後の反映コマンド**:
```bash
# 旧マーケットプレイスを削除
/plugin marketplace remove old-marketplace-name

# 新しいマーケットプレイスを追加
/plugin marketplace add owner/repo

# またはフルURL
/plugin marketplace add https://github.com/owner/repo.git
```

**ベストプラクティス**:
- マーケットプレイス名の変更は極力避ける（ユーザーへの影響が大きい）
- 変更する場合は十分な告知期間を設ける
- リダイレクトや移行手順をREADMEに記載する

### 6. バージョン管理の仕組み

プラグインはセマンティック バージョニング（MAJOR.MINOR.PATCH）に従います。

> 出典: [Version management](https://code.claude.com/docs/en/plugins-reference.md#version-management)

**バージョン形式**:
```json
{
  "name": "my-plugin",
  "version": "2.1.0"
}
```

- **MAJOR（2.0.0）**: 互換性を破る変更（例：API仕様の変更、プラグイン名の変更）
- **MINOR（2.1.0）**: 新機能の追加（下位互換性あり、例：新しいスキルの追加）
- **PATCH（2.1.1）**: バグ修正（下位互換性あり）

**プレリリースバージョン**:
```json
{
  "version": "2.0.0-beta.1"
}
```

**ベストプラクティス**:
- 最初の安定版リリースは`1.0.0`から開始
- CHANGELOG.mdで変更履歴を記録
- 破壊的変更はMAJORバージョンをインクリメント
- マーケットプレイス内で各プラグインのバージョンを明示的に指定する

**注意点**:
- バージョン番号は必ず前進させる（ロールバックは避ける）
- タグとバージョンを一致させる（Git管理の場合）

### 7. プラグインのアップデート方法

ユーザー側でプラグインを更新するコマンド。

> 出典: [Manage installed plugins](https://code.claude.com/docs/en/discover-plugins.md#manage-installed-plugins)

**CLIでアップデート**:
```bash
# 特定のプラグインを更新
/plugin update plugin-name@marketplace-name

# マーケットプレイスの更新データを取得
/plugin marketplace update marketplace-name
```

**ベストプラクティス**:
- 定期的にマーケットプレイスを更新してプラグインの最新バージョンを確認
- 破壊的変更がある場合は、アップデート前にユーザーに警告

### 8. 複数の変更パターン別対応

#### パターン1: プラグイン内のスキル/コマンドを変更した場合

> 出典: [Test your plugins locally](https://code.claude.com/docs/en/plugins.md#test-your-plugins-locally)

```bash
# 1. ファイルを編集
# 2. Claude Codeを再起動
# 3. インストール済みの場合は自動的に読み込まれます

# 開発中の確認方法：
claude --plugin-dir ./my-plugin
```

#### パターン2: マーケットプレイスにプラグインを追加した場合

> 出典: [Manage marketplaces](https://code.claude.com/docs/en/discover-plugins.md#manage-marketplaces)

```bash
# 1. marketplace.jsonに新しいプラグインエントリを追加
# 2. Gitにコミット・プッシュ
# 3. ユーザー側で以下を実行：
/plugin marketplace update marketplace-name
```

#### パターン3: プラグイン名をリネームした場合

```bash
# 1. plugin.jsonの name フィールドを変更
# 2. marketplace.jsonで参照している場合も更新
# 3. バージョンをインクリメント（例: 1.0.0 → 2.0.0）
# 4. Gitにコミット・プッシュ
# 5. ユーザー側で：
/plugin marketplace update marketplace-name
/plugin uninstall old-plugin-name
/plugin install new-plugin-name@marketplace-name
```

#### パターン4: 既存プロジェクトのスキルをプラグイン化した場合

> 出典: [Convert existing configurations to plugins](https://code.claude.com/docs/en/plugins.md#convert-existing-configurations-to-plugins)

```bash
# 1. プラグインディレクトリを作成
mkdir -p my-plugin/.claude-plugin

# 2. plugin.jsonを作成
# 3. .claude/skills/をmy-plugin/skills/にコピー
# 4. plugin.json に "skills" フィールドを追加（必要に応じて）
# 5. インストール
/plugin install my-plugin@marketplace-name
```

### 9. 検証とトラブルシューティング

**ファイルバリデーション**:

> 出典: [Troubleshooting](https://code.claude.com/docs/en/plugin-marketplaces.md#troubleshooting)

```bash
# CLIで検証
claude plugin validate .

# または Claude Code内で
/plugin validate .
```

**よくあるエラーと解決方法**:

> 出典: [Common issues](https://code.claude.com/docs/en/plugins-reference.md#common-issues)

| エラー | 原因 | 解決方法 |
|--------|------|--------|
| `File not found: .claude-plugin/marketplace.json` | マニフェストが見つからない | `.claude-plugin/marketplace.json`を作成 |
| `Invalid JSON syntax: Unexpected token...` | JSON構文エラー | 構文をチェック（コンマの不足など） |
| `Duplicate plugin name "x"` | 同じ名前のプラグインが重複 | 各プラグインに一意の`name`を付ける |
| `Plugin skills not appearing` | キャッシュの問題 | `rm -rf ~/.claude/plugins/cache`して再インストール |

### 10. ベストプラクティスまとめ

> 出典: [Share your plugins](https://code.claude.com/docs/en/plugins.md#share-your-plugins)

1. **セマンティック バージョニングを使用**: 予測可能なリリース管理
2. **CHANGELOG.mdを記録**: 変更履歴を明確に
3. **READMEを作成**: インストール方法と使用方法をドキュメント化
4. **テストしてから公開**: `--plugin-dir`フラグで検証
5. **GitHubでホスト**: 推奨される配布方法
6. **marketplace.jsonのメタデータを完全に記入**: ユーザーの発見性向上

> 出典: [Host and distribute marketplaces](https://code.claude.com/docs/en/plugin-marketplaces.md#host-and-distribute-marketplaces)

**注意点**:
- 設定ファイルの変更後は、必ずClaude Codeを再起動してください
- プラグインはキャッシュディレクトリにコピーされるため、インストール後の直接編集は反映されません
- 公開前に必ず`/plugin validate`で検証してください

### 11. ローカルマーケットプレイスの追加方法

作業中のローカルリポジトリにあるmarketplaceを追加する方法について。

> 出典: [Add from local paths](https://code.claude.com/docs/en/discover-plugins.md#add-from-local-paths)

**基本的なコマンド**:
```bash
# ディレクトリを指定
/plugin marketplace add ./my-marketplace

# marketplace.jsonファイルを直接指定
/plugin marketplace add ./path/to/marketplace.json

# 絶対パスも使用可能
/plugin marketplace add /Users/username/Documents/my-marketplace
```

**GitHubリポジトリから追加**:

> 出典: [Add from GitHub](https://code.claude.com/docs/en/discover-plugins.md#add-from-github)

```bash
# 所有者/リポジトリ形式（最も簡潔）
/plugin marketplace add anthropics/claude-code

# 完全なHTTPS URL
/plugin marketplace add https://github.com/owner/repo.git

# SSH URL
/plugin marketplace add git@github.com:owner/repo.git

# 特定のブランチやタグを指定
/plugin marketplace add https://github.com/owner/repo.git#v1.0.0
```

**他のGitサービスから追加**:

> 出典: [Add from other Git hosts](https://code.claude.com/docs/en/discover-plugins.md#add-from-other-git-hosts)

```bash
# GitLab
/plugin marketplace add https://gitlab.com/company/plugins.git

# 自己ホストサーバー
/plugin marketplace add https://git.example.com/plugins.git
```

**ベストプラクティス**:
- 開発中はローカルパスを使用してテスト
- 本番配布時はGitHubリポジトリを使用
- 相対パスは`./`で始める習慣をつける

### 12. パスの書き方の詳細なルールと制限

> 出典: [Path behavior rules](https://code.claude.com/docs/en/plugins-reference.md#path-behavior-rules)

**正しいパスの書き方**:
```json
{
  "name": "my-plugin",
  "commands": [
    "./commands/",              // ✅ 正しい
    "./custom-commands/special.md"  // ✅ 正しい
  ],
  "agents": "./agents/",        // ✅ 正しい
  "skills": "./skills/"         // ✅ 正しい
}
```

**間違ったパスの書き方**:
```json
{
  "commands": [
    "commands/",              // ❌ ./ がない
    "../shared/commands"      // ❌ 上位ディレクトリ参照
  ]
}
```

**パストラバーサルの制限**:

> 出典: [Path traversal limitations](https://code.claude.com/docs/en/plugins-reference.md#path-traversal-limitations)

プラグインは自分のディレクトリ内のファイルにのみアクセスできます。上位ディレクトリ（`../`）への参照は禁止されています。

**エラーが発生する構造**:
```
marketplace/
├── .claude-plugin/
│   └── marketplace.json
├── plugins/
│   └── my-plugin/
│       ├── .claude-plugin/
│       │   └── plugin.json
│       └── commands/
└── shared-utils/  ← my-pluginからアクセスできない
    └── helper.js
```

エラー: `Path traversal not allowed`

**解決策1: シンボリックリンク使用**:

> 出典: [Working with external dependencies](https://code.claude.com/docs/en/plugins-reference.md#working-with-external-dependencies)

```bash
cd plugins/my-plugin
ln -s ../../shared-utils ./shared-utils
```

**解決策2: ディレクトリ構造を再構成**:
共有リソースをプラグイン内に移動するか、マーケットプレイス全体をプラグインとして扱う。

### 13. ${CLAUDE_PLUGIN_ROOT} 環境変数の使用

> 出典: [Environment variables](https://code.claude.com/docs/en/plugins-reference.md#environment-variables)

フック、MCPサーバー、スクリプト参照時は`${CLAUDE_PLUGIN_ROOT}`変数を使用します。

**理由**: プラグインは`~/.claude/plugins/cache`にコピーされるため、インストール位置が変わります。

> 出典: [Plugin caching and file resolution](https://code.claude.com/docs/en/plugins-reference.md#plugin-caching-and-file-resolution)

**正しい使用例**:
```json
{
  "hooks": {
    "PostToolUse": [
      {
        "hooks": [
          {
            "type": "command",
            "command": "${CLAUDE_PLUGIN_ROOT}/scripts/format.sh"
          }
        ]
      }
    ]
  },
  "mcp_servers": {
    "custom-server": {
      "command": "node",
      "args": ["${CLAUDE_PLUGIN_ROOT}/server/index.js"]
    }
  }
}
```

**注意点**:
- 相対パスではなく必ず`${CLAUDE_PLUGIN_ROOT}`を使用する
- プラグインキャッシュの仕組みを理解する
- 開発中の変更は再インストールで反映

### 14. インストールスコープ（user/project/local）の使い分け

> 出典: [Plugin installation scopes](https://code.claude.com/docs/en/plugins-reference.md#plugin-installation-scopes)

**3つのスコープの違い**:

| スコープ | 設定ファイル | 用途 | Git管理 | 優先度 |
|---------|------------|------|--------|--------|
| `user` | `~/.claude/settings.json` | 個人設定、全プロジェクト共通 | No | 低 |
| `project` | `.claude/settings.json` | チーム共有、プロジェクト固有 | **Yes** | 中 |
| `local` | `.claude/settings.local.json` | 個人設定、プロジェクト固有 | No（.gitignore推奨） | 高 |
| `managed` | `managed-settings.json` | エンタープライズ管理 | 読み取り専用 | 最高 |

**インストールコマンド**:
```bash
# User scope（個人用、全プロジェクト）
/plugin install plugin-name@marketplace --scope user

# Project scope（チーム共有、Git管理）
/plugin install plugin-name@marketplace --scope project

# Local scope（個人用、プロジェクト固有）
/plugin install plugin-name@marketplace --scope local
```

**使い分けのガイドライン**:

- **User scope**: 自分だけが使う汎用ツール（例: 個人的なコード整形ツール）
- **Project scope**: チーム全体で使うプロジェクト固有ツール（例: プロジェクトのlinterプラグイン）
- **Local scope**: 個人的なテストや開発中のプラグイン

**ベストプラクティス**:
- チームで共有するプラグインは`project`スコープを使用
- `.claude/settings.local.json`を`.gitignore`に追加
- エンタープライズ環境では`managed`スコープを活用

### 15. 開発中のmarketplaceのテスト方法

> 出典: [Test locally before distribution](https://code.claude.com/docs/en/plugin-marketplaces.md#test-locally-before-distribution)

**完全なテストワークフロー**:

```bash
# Step 1: marketplace.json を検証
/plugin validate ./my-marketplace

# Step 2: ローカルマーケットプレイスを追加
/plugin marketplace add ./my-marketplace

# Step 3: マーケットプレイスの確認
/plugin marketplace list

# 出力例:
# Known marketplaces:
#   - my-marketplace (local path: ./my-marketplace)
#   - claude-plugins-official (github: anthropics/claude-code)

# Step 4: プラグインをテストインストール（local scopeで）
/plugin install test-plugin@my-marketplace --scope local

# Step 5: インストール確認
/plugin
# → Installed タブでプラグインが表示されることを確認

# Step 6: 変更を反映する場合
# 6-1. ファイルを編集
# 6-2. Claude Codeを再起動
# 6-3. 再インストール
/plugin uninstall test-plugin@my-marketplace
/plugin install test-plugin@my-marketplace --scope local

# Step 7: キャッシュをクリア（変更が反映されない場合）
rm -rf ~/.claude/plugins/cache
# Claude Codeを再起動
```

> 出典: [Troubleshooting](https://code.claude.com/docs/en/discover-plugins.md#troubleshooting)

**開発時の反復サイクル**:

> 出典: [Test your plugins locally](https://code.claude.com/docs/en/plugins.md#test-your-plugins-locally)

```bash
# 1. コードを編集
# 2. /plugin validate . で検証
# 3. Claude Codeを再起動
# 4. 再インストール（必要に応じて）
# 5. テスト
# 6. 1に戻る
```

**実践的な例**:

```bash
# 開発中のmarketplaceディレクトリ構造を作成
mkdir -p my-dev-marketplace/.claude-plugin
mkdir -p my-dev-marketplace/plugins/my-tool/.claude-plugin
mkdir -p my-dev-marketplace/plugins/my-tool/commands

# marketplace.jsonを作成
cat > my-dev-marketplace/.claude-plugin/marketplace.json << 'EOF'
{
  "name": "dev-marketplace",
  "owner": { "name": "DevTeam" },
  "plugins": [
    {
      "name": "my-tool",
      "source": "./plugins/my-tool",
      "description": "My development tool",
      "version": "1.0.0"
    }
  ]
}
EOF

# plugin.jsonを作成
cat > my-dev-marketplace/plugins/my-tool/.claude-plugin/plugin.json << 'EOF'
{
  "name": "my-tool",
  "description": "My development tool",
  "version": "1.0.0"
}
EOF

# 検証してからインストール
/plugin validate ./my-dev-marketplace
/plugin marketplace add ./my-dev-marketplace
/plugin install my-tool@dev-marketplace --scope local
```

**注意点**:
- 開発中は常に`local`スコープを使用
- 変更後は必ず再起動とキャッシュクリアを確認
- 検証を習慣化する（`/plugin validate`）

### 16. チーム向けmarketplace配布のベストプラクティス

> 出典: [Require marketplaces for your team](https://code.claude.com/docs/en/plugin-marketplaces.md#require-marketplaces-for-your-team)

**プロジェクトの`.claude/settings.json`に設定**:

```json
{
  "extraKnownMarketplaces": {
    "team-plugins": {
      "source": {
        "source": "github",
        "repo": "myorg/team-plugins"
      }
    }
  },
  "enabledPlugins": {
    "code-formatter@team-plugins": true,
    "deployment-tools@team-plugins": true,
    "security-scanner@team-plugins": true
  }
}
```

**チームメンバーへの展開方法**:

```bash
# Step 1: リポジトリをクローン
git clone https://github.com/myorg/project.git
cd project

# Step 2: Claude Codeを起動
# → .claude/settings.json が自動的に読み込まれる
# → extraKnownMarketplaces が自動的に追加される
# → enabledPlugins が自動的にインストールされる

# Step 3: 確認
/plugin marketplace list
/plugin
# → Installed タブで確認
```

**ベストプラクティス**:
- マーケットプレイスはGitHubでホスト
- `.claude/settings.json`をバージョン管理
- `.claude/settings.local.json`は`.gitignore`に追加
- READMEにセットアップ手順を記載

> 出典: [Host and distribute marketplaces](https://code.claude.com/docs/en/plugin-marketplaces.md#host-and-distribute-marketplaces)

## 参照ドキュメント

今回の相談で参照した公式ドキュメント一覧：

- [Plugin marketplaces documentation](https://code.claude.com/docs/en/plugin-marketplaces.md)
- [Plugins reference](https://code.claude.com/docs/en/plugins-reference.md)
- [Plugins guide](https://code.claude.com/docs/en/plugins.md)
- [Discover and install plugins](https://code.claude.com/docs/en/discover-plugins.md)
- [Skills documentation](https://code.claude.com/docs/en/skills.md)
- [Plugin settings](https://code.claude.com/docs/en/settings.md#plugin-settings)

## 次のアクション

### 基本セットアップ
- 実際にplugin.jsonとmarketplace.jsonを作成してローカルでテストする
- バージョン管理のためのCHANGELOG.mdテンプレートを作成
- プラグイン配布のためのGitHubリポジトリをセットアップ
- ユーザー向けのREADMEとインストールガイドを作成

### ローカル開発環境
- 作業中のリポジトリで`/plugin marketplace add ./`を試す
- `local`スコープでプラグインをテストインストール
- キャッシュの仕組みを理解するため`~/.claude/plugins/cache`を確認
- パストラバーサルの制限を確認するため、意図的にエラーを発生させてみる

### チーム共有準備
- `.claude/settings.json`に`extraKnownMarketplaces`を設定
- `.gitignore`に`.claude/settings.local.json`を追加
- チーム向けのREADMEにセットアップ手順を記載
- `${CLAUDE_PLUGIN_ROOT}`を使ったフック/MCPサーバーの実装を試す

---

*このレポートは `/guide-chat` コマンドにより自動生成されました*

# Claude Code ガイド相談レポート

**日付**: 2025年12月30日
**トピック**: Plugin Marketplace の形式での作成方法

## 相談内容

- Plugin Marketplace の基本構造と作成フロー
- `marketplace.json` のスキーマと必須フィールド
- プラグイン定義の詳細設定方法
- プラグインソースの指定方法（相対パス、GitHub、Git URL）
- チーム向けマーケットプレイス設定
- ベストプラクティスとトラブルシューティング

## 重要な結論・学び

### 1. Plugin Marketplace の全体フロー

Plugin Marketplaceは、複数のプラグインをカタログ化し、ユーザーが検出・インストール可能にするシステムです。

**基本的な流れ**:
1. プラグイン作成（コマンド、エージェント、スキル等）
2. マーケットプレイスファイル（`marketplace.json`）作成
3. GitHubなどにホスト
4. ユーザーが `/plugin marketplace add` で追加・インストール

> 出典: [Plugin Marketplaces - Claude Code Documentation](https://code.claude.com/docs/en/plugin-marketplaces.md)

### 2. マーケットプレイスファイルの基本構造

`.claude-plugin/marketplace.json` を以下の形式で作成します：

**最小限の必須フィールド**:

```json
{
  "name": "company-tools",
  "owner": {
    "name": "DevTools Team",
    "email": "devtools@example.com"
  },
  "plugins": [
    {
      "name": "code-formatter",
      "source": "./plugins/formatter",
      "description": "Automatic code formatting on save"
    }
  ]
}
```

**主要フィールド**:

| フィールド | 型 | 説明 | 例 |
|-----------|----|----|-----|
| `name` | string | マーケットプレイスID（kebab-case） | `"acme-tools"` |
| `owner.name` | string | メンテナー名 | `"DevTools Team"` |
| `owner.email` | string（オプション） | メンテナーメール | `"devtools@example.com"` |
| `plugins` | array | プラグイン定義の配列 | 下記参照 |

> 出典: [Plugin Marketplaces Schema](https://code.claude.com/docs/en/plugin-marketplaces.md#marketplace-schema)

### 3. オプションメタデータの活用

マーケットプレイスにメタデータを追加すると、管理が容易になります：

```json
{
  "metadata": {
    "description": "Company-wide development tools",
    "version": "1.0.0",
    "pluginRoot": "./plugins"
  }
}
```

**`pluginRoot` の利点**:
- すべてのプラグインパスの相対パスが簡潔になる
- 例: `"source": "formatter"` が自動的に `"./plugins/formatter"` と解釈される

```json
{
  "metadata": {
    "pluginRoot": "./plugins"
  },
  "plugins": [
    {
      "name": "formatter",
      "source": "formatter"  // "./plugins/formatter"の意味
    }
  ]
}
```

### 4. プラグイン定義の詳細設定

各プラグインエントリで指定できるフィールド：

**必須フィールド**:
- `name`: プラグインID（kebab-case）
- `source`: プラグイン取得元

**オプションメタデータ**:

```json
{
  "name": "enterprise-tools",
  "source": {
    "source": "github",
    "repo": "company/enterprise-plugin"
  },
  "description": "Enterprise workflow automation",
  "version": "2.1.0",
  "author": {
    "name": "Enterprise Team",
    "email": "enterprise@example.com"
  },
  "homepage": "https://docs.example.com/plugins",
  "repository": "https://github.com/company/enterprise-plugin",
  "license": "MIT",
  "keywords": ["enterprise", "workflow"],
  "category": "productivity",
  "tags": ["automation", "ci-cd"],
  "strict": false
}
```

**重要なフィールド説明**:

| フィールド | 説明 |
|-----------|------|
| `strict` | `true`（デフォルト）: `plugin.json`が必須。`false`: マーケットプレイスエントリのみで定義可能 |
| `keywords` | ディスカバリー用タグ |
| `license` | SPDX識別子（例：`MIT`、`Apache-2.0`） |

> 出典: [Plugin Entries](https://code.claude.com/docs/en/plugin-marketplaces.md#plugin-entries)

### 5. プラグインソースの指定方法

#### 5-1. 相対パス（同じリポジトリ内）

```json
{
  "name": "my-plugin",
  "source": "./plugins/my-plugin"
}
```

#### 5-2. GitHubリポジトリ

```json
{
  "name": "github-plugin",
  "source": {
    "source": "github",
    "repo": "owner/plugin-repo"
  }
}
```

#### 5-3. GitリポジトリURL

```json
{
  "name": "git-plugin",
  "source": {
    "source": "url",
    "url": "https://gitlab.com/team/plugin.git"
  }
}
```

> 出典: [Plugin Sources](https://code.claude.com/docs/en/plugin-marketplaces.md#plugin-sources)

### 6. 実践例：完全なマーケットプレイスファイル

```json
{
  "name": "company-tools",
  "owner": {
    "name": "DevTools Team",
    "email": "devtools@example.com"
  },
  "metadata": {
    "description": "Company-wide development tools and plugins",
    "version": "2.0.0",
    "pluginRoot": "./plugins"
  },
  "plugins": [
    {
      "name": "code-formatter",
      "source": "formatter",
      "description": "Automatic code formatting on save",
      "version": "2.1.0",
      "author": {
        "name": "DevTools Team"
      },
      "category": "productivity",
      "tags": ["formatting", "code-quality"]
    },
    {
      "name": "deployment-tools",
      "source": {
        "source": "github",
        "repo": "company/deploy-plugin"
      },
      "description": "Deployment automation tools",
      "version": "1.5.0",
      "homepage": "https://docs.example.com/deploy",
      "license": "MIT",
      "category": "devops"
    },
    {
      "name": "security-scanner",
      "source": {
        "source": "url",
        "url": "https://gitlab.com/company/security-plugin.git"
      },
      "description": "Security vulnerability scanning",
      "version": "3.0.0",
      "keywords": ["security", "vulnerability"]
    }
  ]
}
```

### 7. コンポーネント指定の詳細設定

マーケットプレイスエントリで、プラグインの個別コンポーネント（コマンド、エージェント、フック等）をカスタマイズ可能：

```json
{
  "name": "enterprise-tools",
  "source": {
    "source": "github",
    "repo": "company/enterprise-plugin"
  },
  "commands": [
    "./commands/core/",
    "./commands/enterprise/",
    "./commands/experimental/preview.md"
  ],
  "agents": [
    "./agents/security-reviewer.md",
    "./agents/compliance-checker.md"
  ],
  "hooks": {
    "PostToolUse": [
      {
        "matcher": "Write|Edit",
        "hooks": [
          {
            "type": "command",
            "command": "${CLAUDE_PLUGIN_ROOT}/scripts/validate.sh"
          }
        ]
      }
    ]
  },
  "mcpServers": {
    "enterprise-db": {
      "command": "${CLAUDE_PLUGIN_ROOT}/servers/db-server",
      "args": ["--config", "${CLAUDE_PLUGIN_ROOT}/config.json"]
    }
  },
  "strict": false
}
```

**重要**: `${CLAUDE_PLUGIN_ROOT}` 変数を使用して、インストール時のパスを正しく解決します。

> 出典: [Plugin Manifest Schema](https://code.claude.com/docs/en/plugins-reference.md#plugin-manifest-schema)

### 8. プラグイン側の設定（plugin.json）

各プラグインディレクトリに `.claude-plugin/plugin.json` を配置：

```json
{
  "name": "code-formatter",
  "description": "Automatic code formatting",
  "version": "2.1.0",
  "author": {
    "name": "DevTools Team",
    "email": "devtools@example.com"
  }
}
```

**マーケットプレイスエントリとの関係**:
- マーケットプレイスの設定は `plugin.json` とマージされる
- `strict: true`（デフォルト）: `plugin.json` が必須
- `strict: false`: マーケットプレイスエントリだけで完全定義可能

> 出典: [Plugins Reference - Required Fields](https://code.claude.com/docs/en/plugins-reference.md#required-fields)

### 9. マーケットプレイスのホストと配布

#### GitHub（推奨）

```bash
git clone git@github.com:your-org/claude-plugins.git
# ファイル追加後
git push origin main
```

ユーザーが追加:
```
/plugin marketplace add your-org/claude-plugins
```

#### GitLab等のGitサービス

```
/plugin marketplace add https://gitlab.com/company/plugins.git
```

#### ローカルテスト

```
/plugin marketplace add ./my-local-marketplace
```

> 出典: [Host and Distribute Marketplaces](https://code.claude.com/docs/en/plugin-marketplaces.md#host-and-distribute-marketplaces)

### 10. チーム向けマーケットプレイス設定

プロジェクトの `.claude/settings.json` に追加してチーム全体で自動有効化：

```json
{
  "extraKnownMarketplaces": {
    "company-tools": {
      "source": {
        "source": "github",
        "repo": "your-org/claude-plugins"
      }
    }
  },
  "enabledPlugins": {
    "code-formatter@company-tools": true,
    "deployment-tools@company-tools": true
  }
}
```

> 出典: [Require Marketplaces for Your Team](https://code.claude.com/docs/en/plugin-marketplaces.md#require-marketplaces-for-your-team)

## ベストプラクティス

### 予約済み名を避ける

以下の名前は使用できません：
- `claude-code-marketplace`
- `claude-code-plugins`
- `claude-plugins-official`
- `anthropic-marketplace`
- `anthropic-plugins`
- `agent-skills`
- `life-sciences`

### 推奨ディレクトリ構造

```
my-marketplace/
├── .claude-plugin/
│   └── marketplace.json           ← マーケットプレイスファイル
├── plugins/
│   ├── formatter/
│   │   ├── .claude-plugin/
│   │   │   └── plugin.json
│   │   ├── commands/
│   │   │   └── format.md
│   │   └── hooks/
│   │       └── hooks.json
│   └── deploy-tools/
│       ├── .claude-plugin/
│       │   └── plugin.json
│       └── commands/
│           ├── deploy.md
│           └── status.md
├── README.md
└── CHANGELOG.md
```

### 検証とテスト

```bash
# JSON構文検証
claude plugin validate .

# または
/plugin validate .

# ローカルでテスト
/plugin marketplace add ./my-marketplace
/plugin install code-formatter@my-marketplace
```

> 出典: [Validation and Testing](https://code.claude.com/docs/en/plugin-marketplaces.md#validation-and-testing)

## トラブルシューティング

| 問題 | 原因 | 解決策 |
|------|------|--------|
| マーケットプレイスが読み込めない | JSONシンタックスエラー | `claude plugin validate`で確認 |
| プラグインが見つからない | `source`パスが不正 | パスの存在確認、相対パスチェック |
| ファイル見つからずエラー | プラグインが正しくコピーされない | `${CLAUDE_PLUGIN_ROOT}`を使用 |
| 重複プラグイン名エラー | 複数プラグインが同じ名前 | 各プラグインにユニークな`name`を設定 |

> 出典: [Troubleshooting](https://code.claude.com/docs/en/plugin-marketplaces.md#troubleshooting)

## 注意点

### エンタープライズマーケットプレイス制限

組織全体でマーケットプレイスを厳格に管理する場合、管理者設定で制限可能：

```json
{
  "strictKnownMarketplaces": [
    {
      "source": "github",
      "repo": "acme-corp/approved-plugins"
    }
  ]
}
```

> 出典: [Enterprise Marketplace Restrictions](https://code.claude.com/docs/en/plugin-marketplaces.md#enterprise-marketplace-restrictions)

### `${CLAUDE_PLUGIN_ROOT}` 変数の重要性

プラグイン内のスクリプトやMCPサーバーのパスを指定する際は、必ず `${CLAUDE_PLUGIN_ROOT}` を使用してください。これにより、インストール時のパスが正しく解決されます。

```json
{
  "mcpServers": {
    "db-server": {
      "command": "${CLAUDE_PLUGIN_ROOT}/servers/db-server",
      "args": ["--config", "${CLAUDE_PLUGIN_ROOT}/config.json"]
    }
  }
}
```

## 参照ドキュメント

- [Plugin Marketplaces - Claude Code Documentation](https://code.claude.com/docs/en/plugin-marketplaces.md)
- [Plugins Reference - Claude Code Documentation](https://code.claude.com/docs/en/plugins-reference.md)
- [Plugin Creation - Claude Code Documentation](https://code.claude.com/docs/en/plugins.md)

## 次のアクション

1. **サンプルマーケットプレイスの作成**: 小規模なマーケットプレイスを作成してテスト
2. **既存プラグインの移行**: 既存のプラグインをマーケットプレイス形式に移行
3. **チーム設定の検討**: `.claude/settings.json` でチーム向け設定を追加
4. **バリデーションの自動化**: CI/CDパイプラインに `claude plugin validate` を追加

---

*このレポートは `/claude-code:guide-chat` コマンドにより自動生成されました*

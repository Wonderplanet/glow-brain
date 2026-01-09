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

## 既存の作成支援ツール

### 11. Claude Code 提供の作成支援コマンド

Claude Codeには、**プラグインコンポーネント（skills, commands, subagents）を作成するための支援ツール**が既に実装されています。これらは `example-skills:skill-creator` を活用したカスタムコマンドです。

| コマンド | ファイルパス | 用途 |
|---------|------------|------|
| `/claude-code:create-skill` | `.claude/commands/claude-code/create-skill.md` | Claude Code Skillを対話的に作成 |
| `/claude-code:create-command` | `.claude/commands/claude-code/create-command.md` | カスタムスラッシュコマンドを生成 |
| `/claude-code:create-subagent` | `.claude/commands/claude-code/create-subagent.md` | Subagentを自動生成 |

> 出典: [Claude Code Skills](https://code.claude.com/docs/en/skills.md)

#### `/claude-code:create-skill` の使い方

**基本的な使い方**:

```bash
# 対話的に作成
/claude-code:create-skill

# 要件を直接指定
/claude-code:create-skill PDFファイルからテキストと表を抽出し、複数のPDFをマージする機能
```

**実装の流れ**:
1. 要件の確認と分析
2. `example-skills:skill-creator` スキルの起動
3. `.claude/skills/[skill-name]/` ディレクトリの作成
4. `SKILL.md` の生成（frontmatter + instructions）
5. スキルの動作検証

**生成されるファイル構造**:

```
.claude/skills/[skill-name]/
├── SKILL.md                  # メインスキルファイル
├── reference.md              # 参照ドキュメント（オプション）
└── examples/                 # サンプル（オプション）
```

#### `/claude-code:create-command` の使い方

**基本的な使い方**:

```bash
# 対話的に作成
/claude-code:create-command

# 要件を直接指定
/claude-code:create-command 指定したイシューを修正するコマンドを作成
```

**対話内容（AskUserQuestionツール使用）**:
1. **コマンド名**: `fix-issue`, `code-review`, `deploy-staging` など（kebab-case）
2. **配置ディレクトリ**: general, backend, frontend, または新規カテゴリ
3. **引数の有無**: 引数なし、1つの引数、複数の引数、`$ARGUMENTS`で一括
4. **Bashツール許可**: 不要、Git操作のみ、ビルドツール、データベース、包括的

**生成されるコマンドファイル**:

```markdown
---
description: [30文字以内の簡潔な説明]
argument-hint: [引数がある場合のヒント]
allowed-tools: [許可するツールのリスト]
---

# [コマンドのタイトル]

## タスク

1. **ステップ1**
   - 詳細な手順

2. **ステップ2**
   - 期待される動作

## 注意事項

[重要な注意点]
```

#### `/claude-code:create-subagent` の使い方

**基本的な使い方**:

```bash
# 対話的に作成
/claude-code:create-subagent

# 要件を直接指定
/claude-code:create-subagent テストを自動実行してエラーを修正するsubagent
```

**対話内容**:
1. **Subagent名**: `test-runner`, `security-reviewer`, `performance-analyzer` など
2. **使用ツール**: 全ツール継承、読み取りのみ、編集も可能、カスタム
3. **モデル選択**: `inherit`（推奨）, `sonnet`, `haiku`, `opus`
4. **積極的な自動呼び出し**: はい（descriptionに「PROACTIVELY」を含める）/ いいえ

**生成されるSubagentファイル**:

```markdown
---
name: [小文字とハイフンのみ]
description: [具体的な説明。自動呼び出しの場合は「PROACTIVELY」を含める]
tools: [指定されたツール]
model: inherit
---

# あなたの役割

You are a [具体的な専門性] specialized in [専門領域].

## ミッション

When invoked:
1. [最初にやるべきこと]
2. [次にやるべきこと]
...
```

#### Skill vs Command vs Subagent の選択基準

| 特性 | Skill | Command | Subagent |
|------|-------|---------|----------|
| **トリガー** | 自動検出（model-invoked） | 明示的（`/command`実行） | 自動または明示的 |
| **複雑さ** | 複雑（複数ファイル） | シンプル | 非常に複雑（独立した文脈） |
| **ベストユース** | 専門知識の共有 | 定型作業 | 並行処理・マルチステップ |
| **ファイル数** | 複数（reference.md等） | 1ファイル | 1ファイル（独立実行） |
| **例** | PDF処理、データ分析 | Issue修正、コード整形 | テスト実行、セキュリティレビュー |

> 出典: [Skills - When to Use](https://code.claude.com/docs/en/skills.md#when-to-use-skills-versus-other-options)

### 12. Plugin Marketplace作成における既存ツールの活用

**重要な発見**: Plugin Marketplace自体の生成を自動化するビルトインツールは現時点では存在しません。

**推奨される作成フロー**:

```
1. コンポーネント作成（既存ツール使用）
   ├─ /claude-code:create-skill → プラグイン用スキルを作成
   ├─ /claude-code:create-command → プラグイン用コマンドを作成
   └─ /claude-code:create-subagent → プラグイン用subagentを作成

2. プラグイン構造の構築
   └─ 作成したコンポーネントを plugins/[plugin-name]/ に配置

3. plugin.json の作成
   └─ .claude-plugin/plugin.json でプラグイン定義

4. marketplace.json の作成
   └─ .claude-plugin/marketplace.json でカタログ定義

5. 検証
   └─ claude plugin validate . または /plugin validate

6. ホスト＆配布
   └─ GitHubやGitLabにプッシュ
```

**現状の制約**:
- ✅ プラグイン内のコンポーネント（skills, commands, subagents）の作成は支援ツールあり
- ❌ Marketplace構造全体（`marketplace.json`生成、ディレクトリ構造）の自動生成ツールはなし

**解決策**: Marketplace作成を自動化するカスタムスキルの作成が有効

## 参照ドキュメント

- [Plugin Marketplaces - Claude Code Documentation](https://code.claude.com/docs/en/plugin-marketplaces.md)
- [Plugins Reference - Claude Code Documentation](https://code.claude.com/docs/en/plugins-reference.md)
- [Plugin Creation - Claude Code Documentation](https://code.claude.com/docs/en/plugins.md)
- [Claude Code Skills](https://code.claude.com/docs/en/skills.md)
- [Common Workflows](https://code.claude.com/docs/en/common-workflows.md)
- [Sub-Agents](https://code.claude.com/docs/en/sub-agents.md)

## 次のアクション

1. **サンプルマーケットプレイスの作成**: 小規模なマーケットプレイスを作成してテスト
2. **既存プラグインの移行**: 既存のプラグインをマーケットプレイス形式に移行
3. **チーム設定の検討**: `.claude/settings.json` でチーム向け設定を追加
4. **バリデーションの自動化**: CI/CDパイプラインに `claude plugin validate` を追加

---

*このレポートは `/claude-code:guide-chat` コマンドにより自動生成されました*

---
description: Plugin Marketplaceを対話的に作成
argument-hint: [オプション: marketplace名または要件]
---

# Plugin Marketplace作成コマンド

このコマンドは、Claude Code Plugin Marketplaceの完全な構造を対話的に生成します。marketplace.json、plugin.json、ディレクトリ構造、テンプレートファイルを自動作成し、検証までサポートします。

## 概要

Plugin Marketplaceは、複数のClaude Code pluginをカタログ化し、ユーザーが検出・インストール可能にするシステムです。このコマンドは、plugin-marketplace-creatorスキルを活用して、プロフェッショナルなmarketplace構造を簡単に作成できます。

## Plugin Marketplaceとは

- **目的**: 複数のプラグインを一元管理・配布
- **ユースケース**:
  - チーム向けプラグイン配布
  - 企業内ツールの標準化
  - オープンソースプラグインコレクション
- **配布方法**: GitHub、GitLab、ローカルパスで共有可能

## 引数の使い方

### パターン1: 引数なしで対話的に作成（推奨）

```bash
/claude-code:create-marketplace
```

このパターンでは、plugin-marketplace-creatorスキルが対話的にmarketplaceの要件をヒアリングし、最適な構成を提案します。

### パターン2: Marketplace名を指定

```bash
/claude-code:create-marketplace company-tools
```

指定したmarketplace名で作成を開始します。

### パターン3: 要件を詳細に指定

```bash
/claude-code:create-marketplace チーム向けの開発ツールmarketplaceで、3つのプラグインを含める
```

## タスク

あなたのタスクは、plugin-marketplace-creatorスキルを活用して、ユーザーの要件に基づいたPlugin Marketplaceを作成することです。

### ステップ1: 要件の確認

引数が提供されているか確認します：

- **引数あり（$1が存在）**: その要件を使用
- **引数なし**: 「新規Plugin Marketplaceを作成します」とユーザーに伝える

### ステップ2: plugin-marketplace-creatorスキルの活用

**重要**: このコマンドは直接実装するのではなく、`plugin-marketplace-creator`スキルに委譲します。スキルが持つ専門知識とワークフローを活用してください。

スキルを呼び出す際の要件：

```
Plugin Marketplaceを作成します。

$1が指定されている場合の要件: $1

以下のフローで進めてください：
1. Marketplace名と配置ディレクトリの確認
2. オーナー情報とメタデータの収集
3. プラグインの追加（対話的または既存プラグインのインポート）
4. marketplace.jsonとplugin.jsonの生成
5. 検証の実行

既存の /claude-code:create-skill、/claude-code:create-command、/claude-code:create-subagent コマンドとの連携も考慮してください。
```

### ステップ3: スキルに任せる

plugin-marketplace-creatorスキルが以下を自動実行します：

1. **基本情報の収集**:
   - Marketplace名（kebab-case）
   - 配置ディレクトリ
   - オーナー情報（名前、メール）
   - メタデータ（description, version, pluginRoot）

2. **プラグイン追加**:
   - 対話的にプラグイン情報を収集
   - 既存プラグインのインポート
   - `/claude-code:create-*`コマンドとの連携

3. **構造生成**:
   - ディレクトリ構造の作成
   - marketplace.jsonの生成
   - 各プラグインのplugin.jsonの生成
   - テンプレートファイルの配置

4. **検証**:
   - `claude plugin validate`での自動検証
   - エラーがあれば修正提案

あなたはスキルの指示に従い、必要な情報を提供してください。

### ステップ4: 完成後の確認

スキルが完了したら、以下を確認してユーザーに報告します：

- 作成されたMarketplaceの配置場所
- marketplace.jsonの内容概要
- 含まれるプラグインの一覧
- 次のステップ（検証、Git管理、配布方法）

**確認コマンド例**:
```bash
# 構造確認
ls -la <marketplace-directory>

# marketplace.json確認
cat <marketplace-directory>/.claude-plugin/marketplace.json

# 検証
cd <marketplace-directory> && claude plugin validate .
```

## 既存ツールとの連携

### プラグインコンポーネントの作成と追加

Marketplace作成後、以下のコマンドでプラグインコンポーネントを作成し、marketplaceに追加できます：

```bash
# スキルを作成
/claude-code:create-skill PDFファイル処理機能

# コマンドを作成
/claude-code:create-command デプロイ自動化コマンド

# Subagentを作成
/claude-code:create-subagent テスト実行エージェント

# 作成したコンポーネントをmarketplaceに追加
# このコマンドを再度実行して、既存marketplaceにプラグインを追加
/claude-code:create-marketplace
# → 「既存Marketplaceにプラグイン追加」を選択
```

## 使用例

### 例1: 基本的な使い方

```bash
/claude-code:create-marketplace
```

→ 対話的にmarketplace名、オーナー情報、プラグインを設定

### 例2: Marketplace名を指定

```bash
/claude-code:create-marketplace team-plugins
```

→ `team-plugins` という名前のmarketplaceを作成

### 例3: 詳細な要件を指定

```bash
/claude-code:create-marketplace 社内開発ツール用のmarketplaceで、フォーマッター、デプロイツール、セキュリティスキャナーの3つのプラグインを含める
```

→ 指定された要件に基づいてmarketplaceを作成

## ベストプラクティス

### Marketplace名

- 小文字とハイフンのみ使用
- 最大64文字
- 予約済み名を避ける（`claude-code-marketplace`など）
- 例: `company-tools`, `team-plugins`, `acme-devtools`

### ディレクトリ構造

推奨される構造：

```
my-marketplace/
├── .claude-plugin/
│   └── marketplace.json
├── plugins/
│   ├── plugin-1/
│   │   ├── .claude-plugin/
│   │   │   └── plugin.json
│   │   ├── commands/
│   │   ├── skills/
│   │   └── agents/
│   └── plugin-2/
│       └── ...
├── README.md
└── .gitignore
```

### Git管理

Marketplace作成後、Gitで管理することを推奨：

```bash
cd <marketplace-directory>
git init
git add .
git commit -m "Initial marketplace setup"

# GitHubにプッシュ（リモートリポジトリを作成済みの場合）
git remote add origin git@github.com:your-org/your-marketplace.git
git push -u origin main
```

### チーム配布

**GitHubで配布**:
```bash
# ユーザーが追加
/plugin marketplace add your-org/your-marketplace
```

**ローカルテスト**:
```bash
# 開発中はローカルパスでテスト
/plugin marketplace add ./path/to/marketplace
```

**プロジェクト設定**:
`.claude/settings.json`でチーム全体に自動配布：

```json
{
  "extraKnownMarketplaces": {
    "company-tools": {
      "source": {
        "source": "github",
        "repo": "your-org/company-tools"
      }
    }
  },
  "enabledPlugins": {
    "formatter@company-tools": true,
    "deploy-tools@company-tools": true
  }
}
```

## トラブルシューティング

### Marketplaceが検証エラーになる

```bash
# 検証実行
cd <marketplace-directory>
claude plugin validate .

# エラーメッセージを確認して修正
# marketplace.jsonのJSON構文エラーが最も一般的
```

### プラグインが見つからない

- `source`パスが正しいか確認
- 相対パスの場合、`metadata.pluginRoot`を設定
- プラグインディレクトリに`.claude-plugin/plugin.json`が存在するか確認（`strict: true`の場合）

### ${CLAUDE_PLUGIN_ROOT}が解決されない

- プラグイン内のパス指定には必ず`${CLAUDE_PLUGIN_ROOT}`を使用
- 例: `${CLAUDE_PLUGIN_ROOT}/scripts/helper.py`

## 参考資料

- スキルドキュメント: `.claude/skills/plugin-marketplace-creator/SKILL.md`
- Marketplace Schema: `.claude/skills/plugin-marketplace-creator/references/marketplace-schema.md`
- Plugin Schema: `.claude/skills/plugin-marketplace-creator/references/plugin-schema.md`
- 連携ガイド: `.claude/skills/plugin-marketplace-creator/references/integration-guide.md`
- 公式ドキュメント: claude-code-guideエージェントに質問

## 注意事項

- **シンプルな場合**: 単一プラグインのみの場合は、marketplace不要です。直接プラグインを配布してください。
- **予約済み名**: `claude-code-marketplace`などの予約済み名は使用できません
- **検証必須**: 配布前に必ず`claude plugin validate .`で検証してください

---

それでは、plugin-marketplace-creatorスキルを活用して、プロフェッショナルなPlugin Marketplaceを作成しましょう！

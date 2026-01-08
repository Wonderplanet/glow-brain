---
name: "Creating Claude Code Subagents"
description: 新しいClaude Code subagentを作成する際に使用。エージェントの専門分野、トリガー条件、作業フロー、モデル選択を定義し、.claude/agents/ディレクトリに新規エージェントを生成する。
---

# サブエージェント作成

Claude Code subagentsを作成するためのスキルです。指定された要件に基づいて新しいサブエージェントファイルを`.claude/agents/`ディレクトリに作成します。

## Instructions

### 1. サブエージェントの基本構造を理解する

サブエージェントの必須要素とYAML frontmatter形式を確認します。

参照: **[subagent-structure.md](subagent-structure.md)**

### 2. エージェントパターンを選択する

作成したいエージェントの専門分野に応じて、適切なパターンを選択します。

参照リスト:
- **[API開発系パターン](patterns/api-development.md)** - API実装、テスト、エラー修正など
- **[テスト・品質管理系パターン](patterns/testing.md)** - PHPStan、phpcs、Deptracなどの品質チェック
- **[運用・管理系パターン](patterns/operations.md)** - データベース、ブラウザテスト、設計フローなど

### 3. 既存エージェントから学ぶ

プロジェクトの既存エージェントの実例を参考にして、ベストプラクティスを理解します。

参照: **[existing-agents.md](examples/existing-agents.md)**

### 4. エージェントファイルを作成する

`.claude/agents/`ディレクトリに新しいマークダウンファイル（`{エージェント名}.md`）を作成し、以下を含めます：

- YAML frontmatter（name、description、model、color）
- エージェントの役割と責任
- 基本原則と制約事項
- 標準作業フロー
- 品質保証基準

### 5. トリガー条件を明確にする

description に具体的な使用タイミングとトリガー条件を含め、Claudeが自動的にエージェントを呼び出せるようにします。

## 参照ドキュメント

- **[subagent-structure.md](subagent-structure.md)** - サブエージェントの構造と必須フィールド
- **[patterns/api-development.md](patterns/api-development.md)** - API開発系エージェントパターン
- **[patterns/testing.md](patterns/testing.md)** - テスト・品質管理系エージェントパターン
- **[patterns/operations.md](patterns/operations.md)** - 運用・管理系エージェントパターン
- **[examples/existing-agents.md](examples/existing-agents.md)** - 既存エージェントの実例

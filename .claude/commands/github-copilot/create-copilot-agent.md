---
description: GitHub Copilot Agent を作成
argument-hint: [agent-name]
allowed-tools: WebFetch(*), WebSearch(*)
---

# GitHub Copilot Agent 作成

GitHub Copilot Chat で使えるカスタムエージェント（Claude CodeのSubagentに相当）を、公式ドキュメントとベストプラクティスに基づいて作成します。

## 引数

- `$1`: 作成するエージェントの名前（例: `code-reviewer`, `test-generator`）

## 公式ドキュメント

- [GitHub Copilot Custom Agents 公式ドキュメント](https://docs.github.com/en/copilot/using-github-copilot/using-extensions-to-integrate-external-tools-with-copilot-chat)
- [Copilot Agent 開発ガイド](https://docs.github.com/en/copilot/building-copilot-extensions)
- [Agent Prompt Engineering ベストプラクティス](https://docs.github.com/en/copilot/building-copilot-extensions/building-a-copilot-agent-for-your-copilot-extension/configuring-your-copilot-agent-to-communicate-with-github-copilot)
- [Copilot Extensions API リファレンス](https://docs.github.com/en/rest/copilot/copilot-extensions)

## タスク

### 1. 最新の公式ドキュメントを確認

WebFetch または WebSearch を使用して以下を調査：
- GitHub Copilot Custom Agentの最新仕様
- 推奨されるエージェントプロンプトパターン
- ベストプラクティス

参考URL:
- https://docs.github.com/en/copilot/reference/custom-agents-configuration
- https://github.blog/ai-and-ml/github-copilot/how-to-write-a-great-agents-md-lessons-from-over-2500-repositories/

### 2. エージェントの要件を明確化

AskUserQuestion ツールを使用して、以下の情報を収集してください：

**質問1: エージェントの目的**
- このエージェントが解決する主な課題は何か
- 対象となるユースケース（例: コードレビュー、テスト生成、実装計画）

**質問2: 必要な機能とツール**
- 特定のツールに制限する必要があるか（例: read, search, editのみ）
- 全ツールへのアクセスが必要か

**質問3: 対象言語/フレームワーク**
- 特定の言語に特化するか（例: TypeScript, Python, Go）
- 特定のフレームワークに特化するか（例: React, Laravel）
- 汎用的なエージェントか

**質問4: 境界線**
- 絶対に実行してはいけない操作は何か（例: 本番コードの変更、設定ファイルの削除）
- 確認が必要な操作は何か

### 3. エージェントファイルの作成

`.github/agents/$1.agent.md` にエージェントファイルを作成します。

必ず以下を含めてください：
1. YAML frontmatter（name, descriptionは必須）
2. Purpose（目的）
3. Core Responsibilities（主要な責任）
4. Guidelines（ガイドライン）
5. Boundaries（境界線: ✅⚠️🚫）
6. Workflow（ワークフロー）
7. Commands（実行可能なコマンド）
8. Examples（具体的な使用例）
9. Limitations（制限事項）
10. Security Considerations（セキュリティ考慮事項）

#### ファイル構造（公式推奨フォーマット）

```markdown
---
name: $1
description: [エージェントの1行説明]
# tools: []  # オプション: 特定ツールのみに制限する場合
# model: Claude Sonnet 4  # オプション: 使用するAIモデルを指定
# target: vscode  # オプション: vscode または github-copilot
---

# $1 Agent

## Purpose

このエージェントは[目的]を実現します。

## Core Responsibilities

1. **[責任1]**
   - [詳細な説明]
   - [期待される動作]

2. **[責任2]**
   - [詳細な説明]
   - [期待される動作]

## Guidelines

### Code Quality
[コード品質基準]

### Best Practices
[適用するベストプラクティス]

### Boundaries

- ✅ **常に実行すべきこと**:
  - [項目1]
  - [項目2]

- ⚠️ **確認が必要なこと**:
  - [項目1]
  - [項目2]

- 🚫 **絶対に実行してはいけないこと**:
  - [項目1]
  - [項目2]

## Workflow

1. [ステップ1の説明]
2. [ステップ2の説明]
3. [ステップ3の説明]

## Commands

実行可能なコマンド例：
\`\`\`bash
npm test
npm run build
\`\`\`

## Examples

### Example 1: [ユースケース1]

User Input:
\`\`\`
@copilot /$1 [example command]
\`\`\`

Expected Response:
\`\`\`
[期待される応答の詳細]
\`\`\`

### Example 2: [ユースケース2]

User Input:
\`\`\`
@copilot /$1 [example command]
\`\`\`

Expected Response:
\`\`\`
[期待される応答の詳細]
\`\`\`

## Limitations

- [制限事項1]
- [制限事項2]

## Security Considerations

- [セキュリティ考慮事項1]
- [セキュリティ考慮事項2]
```

### 4. テストとバリデーション

作成したエージェントが正しく動作することを確認するためのチェックリスト：

- [ ] エージェントファイルが `.github/agents/$1.agent.md` に配置されている
- [ ] YAML frontmatterが正しく記述されている（name, description必須）
- [ ] エージェントの指示がMarkdown形式で明確に記述されている
- [ ] 実用的な例が提供されている（最低2つ）
- [ ] 境界線（✅⚠️🚫）が明確に定義されている
- [ ] 実行可能なコマンドが記載されている
- [ ] セキュリティリスクが考慮されている

## ベストプラクティス

### プロンプト設計

1. **明確な役割定義**: エージェントの責任範囲を明確に
2. **具体的な例**: 少なくとも3つの実用的な例を提供
3. **段階的な指示**: ステップバイステップのワークフローを記述
4. **コンテキスト利用**: ファイルパスや行番号を `file_path:line_number` 形式で参照
5. **エラーハンドリング**: 想定されるエラーケースへの対応を明記

### コード品質

1. **一貫性**: プロジェクトの既存のコーディング規約に従う
2. **最小限の変更**: 必要最小限の変更のみを行う
3. **テスタビリティ**: テスト可能なコードを生成
4. **ドキュメント**: 自己説明的なコードと必要に応じたコメント

### セキュリティ

1. **入力検証**: ユーザー入力を常に検証
2. **機密情報**: 秘密鍵やトークンをハードコードしない
3. **権限**: 必要最小限の権限のみを要求
4. **監査**: セキュリティ関連の操作をログに記録

## 注意事項

- GitHub Copilot Custom Agent は GitHub Copilot Chat および VS Code で利用可能です
- エージェントファイルは `.github/agents/` ディレクトリに配置する必要があります
- ファイル拡張子は `.agent.md` を使用してください
- エージェント名は小文字とハイフンのみを使用してください
- YAML frontmatterのname, descriptionは必須フィールドです
- 公式ドキュメントは頻繁に更新されるため、最新情報を確認してください

## 出力形式

以下のファイルを生成してください：

1. **エージェントファイル**: `.github/agents/$1.agent.md`
   - 必須のYAML frontmatter（name, description）を含む
   - Markdown形式でエージェントの詳細な指示を記述

ファイル作成後、以下を提供してください：
- 作成したファイルのパス
- 基本的な使用方法の例
- GitHub Copilot ChatまたはVS Codeでの起動方法

---

**参考資料:**

- [GitHub Copilot Extensions Documentation](https://docs.github.com/en/copilot/building-copilot-extensions)
- [Building a Copilot Agent](https://docs.github.com/en/copilot/building-copilot-extensions/building-a-copilot-agent-for-your-copilot-extension)
- [Copilot Extensions Quickstart](https://docs.github.com/en/copilot/building-copilot-extensions/creating-a-copilot-extension/creating-a-github-app-for-your-copilot-extension)
- [Agent Prompt Engineering Best Practices](https://docs.github.com/en/copilot/building-copilot-extensions/building-a-copilot-agent-for-your-copilot-extension/configuring-your-copilot-agent-to-communicate-with-github-copilot)

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

まず、GitHub Copilot Agent の最新の公式ドキュメントを参照して、現在のベストプラクティスを確認してください：

- WebFetch または WebSearch を使用して以下を調査：
  - GitHub Copilot Agent の基本構造
  - 推奨されるプロンプトパターン
  - ツール統合の方法
  - エラーハンドリングのベストプラクティス
  - セキュリティ考慮事項

### 2. エージェントの要件を明確化

AskUserQuestion ツールを使用して、以下の情報を収集してください：

**質問1: エージェントの目的**
- エージェントが解決する主な課題は何か
- 対象となるユースケース（例: コードレビュー、テスト生成、ドキュメント作成）

**質問2: 必要な機能**
- ファイル読み取りが必要か
- API呼び出しが必要か
- データベースアクセスが必要か
- その他の外部ツール統合が必要か

**質問3: 対象言語/フレームワーク**
- 特定の言語に特化するか（例: TypeScript, Python, Go）
- 特定のフレームワークに特化するか（例: React, Django, Laravel）
- 汎用的なエージェントか

**質問4: 出力形式**
- コード生成が主か
- 分析結果のレポートが主か
- インタラクティブな提案が主か

### 3. エージェントプロンプトファイルの作成

`.github/copilot/agents/$1.md` にエージェントプロンプトファイルを作成します。

#### ファイル構造（公式推奨フォーマット）

```markdown
# $1 Agent

[エージェントの1行説明]

## Purpose

[このエージェントの目的と解決する課題]

## Capabilities

- [機能1]
- [機能2]
- [機能3]

## Usage

\`\`\`
@copilot /$1 [command]
\`\`\`

### Examples

\`\`\`
@copilot /$1 review this PR
@copilot /$1 generate tests for UserService
\`\`\`

## System Instructions

You are a specialized GitHub Copilot agent focused on [specific domain].

### Core Responsibilities

1. **[責任1]**
   - [詳細な説明]
   - [期待される動作]

2. **[責任2]**
   - [詳細な説明]
   - [期待される動作]

### Guidelines

- **Code Quality**: [コード品質基準]
- **Best Practices**: [適用するベストプラクティス]
- **Error Handling**: [エラー処理方針]
- **Documentation**: [ドキュメント要件]

### Workflow

1. [ステップ1]
2. [ステップ2]
3. [ステップ3]

### Tools and Context

- **File Access**: [ファイルアクセスパターン]
- **API Usage**: [API使用方法]
- **External Tools**: [外部ツール統合]

### Response Format

[期待される応答形式]

### Examples

#### Example 1: [ユースケース1]

User: `@copilot /$1 [example command]`

Expected Response:
\`\`\`
[期待される応答例]
\`\`\`

#### Example 2: [ユースケース2]

User: `@copilot /$1 [example command]`

Expected Response:
\`\`\`
[期待される応答例]
\`\`\`

## Limitations

- [制限事項1]
- [制限事項2]

## Security Considerations

- [セキュリティ考慮事項1]
- [セキュリティ考慮事項2]
```

### 4. GitHub Copilot Agent 設定ファイルの作成（オプション）

`.github/copilot/agents/$1.json` に設定ファイルを作成します（必要に応じて）：

```json
{
  "name": "$1",
  "description": "[エージェントの簡潔な説明]",
  "version": "1.0.0",
  "capabilities": {
    "file_access": true,
    "web_search": false,
    "code_execution": false
  },
  "context": {
    "max_tokens": 4000,
    "temperature": 0.7
  },
  "tools": []
}
```

### 5. 使用例とドキュメントの作成

`docs/copilot-agents/$1.md` に詳細なドキュメントを作成します：

```markdown
# $1 Agent - 使用ガイド

## 概要

[エージェントの詳細な説明]

## インストール

\`\`\`bash
# GitHub Copilot Agent は自動的に認識されます
# .github/copilot/agents/$1.md が配置されていることを確認
\`\`\`

## 使用方法

### 基本的な使い方

\`\`\`
@copilot /$1 [command]
\`\`\`

### 実用例

#### ユースケース1: [タイトル]

\`\`\`
@copilot /$1 [具体的なコマンド]
\`\`\`

**期待される結果:**
[説明]

#### ユースケース2: [タイトル]

\`\`\`
@copilot /$1 [具体的なコマンド]
\`\`\`

**期待される結果:**
[説明]

## ベストプラクティス

1. **[プラクティス1]**
   - [説明]

2. **[プラクティス2]**
   - [説明]

## トラブルシューティング

### 問題: [一般的な問題1]

**解決策:**
[解決方法]

### 問題: [一般的な問題2]

**解決策:**
[解決方法]

## 制限事項

- [制限1]
- [制限2]

## 参考リンク

- [GitHub Copilot Custom Agents 公式ドキュメント](https://docs.github.com/en/copilot/using-github-copilot/using-extensions-to-integrate-external-tools-with-copilot-chat)
- [プロジェクト固有のドキュメント]
```

### 6. テストとバリデーション

作成したエージェントが正しく動作することを確認するためのチェックリスト：

- [ ] プロンプトファイルが正しいパスに配置されている
- [ ] プロンプトが明確で具体的
- [ ] 例が十分に提供されている
- [ ] エラーハンドリングが考慮されている
- [ ] セキュリティリスクが最小化されている
- [ ] ドキュメントが完備している
- [ ] 実際の GitHub Copilot Chat でテストできる

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

- GitHub Copilot Agent は GitHub Copilot Chat でのみ利用可能です
- プロンプトファイルは `.github/copilot/agents/` ディレクトリに配置する必要があります
- エージェント名は小文字とハイフンのみを使用してください
- 公式ドキュメントは頻繁に更新されるため、最新情報を確認してください
- 作成したエージェントは組織内でのみ使用可能です（パブリック公開には追加の設定が必要）

## 出力形式

以下のファイルを生成してください：

1. **エージェントプロンプト**: `.github/copilot/agents/$1.md`
2. **設定ファイル**（オプション）: `.github/copilot/agents/$1.json`
3. **ドキュメント**: `docs/copilot-agents/$1.md`

各ファイルのパスと内容を明確に示し、使用方法の例を提供してください。

---

**参考資料:**

- [GitHub Copilot Extensions Documentation](https://docs.github.com/en/copilot/building-copilot-extensions)
- [Building a Copilot Agent](https://docs.github.com/en/copilot/building-copilot-extensions/building-a-copilot-agent-for-your-copilot-extension)
- [Copilot Extensions Quickstart](https://docs.github.com/en/copilot/building-copilot-extensions/creating-a-copilot-extension/creating-a-github-app-for-your-copilot-extension)
- [Agent Prompt Engineering Best Practices](https://docs.github.com/en/copilot/building-copilot-extensions/building-a-copilot-agent-for-your-copilot-extension/configuring-your-copilot-agent-to-communicate-with-github-copilot)

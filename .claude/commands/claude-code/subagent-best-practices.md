# Claude Code Subagent 作成ベストプラクティス

このドキュメントは、公式ドキュメントに基づく、高品質なClaude Code subagentを作成するためのベストプラクティスガイドです。

## 目次

1. [Subagentとは](#subagentとは)
2. [ファイル構造と設定](#ファイル構造と設定)
3. [命名規則](#命名規則)
4. [プロンプト設計](#プロンプト設計)
5. [ツール指定](#ツール指定)
6. [モデル選択](#モデル選択)
7. [良い例と悪い例](#良い例と悪い例)
8. [チーム運用](#チーム運用)

---

## Subagentとは

Subagent（サブエージェント）は、特定のタスクに特化したClaude Codeのエージェントです。メインの会話から切り離された独立したコンテキストで動作し、以下の利点があります：

- **専門性**: 特定のタスクに集中した専門的な処理
- **再利用性**: 同じタスクを繰り返し実行可能
- **コンテキスト管理**: メイン会話のコンテキストを消費しない
- **チーム共有**: プロジェクト全体で共有可能

---

## ファイル構造と設定

### 配置場所

```
# プロジェクトレベル（チーム共有）
.claude/agents/your-agent.md

# ユーザーレベル（個人用）
~/.claude/agents/your-agent.md
```

### 基本構造

```markdown
---
name: your-sub-agent-name
description: いつこのsubagentを呼び出すべきかの説明
tools: tool1, tool2, tool3  # オプション
model: inherit              # オプション
permissionMode: default     # オプション
skills: skill1, skill2      # オプション
---

ここにsubagentのシステムプロンプトを記述します。
複数段落で、ロール、機能、アプローチを明確に定義してください。
```

### 設定フィールド詳細

| フィールド | 必須 | 説明 | 推奨値 |
|-----------|-----|------|--------|
| `name` | ✅ | subagentの識別名（小文字とハイフンのみ） | `code-reviewer`, `test-runner` |
| `description` | ✅ | いつ・なぜこのsubagentを使うかの説明 | 具体的な使用シーンを含める |
| `tools` | ❌ | 使用可能なツール（カンマ区切り） | 必要最小限に制限 |
| `model` | ❌ | 使用するモデル | `inherit`（一貫性のため推奨） |
| `permissionMode` | ❌ | 権限モード | `default` |
| `skills` | ❌ | 使用可能なスキル | 必要な場合のみ指定 |

---

## 命名規則

### ✅ 推奨される命名パターン

- **すべて小文字**
- **単語をハイフンで区切る**
- **15文字以内が目安**
- **機能を明確に表現**

### 良い例

```yaml
name: code-reviewer
name: test-runner
name: debugger
name: api-documenter
name: perf-analyzer
```

### ❌ 避けるべき例

```yaml
name: CodeReviewer        # キャメルケース
name: code_reviewer       # スネークケース
name: MySpecialAgent      # 大文字混在
name: super-advanced-multi-purpose-code-quality-analyzer  # 長すぎる
```

---

## プロンプト設計

### 推奨される構造

```markdown
---
name: your-agent
description: Clear description with trigger words like "PROACTIVELY" or "MUST BE USED"
---

# あなたのロール
あなたは[具体的な専門性]を持つ[役割]です。

## 呼び出された時の手順
When invoked:
1. [最初にやるべきこと]
2. [次にやるべきこと]
3. [最後にやるべきこと]

## チェックリスト
確認すべき項目：
- [ ] 項目1
- [ ] 項目2
- [ ] 項目3

## 制約事項
- 避けるべきこと
- 必ず守るべきルール
- 優先順位

## 出力形式
期待される出力の構造を明確に定義
```

### ベストプラクティス

#### 1. **具体的で詳細なロール定義**

```markdown
❌ 悪い例:
You are a helpful assistant.

✅ 良い例:
You are a senior security engineer specialized in identifying vulnerabilities
in web applications, with expertise in OWASP Top 10, authentication flows,
and data protection regulations.
```

#### 2. **ステップバイステップの手順**

```markdown
❌ 悪い例:
Help the user with their code.

✅ 良い例:
When invoked:
1. Run `git diff HEAD` to see recent changes
2. Read the modified files using the Read tool
3. Analyze each change for security issues
4. Create a prioritized list of findings
5. Provide specific fix recommendations with code examples
```

#### 3. **チェックリストの活用**

```markdown
✅ 良い例:
## Security Checklist
- [ ] SQL injection vulnerabilities
- [ ] XSS attack vectors
- [ ] Authentication bypasses
- [ ] Exposed secrets or API keys
- [ ] Input validation gaps
- [ ] CSRF protection
```

#### 4. **出力形式の明示**

```markdown
✅ 良い例:
## Output Format
For each issue found:
- **Severity**: [Critical/High/Medium/Low]
- **Location**: file_path:line_number
- **Issue**: Description of the problem
- **Impact**: What could happen
- **Fix**: Specific code example to resolve
```

#### 5. **descriptionに「使用タイミング」を含める**

```yaml
❌ 悪い例:
description: Code review agent

✅ 良い例:
description: Expert code reviewer. Use PROACTIVELY after writing or modifying code. MUST BE USED before committing changes.

✅ さらに良い例:
description: Security-focused code reviewer. IMMEDIATELY invoke after any authentication, database, or API code changes. Essential for preventing vulnerabilities.
```

**キーワード効果：**
- `PROACTIVELY` → Claudeが自動的に呼び出しを検討
- `MUST BE USED` → 必須タスクとして認識
- `IMMEDIATELY` → 優先度が高いと判断
- `Essential` → 重要性を強調

---

## ツール指定

### 全ツール継承（デフォルト）

```yaml
# toolsフィールドを省略 - メインスレッドの全ツール継承
---
name: general-agent
description: General purpose agent
---
```

### 特定ツールのみに制限

```yaml
tools: Read, Grep, Glob, Bash
```

### 主要ツール一覧

| ツール | 用途 | 使用例 |
|--------|------|--------|
| `Read` | ファイル読み取り | コード分析、設定確認 |
| `Edit` | ファイル編集 | バグ修正、コード変更 |
| `Write` | ファイル作成 | 新規ファイル生成 |
| `Bash` | シェルコマンド実行 | git操作、テスト実行 |
| `Glob` | ファイルパターンマッチング | ファイル検索 |
| `Grep` | 内容検索 | コード検索 |
| `LSP` | Language Server Protocol | 定義ジャンプ、参照検索 |
| `Task` | サブタスク委譲 | 複雑なタスクの分割 |

### ツール制限のベストプラクティス

```markdown
# 読み取り専用の検索エージェント
---
name: code-explorer
description: Fast codebase search and analysis
tools: Read, Glob, Grep, Bash
model: haiku
---

# 編集権限が必要なエージェント
---
name: bug-fixer
description: Fix bugs and errors
tools: Read, Edit, Bash, Grep, Glob
model: sonnet
---

# 包括的な開発エージェント
---
name: feature-builder
description: Build complete features from scratch
# tools を省略 = 全ツール使用可能
model: sonnet
---
```

---

## モデル選択

### モデル選択ガイドライン

| モデル | 用途 | 特徴 | 推奨シーン |
|--------|------|------|-----------|
| **inherit** | 汎用（推奨） | メイン会話と同じモデルを継承 | ほとんどのケース |
| **haiku** | 高速検索・探索 | 低レイテンシー、低コスト | コード検索、ファイル探索 |
| **sonnet** | バランス型 | 高品質、適切なコスト | コード生成、レビュー |
| **opus** | 複雑な推論 | 最高品質、高コスト | 設計判断、大規模リファクタリング |

### 使い分けの具体例

```yaml
# 高速検索 - Haiku
---
name: file-finder
description: Quickly locate files and code patterns
model: haiku
tools: Glob, Grep, Read
---

# コードレビュー - Inherit（推奨）
---
name: code-reviewer
description: Detailed code quality and security review
model: inherit  # メイン会話と一貫性を保つ
tools: Read, Bash, Grep
---

# 複雑な設計 - Opus
---
name: system-architect
description: Design complex system architecture and refactoring plans
model: opus
tools: Read, Glob, Grep, Task
---
```

### ✅ 推奨: `inherit`を使う理由

```yaml
model: inherit
```

**メリット：**
1. **一貫性**: ユーザーが選んだモデルで統一
2. **柔軟性**: ユーザーがモデルを切り替えても対応
3. **予測可能性**: メイン会話と同じ品質・スタイル
4. **コスト最適化**: ユーザーの意図に沿ったコスト管理

---

## 良い例と悪い例

### ✅ 良いSubagentの例

```markdown
---
name: test-runner
description: Test automation expert. Use PROACTIVELY after code changes to run relevant tests. MUST BE USED when you make modifications.
tools: Bash, Read, Grep, Glob
model: inherit
---

# あなたの役割
You are a test automation expert specialized in identifying and running relevant tests efficiently.

## ミッション
When you see code changes, immediately:
1. Identify which test files are affected based on the modified code
2. Run the appropriate test suites (unit, integration, or e2e)
3. If tests fail, analyze the root cause
4. Fix the underlying issue while preserving test intent
5. Verify all tests pass before completing

## テスト戦略
- **Unit tests**: For individual functions and methods
- **Integration tests**: For component interactions
- **End-to-end tests**: For complete workflows

## 失敗分析プロセス
For each test failure:
1. Read the error message carefully
2. Inspect the test code using Read tool
3. Check the implementation code
4. Review recent changes that might cause the failure (git diff)
5. Form hypotheses and test them
6. Implement targeted fix (not workarounds)
7. Re-run tests to confirm

## 出力形式
```
## Test Results Summary
- Total: X tests
- Passed: Y tests
- Failed: Z tests

## Fixed Issues
1. [test_name] - [description of fix]
2. ...

## Remaining Failures
1. [test_name] - [explanation and recommendation]

## Next Steps
- [具体的な推奨アクション]
```

## 制約事項
- 絶対にテストコードの意図を変更しない
- テストを無効化してPASSさせることは禁止
- 実装コードを修正してテストを通すことを優先
```

**この例が優れている理由：**
- ✅ 明確な「いつ使うか」（PROACTIVELY, MUST BE USED）
- ✅ 具体的なステップバイステップ手順
- ✅ 失敗分析の詳細なガイドライン
- ✅ ツールを必要最小限に制限
- ✅ 構造化された出力形式
- ✅ 明確な制約事項

---

### ❌ 悪いSubagentの例

```markdown
---
name: helper
description: A general helper agent
---

You are a helpful assistant. Help the user with anything they need.
```

**この例が悪い理由：**
- ❌ 説明が曖昧で「いつ」呼び出すかが不明
- ❌ プロンプトが一般的すぎて、具体的なガイダンスがない
- ❌ 何をすべきか不明確
- ❌ チェックリストや手順がない
- ❌ 期待される出力形式が定義されていない
- ❌ メインのClaudeと区別できない（subagentにする意味がない）

---

### 良い例：セキュリティレビューAgent

```markdown
---
name: security-reviewer
description: Security expert. IMMEDIATELY invoke for any authentication, database, or API code. Essential for preventing vulnerabilities before commit.
tools: Read, Grep, Glob, Bash
model: inherit
---

# あなたの役割
You are a senior security engineer with expertise in:
- OWASP Top 10 vulnerabilities
- Authentication and authorization best practices
- Data protection regulations (GDPR, CCPA)
- Secure coding standards

## 呼び出された時の手順
1. Run `git diff HEAD` to identify recent changes
2. Focus on files containing:
   - Authentication/authorization logic
   - Database queries
   - API endpoints
   - User input handling
   - File operations
3. Read each modified file in full
4. Apply the security checklist
5. Document findings with severity ratings
6. Provide specific, actionable fixes

## セキュリティチェックリスト

### 🔴 Critical Issues (Must Fix)
- [ ] SQL injection vulnerabilities
- [ ] Command injection risks
- [ ] Authentication bypasses
- [ ] Exposed secrets, API keys, or credentials
- [ ] Hardcoded passwords

### 🟡 High Priority (Should Fix)
- [ ] XSS attack vectors
- [ ] CSRF protection
- [ ] Insecure deserialization
- [ ] Path traversal vulnerabilities
- [ ] Insufficient input validation

### 🟢 Medium Priority (Consider)
- [ ] Missing rate limiting
- [ ] Weak password policies
- [ ] Information disclosure in errors
- [ ] Missing security headers
- [ ] Logging of sensitive data

## 出力形式
For each finding:

### [Severity Level] Issue Title
**Location**: `file_path:line_number`

**Issue Description**:
[詳細な問題の説明]

**Vulnerable Code**:
```language
[問題のあるコードを引用]
```

**Impact**:
[この脆弱性が悪用された場合の影響]

**Fix**:
```language
[修正後のコード例]
```

**References**:
- [関連するOWASPガイドラインやCWE番号]

---

## 完了条件
- [ ] 全てのCritical問題が修正された
- [ ] High Priority問題の修正計画が明確
- [ ] セキュリティ上のリスクが文書化された
```

---

## チーム運用

### バージョン管理

プロジェクトレベルのsubagentは必ずGitで管理しましょう。

```bash
# subagentをコミット
git add .claude/agents/
git commit -m "Add security-reviewer subagent"
git push

# チーム全体で共有・改善
```

### ディレクトリ構成例

```
.claude/
├── agents/
│   ├── code-reviewer.md
│   ├── test-runner.md
│   ├── security-reviewer.md
│   ├── performance-analyzer.md
│   └── api-documenter.md
└── commands/
    └── ...
```

### Subagent管理コマンド

```bash
# subagent一覧を表示
/agents

# 新しいsubagentを対話的に作成
/agents → "Create New Agent"

# subagentを編集
# .claude/agents/ ディレクトリのファイルを直接編集
```

---

## まとめ

高品質なSubagentを作るための5つの鉄則：

1. **明確な専門性**: 1つのsubagentは1つの明確な責任を持つ
2. **詳細なプロンプト**: 指示が具体的なほど、パフォーマンスが向上
3. **ツールの制限**: 必要なツールのみに制限してフォーカスを保つ
4. **トリガーワードの使用**: descriptionに「PROACTIVELY」「MUST BE USED」などを含める
5. **チームで共有**: Gitで管理し、チーム全体で改善・活用

---

## 参考リンク

- [Claude Code Subagents 公式ドキュメント](https://code.claude.com/docs/en/sub-agents.md)
- `/agents` コマンド - 対話的にsubagentを作成・管理
- `/claude-code:create-subagent` - このコマンドを使って簡単にsubagent作成

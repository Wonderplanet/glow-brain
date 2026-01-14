# Claude Code ガイド相談レポート

**日付**: 2026年01月14日
**トピック**: HeadlessモードとAgent内でのSkills利用

## 相談内容

- Headlessモードで回答テキストを保存する方法
- 「Headlessモードではskillsが使えない」という制限の真偽
- `--agents`オプション使用時に、Agent内でSkillsを使うことができるか
- 実現可能な回避策

## 重要な発見

### **「Headlessモードではskillsが使えない」は誤情報**

公式ドキュメントを調査した結果、**この制限は公式ドキュメントに記載されていません**。

> 出典: [Headlessモードドキュメント](https://code.claude.com/docs/en/headless.md)

Headlessモード（`-p`フラグ）は単に「非対話的な実行モード」を指しており、Skillsの利用を禁止するものではありません。

> The CLI was previously called "headless mode." The `-p` flag and all CLI options work the same way.

## Agent内でのSkills利用

### ✅ Agent内でSkillsを使うことは可能

公式ドキュメントでは、Agent内でSkillsを明示的に指定することで利用可能になることが明記されています。

> 出典: [Subagentsドキュメント](https://code.claude.com/docs/en/sub-agents.md)

**Agent定義ファイルでのSkills指定**:
```yaml
---
name: code-reviewer
description: Review code for quality and best practices
skills: pr-review, security-check
tools: Read, Grep, Glob, Bash
model: sonnet
---

You are a senior code reviewer. Use the available skills proactively.
```

公式ドキュメントから引用：

> Give a subagent access to Skills
>
> Subagents do not automatically inherit Skills from the main conversation. To give a custom subagent access to specific Skills, list them in the subagent's `skills` field

**重要なポイント**:
- Skillsは自動的に継承されない
- `skills`フィールドで明示的に列挙する必要がある
- 指定されたSkillの完全な内容がAgent起動時にコンテキストに注入される

### CLIの`--agents`フラグの制限

> 出典: [CLIリファレンス](https://code.claude.com/docs/en/cli-reference.md)

CLIの`--agents`フラグでは、以下のフィールドのみがサポートされています：

| フィールド | 説明 |
|-----------|------|
| `description` | 必須：Agentを呼び出すタイミングの説明 |
| `prompt` | 必須：Agentの動作を指定するシステムプロンプト |
| `tools` | オプション：使用可能なツール（省略時はすべて継承） |
| `model` | オプション：使用モデル（sonnet, opus, haiku） |

**`skills`フィールドは`--agents`フラグでは使用できません**。

## Headlessモード + Agent + Skills の実現方法

### 推奨アプローチ：Agent定義ファイルを使用

**ステップ1: Agent定義ファイルを作成**

`.claude/agents/response-saver.md`:
```yaml
---
name: response-saver
description: Save responses with skills support
skills: skill-a, skill-b
tools: Read, Grep, Glob, Bash, Write
model: sonnet
---

You are an agent that can use skills to complete tasks.
Use the available skills proactively when relevant to the task.

When you complete your task, provide a comprehensive response.
```

**ステップ2: Headlessモードで実行**

```bash
# Agentが内部でSkillsを使いながら、回答をファイルに保存
claude -p "質問内容" --agent response-saver > ~/responses/$(date +%Y%m%d%H%M%S).txt

# または、JSON形式で構造化出力
claude -p "質問内容" --agent response-saver --output json > ~/responses/response.json
```

> 出典: [Subagentsドキュメント - Write subagent files](https://code.claude.com/docs/en/sub-agents.md#write-subagent-files)

**ベストプラクティス**:
- Agent定義は`.claude/agents/`に配置（プロジェクト内で共有・バージョン管理可能）
- Skillsは`skills`フィールドで明示的に列挙
- Agentのシステムプロンプト内で、利用可能なSkillsと使用方法を明記

**注意点**:
- Skill内の自動トリガーは機能しない
- Agentプロンプト内で明示的にSkillsを使うよう指示する必要がある

### 代替アプローチ：Skill内のAgent設定

Skill自体が`context: fork`でAgent実行を指定することも可能です。

> 出典: [Skillsドキュメント - Run skills in a forked context](https://code.claude.com/docs/en/skills.md#run-skills-in-a-forked-context)

```yaml
---
name: code-analysis
description: Analyze code quality and generate detailed reports
context: fork
agent: my-analyzer-agent
---
```

### プラグインとしての配布

Skillsと一緒にAgentをプラグインとしてパッケージ化し、複数プロジェクト間で共有することも可能です。

> 出典: [Pluginsドキュメント](https://code.claude.com/docs/en/plugins.md)

## 完全な実装例

### Agent定義ファイル

`.claude/agents/master-data-csv-creator.md`:
```yaml
---
name: master-data-csv-creator
description: Create master data CSV from specifications
skills: masterdata-csv-validator, masterdata-explorer
tools: Read, Grep, Glob, Bash, Write, Edit
model: sonnet
---

You are a master data CSV creation specialist.

Available skills:
- masterdata-csv-validator: Validate CSV files against DB schema
- masterdata-explorer: Explore schema and query CSV data

When creating master data CSV:
1. Use masterdata-explorer to understand the schema
2. Create the CSV file based on specifications
3. Use masterdata-csv-validator to validate the result
4. Provide a comprehensive report of the process

Always use these skills proactively when relevant to the task.
```

### Headlessモードでの実行

```bash
# 回答を完全な形でファイルに保存
claude -p "イベントマスタのCSVを作成してください" \
  --agent master-data-csv-creator \
  > ~/responses/$(date +%Y%m%d%H%M%S)_masterdata_creation.txt

# 並行実行も可能
for spec in spec1 spec2 spec3; do
  claude -p "$specからCSVを作成" \
    --agent master-data-csv-creator \
    > ~/responses/$(date +%Y%m%d%H%M%S)_${spec}.txt &
done
wait
```

## トラブルシューティング

### Skillsが表示されない場合

> 出典: [Skillsドキュメント - Plugin skills not appearing](https://code.claude.com/docs/en/skills.md#plugin-skills-not-appearing)

プラグイン提供のSkillsの場合、キャッシュをクリアして再インストール：

```bash
rm -rf ~/.claude/plugins/cache
/plugin install plugin-name@marketplace-name
```

### Skills一覧の確認

```bash
# 利用可能なSkills一覧を確認
claude /skills

# または対話モードで
/skills
```

## 比較：各アプローチのメリット・デメリット

| アプローチ | Skills利用 | 実装難易度 | 欠損リスク | 推奨度 |
|-----------|----------|----------|----------|--------|
| **Headless + Agent定義ファイル** | ✅ 可能 | 低 | 最小 | ⭐⭐⭐ 推奨 |
| **Headless（Skills不使用）** | ❌ 不可 | 最低 | 最小 | ⭐⭐ 限定的 |
| **Stop hook + トランスクリプト** | ✅ 可能 | 高 | 中 | ⭐ 複雑 |

## 参照ドキュメント

各トピックで参照した公式ドキュメントのURL一覧：

- [Headlessモードドキュメント](https://code.claude.com/docs/en/headless.md)
- [Skillsドキュメント](https://code.claude.com/docs/en/skills.md)
- [Subagentsドキュメント](https://code.claude.com/docs/en/sub-agents.md)
- [CLIリファレンス](https://code.claude.com/docs/en/cli-reference.md)
- [Pluginsドキュメント](https://code.claude.com/docs/en/plugins.md)

## まとめ

### ✅ 実現可能な方法

**Headlessモード + Agent + Skills の組み合わせは完全に可能です**

1. `.claude/agents/`配下にAgent定義ファイルを作成
2. `skills`フィールドでSkillsを明示的に指定
3. HeadlessモードでそのAgentを起動
4. 標準出力をファイルにリダイレクト

### 重要な発見

- **「Headlessモードではskillsが使えない」は誤情報**
- 公式ドキュメントにそのような制限は記載されていない
- 正しい方法で設定すれば、Headlessモード内でSkillsを活用可能

### 推奨実装パターン

```bash
# Agent定義ファイルを作成（一度だけ）
cat > .claude/agents/my-task-agent.md << 'EOF'
---
name: my-task-agent
description: Execute tasks with skills
skills: skill-a, skill-b
tools: Read, Write, Bash
model: sonnet
---
Execute tasks using available skills proactively.
EOF

# Headlessモードで実行（繰り返し可能）
claude -p "タスク内容" --agent my-task-agent > output.txt
```

## 次のアクション

- Agent定義ファイルの作成とテスト
- 実際の業務で使用するSkillsを特定
- Headlessモードでの自動化スクリプトを構築
- 回答保存用のディレクトリ構成を設計

---

*このレポートは `/guide-chat` コマンドにより自動生成されました*

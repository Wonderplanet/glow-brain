---
name: orchestrate-task
description: |
  タスクを細分化単位(1メソッド/1クラスレベル)に分解し、依存関係を分析、効率的な実行のためにスキル/サブエージェントを自動割り当てするタスク分析・分解・並列実行オーケストレーター。メインセッションのコンテキストを節約するため、計画と実行をサブエージェントに委譲。以下の場合に使用: (1) 複雑な複数ステップのタスクのオーケストレーション、(2) 機能実装の細分化されたTODOへの分解、(3) 依存関係が許す範囲でのタスクの並列実行、(4) 利用可能なスキルとサブエージェントをタスク要件に自動マッチング、(5) 依存関係トラッキング付きYAML実行計画の生成、(6) --with-gap-analysisオプションでのスキルギャップ検出。ワークフロー: plannerサブエージェント(Phase 1) → ギャップ分析(Phase 2, オプション) → TODO表示(Phase 3) → executorサブエージェント(Phase 4)。 (project)
---

# タスクオーケストレータースキル

タスクを効率的に進めるために、**計画フェーズと実行フェーズをサブエージェントに委譲**し、メインセッションのコンテキストを節約しながら実行します。

- **Phase 1**: orchestrate-task-planner（計画立案）
- **Phase 4**: orchestrate-task-executor（実行）

メインセッションはPhase 3で計画を受け取り、TODO一覧を表示するのみです。

## Instructions

### オプション解析

引数に `--with-gap-analysis` が含まれているか確認：
- **含まれている** → Phase 2（ギャップ判定）を実行
- **含まれていない** → Phase 2をスキップ

### 実行フロー

```
Phase 1: 計画立案（サブエージェント委譲）
    ↓ orchestrate-task-planner起動
Phase 2: [--with-gap-analysis時のみ] スキルギャップ判定
    ↓
Phase 3: 計画受け取り・TODO登録（メインセッション）
    ↓ plan.yaml読み込み、TODO表示
Phase 4: フロー実行（サブエージェント委譲）
    ↓ orchestrate-task-executor起動
    完了サマリー受け取り
```

---

## Phase 1: 計画立案（サブエージェント委譲）

**orchestrate-task-planner サブエージェントを起動**

```
Task(
  subagent_type="orchestrate-task-planner",
  prompt="以下のタスクを分析し、実行計画を作成してください：\n\n{タスク内容}"
)
```

サブエージェントが以下を実行：
1. タスク分解・TODO細分化（1メソッド/1クラス単位）
2. 依存関係の整理
3. 利用可能ツールの検索・マッチング
4. 並列実行グループの最適化

**返却されるYAML形式の計画:**
```yaml
task_plan:
  task: "タスクの説明"
  execution_groups:
    - group: 1
      name: "グループ名"
      parallel: true/false
      todos:
        - id: 1
          content: "TODO内容"
          activeForm: "進行形の表現"
          skill: "使用スキル名"
          subagent: "使用サブエージェント名"
```

---

## Phase 2: スキルギャップ判定（オプション）

**`--with-gap-analysis` 指定時のみ実行**

### 2.1 skill-gap-analyzer サブエージェントを起動

```
Task(subagent_type="skill-gap-analyzer", prompt="タスク: {タスク内容}")
```

### 2.2 ギャップが検出された場合

AskUserQuestionで確認：

```
スキルギャップが検出されました:
- [ギャップ1]: 〇〇機能

どのように進めますか？
1. スキル/サブエージェントを作成してから進む
2. 既存ツールで代替して進む (Recommended)
```

### 2.3 生成を選択した場合

- **再利用可能なパターン** → `Skill("create-skill")`
- **自律的な判断が必要** → `Skill("create-subagent")`

生成後、Phase 3へ進む。

---

## Phase 3: 計画受け取り・TODO登録

### 3.1 計画ディレクトリパスの確認

サブエージェントから返却されたメッセージに計画ディレクトリパスが含まれていることを確認：

```
計画ディレクトリ: .claude/plans/orchestrate-task/{timestamp}-{task_slug}/
```

### 3.2 plan.yamlの読み込みとパース

計画ディレクトリから`plan.yaml`を読み込み、TodoWriteで全TODO登録：

```
Read: .claude/plans/orchestrate-task/{timestamp}-{task_slug}/plan.yaml

TodoWrite([
  {content: "TODO1", status: "pending", activeForm: "TODO1を実行中"},
  {content: "TODO2", status: "pending", activeForm: "TODO2を実行中"},
  ...
])
```

### 3.3 全TODO一覧表示 + ファイルパス明示

**全TODOをテーブル形式で表示**し、詳細ファイルへのリンクを明示：

```markdown
## 📋 実行計画が作成されました

**タスク**: {タスク名}
**総TODO数**: {total_todos}
**総グループ数**: {total_groups}
**並列化効率**: {estimated_parallel_efficiency}

### 全TODO一覧

| # | グループ | TODO内容 | ツール | 依存 | 並列 |
|---|---------|---------|--------|------|------|
| 1 | G1: スキーマ確認 | glow-schema確認 | api-schema-reference | - | - |
| 2 | G2: DB準備 | マイグレーションファイル作成 | migration | #1 | - |
| 3 | G2: DB準備 | マイグレーション実行 | migration | #2 | - |
| 4 | G3: Entity/Model作成 | StaminaRecoveryEntity作成 | domain-layer | #3 | ✅ |
| 5 | G3: Entity/Model作成 | StaminaRecoveryModel作成 | domain-layer | #3 | ✅ |
| ... | ... | ... | ... | ... | ... |

*(全{total_todos}TODO)*

📁 **詳細な計画ファイル**:
`.claude/plans/orchestrate-task/{timestamp}-{task_slug}/`

- `plan.md` ← 全詳細（依存関係図、実行計画、ツール確実性）
- `progress.md` ← 進捗トラッキング

**依存関係の詳細、Mermaid図、実行コマンド例は上記ディレクトリの `plan.md` を参照してください。**
```

---

## Phase 4: フロー実行（サブエージェント委譲）

### 4.1 orchestrate-task-executor サブエージェントを起動

**重要**: Phase 4の実行は全てexecutorサブエージェントに委譲し、メインセッションのコンテキストを節約します。

Phase 1のplannerと同様に、**必ずサブエージェントを起動**してください：

```
Task(
  subagent_type="orchestrate-task-executor",
  description="タスク実行",
  prompt="""
以下のYAML計画ファイルを読み込んで、グループ単位でTODOを実行してください。

計画ファイル: .claude/plans/orchestrate-task/{timestamp}-{task_slug}/plan.yaml

全TODO実行後、完了サマリーを返却してください。
"""
)
```

**計画ディレクトリパスの取得方法**:
- Phase 1のplannerサブエージェントから返却されたメッセージに含まれる
- または、Phase 3で読み込んだplan.yamlのファイルパスから抽出

### 4.2 executorサブエージェントの動作

サブエージェント側で以下を自動実行：

#### 4.2.1 グループ単位での実行

1. plan.yamlの読み込み
2. グループNのTODOをin_progressに更新
3. 同一グループ内のTaskは単一メッセージで並列呼び出し（parallel: trueの場合）
4. グループ完了後、TODOをcompletedに更新
5. 次グループへ進む

#### 4.2.2 Skillの呼び出し

TODOに`skill`フィールドがある場合：
```
Skill("{skill_name}")
```

#### 4.2.3 Subagentの呼び出し

TODOに`subagent`フィールドがある場合：
```
Task(subagent_type="{subagent_name}", prompt="...")
```

#### 4.2.4 エラーハンドリング

| エラー種別 | 対応fixer |
|-----------|----------|
| テストエラー | api-test-fixer |
| PHPStanエラー | api-phpstan-fixer |
| phpcs/phpcbfエラー | api-phpcs-phpcbf-fixer |
| deptracエラー | api-deptrac-fixer |
| 複合エラー | api-sail-check-fixer |

### 4.3 完了サマリー（executorから返却）

executorサブエージェントから以下の形式でサマリーが返却されます：

```markdown
## 実行結果

### 生成したスキル/サブエージェント（ある場合）
- [新規] xxx-skill: 〇〇用途

### 実行したTODO
| グループ | TODO | 使用ツール | 結果 |
|---------|------|-----------|------|
| 1 | glow-schema確認 | api-schema-reference | ✅ |
| 2 | マイグレーション作成 | migration | ✅ |
| ... | ... | ... | ... |

### 最終成果物
- 作成したクラス/メソッド一覧
```

---

## 参照ドキュメント

- [granularity-rules.md](./granularity-rules.md) - TODO細分化ルール詳細
- [parallel-patterns.md](./parallel-patterns.md) - 並列化パターン
- [tool-matching.md](./tool-matching.md) - ツールマッチングルール

### サブエージェント
- [orchestrate-task-planner](../../agents/general/orchestrate-task-planner.md) - Phase 1: 計画立案エージェント
- [orchestrate-task-executor](../../agents/general/orchestrate-task-executor.md) - Phase 4: 実行エージェント
- [skill-gap-analyzer](../../agents/general/skill-gap-analyzer.md) - Phase 2: ギャップ分析エージェント

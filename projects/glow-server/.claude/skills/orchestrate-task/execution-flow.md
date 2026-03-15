# 実行フロー詳細

## フロー概要

```
Phase 1: 計画立案（サブエージェント委譲）
    ↓
Phase 2: [--with-gap-analysis時のみ] スキルギャップ判定
    ↓
Phase 3: 計画受け取り・TODO登録
    ↓
Phase 4: フロー実行・完了サマリー
```

---

## Phase 1: 計画立案（サブエージェント委譲）

**目的**: タスク分析・TODO細分化・ツールマッチングを別コンテキストで実行し、メインセッションのコンテキストを節約する。

### 1.1 orchestrate-task-planner サブエージェントを起動

```
Task(
  subagent_type="orchestrate-task-planner",
  prompt="以下のタスクを分析し、実行計画を作成してください：\n\n{タスク内容}"
)
```

### 1.2 サブエージェントが実行する内容

1. **タスク分解・TODO細分化**
   - 1メソッド実装/1クラスファイル追加レベルまで分解
   - 詳細は [granularity-rules.md](./granularity-rules.md) を参照

2. **依存関係の整理**
   - 各TODOの前提条件を特定
   - トポロジカルソートで実行順序を決定

3. **利用可能ツールの検索とカタログ化**

以下の手順で全ツールを収集し、構造化されたカタログを作成する：

#### 3.1 スキルのスキャン

```bash
Glob: .claude/skills/**/SKILL.md
```

各SKILL.mdファイルから以下を抽出：
- `name`: スキル名（例: `api-endpoint-implementation`）
- `description`: スキルの用途・対象・使用時機

**descriptionの構造化解析：**

description文を以下の要素に分解：

1. **【用途】**: "〇〇が必要な時に使用"、"〇〇する際に使用"
2. **【対象】**: "glow-schema"、"複数DB"、"PHPUnit"等の対象技術
3. **【提供機能】**: "全体フロー"、"既存パターンの踏襲"等の提供内容

**例：**
```yaml
skill:
  name: "api-endpoint-implementation"
  description: "新規APIエンドポイント追加が必要な時に使用。glow-schema確認からルーティング定義、Controller・ResultData・ResponseFactory実装、テストまでの全体フローを提供し、既存スキル（migration、domain-layer、api-request-validation、api-response、api-test-implementation）を適切な順序で統合する。"
  parsed:
    用途: "新規APIエンドポイント追加"
    対象: ["glow-schema", "Controller", "ResultData", "ResponseFactory"]
    提供機能: "全体フロー、既存スキル統合"
    使用時機: "必要な時"
```

#### 3.2 サブエージェントのスキャン

```bash
Glob: .claude/agents/**/*.md
```

各エージェントファイルから以下を抽出：
- `name`: サブエージェント名（例: `api-test-fixer`）
- `description`: サブエージェントの役割・使用時機
- `model`: 使用モデル（sonnet/haiku）

**descriptionの構造化解析：**

1. **【役割】**: "〇〇を検出・修正する"、"〇〇を分析する"
2. **【対象】**: "PHPUnit"、"PHPStan"等の対象ツール
3. **【使用時機】**: "〇〇が出た時"、"〇〇が必要な時"

#### 3.3 カタログの構造化

抽出した情報を以下の形式でカタログ化：

```yaml
tool_catalog:
  skills:
    - name: "api-schema-reference"
      用途: "YAML定義確認"
      対象: ["glow-schema", "YAML"]
      提供機能: "構造の読み方、データ型理解"
      キーワード: ["schema", "YAML", "リクエスト", "レスポンス"]

    - name: "migration"
      用途: "マイグレーション実装"
      対象: ["mst", "mng", "usr", "log", "sys", "admin"]
      提供機能: "テーブル作成・変更、実行、ロールバック"
      キーワード: ["migration", "DB", "テーブル", "カラム"]

  subagents:
    - name: "api-test-fixer"
      役割: "テストエラー修正"
      対象: ["PHPUnit"]
      使用時機: "Error/Failure/Warning検出時"
      キーワード: ["test", "エラー", "修正"]
```

4. **ツールマッチングの実行**

各TODOに対して最適なスキル/サブエージェントを割り当てる。

**詳細は [tool-matching.md](./tool-matching.md) を参照**

概要：
1. TODO内容から主要キーワードを抽出
2. 開発工程に分類（スキーマ確認/マイグレーション/ドメイン層/API層/テスト/品質チェック）
3. 優先順位に従ってマッチング実行：
   - 優先度1: 直接マッチング（完全一致）
   - 優先度2: 工程ベースマッチング
   - 優先度3: キーワードベースマッチング
   - 優先度4: 複合判定（複数マッチ時）
   - 優先度5: general-purpose（最終手段）

#### 4.1 マッチング結果の記録

各TODOに以下を付与：

```yaml
- id: 1
  content: "glow-schema確認"
  activeForm: "glow-schemaを確認中"
  skill: "api-schema-reference"
  subagent: null
  matching_reason: "直接マッチング（用途完全一致）"
  confidence: "high"
```

**confidence（確実性）の判定基準：**
- `high`: 直接マッチング、または工程ベースマッチングで唯一のツールが選択された
- `medium`: キーワードベースマッチングで複数候補から絞り込んだ
- `low`: 複合判定でも明確な差がなく、最新のスキルを選択した
- `fallback`: general-purposeを使用

#### 4.2 併用パターンの検出

特定のTODOで複数のスキルが必要な場合を検出：

**例：**
```yaml
- id: 10
  content: "新規APIエンドポイント実装"
  activeForm: "新規APIエンドポイントを実装中"
  primary_skill: "api-endpoint-implementation"
  secondary_skills: ["api-test-implementation"]
  matching_reason: "API実装は全体フロー+テストが標準パターン"
  confidence: "high"
```

5. **並列最適化**
   - 依存関係に基づいてグループ化
   - 詳細は [parallel-patterns.md](./parallel-patterns.md) を参照

### 1.3 返却される計画フォーマット

```yaml
task_plan:
  task: "タスクの説明"
  planned_at: "2025-01-01T10:00:00Z"

  summary:
    total_todos: 15
    total_groups: 6

  execution_groups:
    - group: 1
      name: "スキーマ確認"
      parallel: false
      todos:
        - id: 1
          content: "glow-schema確認"
          activeForm: "glow-schemaを確認中"
          skill: "api-schema-reference"
          subagent: null

    - group: 2
      name: "DB準備"
      parallel: false
      todos:
        - id: 2
          content: "マイグレーションファイル作成"
          activeForm: "マイグレーションファイルを作成中"
          skill: "migration"
          subagent: null

  tool_catalog:
    skills_used:
      - name: "api-schema-reference"
        description: "glow-schemaのYAML定義を参照"
    subagents_used:
      - name: "api-test-fixer"
        description: "テストエラーの自動修正"
```

---

## Phase 2: スキルギャップ判定（オプション）

**`--with-gap-analysis` 指定時のみ実行**

### 2.1 skill-gap-analyzer サブエージェントを起動

```
Task(subagent_type="skill-gap-analyzer", prompt="タスク: {タスク内容}")
```

サブエージェントが以下を実行：
1. 既存ツールで対応可能かを判定
2. ギャップを特定

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

### 3.1 計画のパース

サブエージェントから返却されたYAML計画をパース。

### 3.2 TodoWriteで進捗管理

全TODOをTodoWriteに登録：

```
TodoWrite([
  {content: "glow-schema確認", status: "pending", activeForm: "glow-schemaを確認中"},
  {content: "マイグレーションファイル作成", status: "pending", activeForm: "マイグレーションファイルを作成中"},
  ...
])
```

### 3.3 実行計画サマリーの表示

```markdown
## 実行計画

| グループ | 並列 | TODO数 | 使用ツール |
|---------|-----|-------|-----------|
| 1. スキーマ確認 | - | 1 | api-schema-reference |
| 2. DB準備 | - | 2 | migration |
| 3. Entity/Model | ✅ | 2 | domain-layer |

**合計: 15 TODO, 6 グループ**
```

---

## Phase 4: フロー実行

### 4.1 グループ単位で実行

```
1. グループNのTODOをin_progressに更新
2. 同一グループ内のTaskは単一メッセージで並列呼び出し（parallel: trueの場合）
3. グループ完了後、TODOをcompletedに更新
4. 次グループへ進む
```

### 4.2 並列実行の呼び出し方

**parallel: true のグループ**

同一グループ内のTaskは**単一メッセージで複数呼び出し**：

```
// グループ4を並列実行（単一メッセージで）
Task(subagent_type="general-purpose", prompt="XxxRepository インターフェース作成...")
Task(subagent_type="general-purpose", prompt="XxxRepositoryImpl クラス作成...")
```

**parallel: false のグループ**

順次実行：

```
// 1つずつ実行
Task(subagent_type="general-purpose", prompt="マイグレーション作成...")
// 完了を待ってから
Task(subagent_type="general-purpose", prompt="マイグレーション実行...")
```

### 4.3 Skillの使用

```
Skill("{skill_name}")
```

Skillは内部で並列化を管理するため、基本的に順次実行。

### 4.4 エラーハンドリング

| エラー種別 | 対応fixer |
|-----------|----------|
| テストエラー | api-test-fixer |
| PHPStanエラー | api-phpstan-fixer |
| phpcs/phpcbfエラー | api-phpcs-phpcbf-fixer |
| deptracエラー | api-deptrac-fixer |
| 複合エラー | api-sail-check-fixer |

### 4.5 完了サマリー

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

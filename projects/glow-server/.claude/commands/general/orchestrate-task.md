# タスク進行調整コマンド

受け取ったプロンプトのタスクを効率的に進めるために、利用可能なClaude Codeの機能（skills/subagents）を**動的に検索**し、TODOリストの各工程に最適なツールを紐付けて進行を調整します。

## 使用方法

```
/general:orchestrate-task [タスクの説明]
```

例: `/general:orchestrate-task 新規APIエンドポイントの実装とテスト`

## 実行内容

引数: $ARGUMENTS

---

## 自動実行手順

### ステップ1: 利用可能なツールの動的検索

**重要**: 以下のファイルを実際に検索して、最新のskills/subagents一覧を取得してください。

#### 1-1. Skills一覧の取得

```
Glob: .claude/skills/*.md
```

各スキルファイルを読み込み、以下の情報を抽出：
- ファイル名（= スキル名）
- description（用途の説明）
- 使用トリガー条件

#### 1-2. Subagents一覧の取得

```
Glob: .claude/agents/*.md
```

各エージェントファイルを読み込み、以下の情報を抽出：
- ファイル名（= subagent_type）
- description（用途の説明）
- 使用トリガー条件

#### 1-3. システム組み込みのSubagents

Taskツールのシステム説明から以下の組み込みsubagentsも利用可能：
- `general-purpose`: 複雑なマルチステップタスクの自律的処理
- `Explore`: コードベース探索（quick/medium/very thorough）
- `Plan`: 実装計画の設計
- `claude-code-guide`: Claude Code/Agent SDKの使い方

### ステップ2: タスク分析とツールマッチング

与えられたタスク「$ARGUMENTS」を分析し：

1. **タスクの分解**: 実行可能な単位に分解
2. **キーワード抽出**: タスクから技術キーワードを抽出
3. **ツールマッチング**:
   - 各skill/subagentのdescriptionとキーワードを照合
   - 最も適切なツールを各TODOに紐付け
4. **依存関係の整理**: 実行順序を決定
5. **並列実行の検討**: 独立したタスクは並列実行を提案

### ステップ3: TODOリストのフォーマット

以下の形式でTODOリストを作成：

```markdown
## TODOリスト（ツール情報付き）

### 1. [TODO内容]
- **推奨ツール**: [skill名 または subagent_type]
- **呼び出し方**:
  - Skill: `Skill("skill名")`
  - Subagent: `Task(subagent_type="...", prompt="...")`
- **選定理由**: [なぜこのツールが適切か]

### 2. [TODO内容]
...
```

### ステップ4: タスク実行

1. **TodoWriteツール**でTODOリストを作成
2. 各TODOを順番に実行（推奨ツールを使用）
3. 完了したらTODOを**completed**に更新
4. エラー発生時は適切なfixerツールを検索して使用

---

## マッチングルール

### キーワードベースのマッチング例

| タスクキーワード | 検索対象 |
|-----------------|---------|
| API, エンドポイント, Controller | `api-*` skills |
| admin, 管理画面, Filament | `admin-*` skills |
| テスト, PHPUnit, test | `*test*` skills/agents |
| マイグレーション, DB, テーブル | `migration`, `database-*` skills |
| エラー, 修正, fix | `*fixer*` agents |
| スキーマ, YAML, glow-schema | `schema-*`, `api-schema-*` skills |
| 報酬, reward | `reward-*` skills |
| PR, レビュー, コメント | `pr-*` skills |
| SDD, 仕様, 設計 | `sdd-*` skills |

### 複合タスクの分解例

「新規API実装」の場合：
1. スキーマ確認 → `api-schema-reference`
2. マイグレーション → `migration`
3. ドメイン実装 → `domain-layer`
4. API実装 → `api-endpoint-implementation`
5. テスト実装 → `api-test-implementation`
6. 品質チェック → `sail-check-fixer`

---

## 実行開始

**以下の手順で実行してください：**

1. **まず** `.claude/skills/*.md` と `.claude/agents/*.md` をGlobで検索
2. **次に** 各ファイルのdescription部分を読み取り
3. **そして** タスク「$ARGUMENTS」を分析してツールをマッチング
4. **最後に** ツール情報付きTODOリストを作成して進行開始

---

**注意**: スキル/エージェントのリストはハードコードせず、必ず毎回ファイルシステムから動的に取得すること。

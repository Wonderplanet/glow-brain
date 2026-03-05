# frontmatterガイド

SKILL.mdのfrontmatter（--- で囲まれた部分）の作成ルールとベストプラクティスを説明します。

## 目次

- [frontmatterとは](#frontmatterとは)
- [全パラメータ一覧](#全パラメータ一覧)
- [基本パラメータ](#基本パラメータ)
  - [name](#name)
  - [description](#description)
- [実行制御パラメータ](#実行制御パラメータ)
  - [disable-model-invocation](#disable-model-invocation)
  - [user-invocable](#user-invocable)
- [ツール・権限パラメータ](#ツール権限パラメータ)
  - [allowed-tools](#allowed-tools)
- [UI・UXパラメータ](#uiuxパラメータ)
  - [argument-hint](#argument-hint)
- [実行環境パラメータ](#実行環境パラメータ)
  - [model](#model)
  - [context](#context)
  - [agent](#agent)
- [ライフサイクルパラメータ](#ライフサイクルパラメータ)
  - [hooks](#hooks)
- [動的コンテキスト注入](#動的コンテキスト注入)
- [パラメータ組み合わせパターン](#パラメータ組み合わせパターン)
- [スキル作成時の要件収集](#スキル作成時の要件収集)
- [検証方法](#検証方法)
- [よくある質問](#よくある質問)

## frontmatterとは

frontmatterはSKILL.mdの先頭にあるYAML形式のメタデータ領域です。`---`で囲まれた部分で、スキルの動作、発見性、実行制御を定義します。

**基本構造:**

```yaml
---
name: "skill-name"
description: |
  スキルの説明
---

# スキル本文
```

**役割:**

- **発見性**: Claudeがいつスキルを使うべきかを理解する（descriptionフィールド）
- **実行制御**: ユーザーのみが起動できるか、Claudeも自動起動できるか
- **権限管理**: スキルが使用できるツールの制限
- **実行環境**: スキルを実行するモデルやsubagent設定

frontmatterはシステムプロンプトに注入されるため、記述方法がスキルの発見性に大きく影響します。

## 全パラメータ一覧

Claude Code SKILL.mdのfrontmatterで使用可能な全10個のパラメータ:

| パラメータ | カテゴリ | 必須/推奨/オプショナル | 概要 |
|:---|:---|:---|:---|
| **name** | 基本 | オプショナル | スキル識別名（デフォルト: ディレクトリ名） |
| **description** | 基本 | 推奨 | スキル説明とトリガーキーワード |
| **argument-hint** | UI・UX | オプショナル | オートコンプリート時の引数ヒント |
| **disable-model-invocation** | 実行制御 | オプショナル | Claude自動実行の禁止 |
| **user-invocable** | 実行制御 | オプショナル | ユーザーメニュー表示制御 |
| **allowed-tools** | ツール・権限 | オプショナル | 使用可能ツールの制限 |
| **model** | 実行環境 | オプショナル | 実行時のモデル指定 |
| **context** | 実行環境 | オプショナル | subagent実行（値: `fork`） |
| **agent** | 実行環境 | オプショナル | subagentタイプ指定 |
| **hooks** | ライフサイクル | オプショナル | ライフサイクルフック設定 |

## 基本パラメータ

### name

#### 概要

スキルの識別名。`/slash-command` になる名前を定義します。

#### 必須/オプショナル

- **オプショナル**（省略時はディレクトリ名を使用）

#### 形式

ジェランド形（動詞 + -ing）を使用します。

```yaml
---
name: creating-code-skills
---
```

#### 公式要件（制約）

- **最大64文字**
- **小文字、数字、ハイフン（-）のみ使用可能**
- XMLタグ不可
- 予約語不可（"anthropic", "claude"）
- 日本語不可（ASCII文字のみ）

#### 良い例・悪い例

**良い例:**

- ✅ `creating-code-skills`
- ✅ `implementing-api-endpoints`
- ✅ `testing-laravel-applications`
- ✅ `managing-database-migrations`

**悪い例:**

- ❌ `Code Skill Creation` （名詞形）
- ❌ `API Endpoint Implementation` （名詞形）
- ❌ `Create Skills` （動詞の原形）
- ❌ `creating_code_skills` （アンダースコア使用）
- ❌ `Creating-Code-Skills` （大文字使用）

#### よくあるミス

- アンダースコアを使用してしまう
- 大文字を使用してしまう
- 名詞形で書いてしまう
- 64文字を超えてしまう

### description

#### 概要

スキルの発見性と使用トリガーを定義。スキルをいつ使うかをClaudeに伝えます。

#### 必須/推奨/オプショナル

- **推奨**（重要）
- 省略時はSKILL.mdの最初の段落がデフォルトで使用される

#### 形式

複数行YAML形式（`|`）で、自然言語トリガー、機能要約、箇条書き詳細、Examplesを含みます。

```yaml
---
description: |
  【トリガー】キーワード1、キーワード2、キーワード3、キーワード4

  簡潔な技術要約（1-2文）。

  対応範囲/主要機能:
  - 機能1
  - 機能2

  Examples:
  <example>user: '例' → スキル起動</example>
---
```

#### 公式要件

- **最大1024文字**
- **必ず第三人称で記述**（重要）

#### 第三人称の重要性

descriptionはシステムプロンプトに注入されるため、一人称や二人称を使うと発見性の問題を引き起こします。

✅ **良い例（第三人称）:**
- 「新規APIエンドポイント追加の全体フロースキル」
- 「Processes Excel files and generates reports」

❌ **悪い例（一人称・二人称）:**
- 「このスキルはAPIエンドポイントを追加できます」（混在）
- 「I can help you create API endpoints」（一人称）
- 「You can add new API endpoints」（二人称）

#### 必須構成要素

1. **【トリガー】セクション**: 自然言語でのトリガーキーワード（カンマ区切り、4個以上推奨）
2. **簡潔な技術要約**: 1-2文でスキルの目的を明確化（第三人称）
3. **箇条書き詳細**: 主要機能や対応範囲を箇条書きで整理
4. **Examples**: Context/User/Assistant/Commentaryパターンで具体例を提示

#### テンプレート

```yaml
description: |
  【トリガー】キーワード1、キーワード2、キーワード3、キーワード4

  簡潔な技術要約（1-2文）。スキルの主目的を明確に記述。

  対応範囲/主要機能:
  - 機能1の説明
  - 機能2の説明
  - 機能3の説明

  Examples:
  <example>
  Context: この状況の説明
  user: 'ユーザーの自然な発話例'
  assistant: 'スキル名スキルで〜を実行します'
  <commentary>なぜこのスキルが必要か</commentary>
  </example>

  <example>user: '別の発話例' → スキル名起動</example>
```

#### 良い例

```yaml
description: |
  【トリガー】新しいスキルを作る、スキル作成、カスタムスキル追加、Progressive Disclosure実装

  Claude Codeスキル作成スキル。要件収集、コード調査、スコープ評価、ファイル構成設計を包括的に実施。

  作成フロー:
  - 基本情報の収集（目的、トリガー条件）
  - 既存コード調査（パターン理解）
  - スコープ評価（複雑度判定）
  - 複数スキルへの分割提案
  - ファイル構成設計（Progressive Disclosure）
  - 実装と検証

  Examples:
  <example>
  Context: 新しい実装パターンのドキュメント化
  user: '新しいスキルを作成したい'
  assistant: 'create-skillスキルでClaude Codeスキルを作成します'
  <commentary>新規スキル作成が必要</commentary>
  </example>

  <example>user: 'スキルのドキュメント構造を設計して' → create-skill起動</example>
```

```yaml
description: |
  【トリガー】マイグレーションを作成、テーブルを作成、DBスキーマを変更、新しいカラムを追加

  glow-serverの複数DB（mst/mng/usr/log/sys/admin）に対するマイグレーション実装。api/admin両ディレクトリ対応。

  対応操作:
  - テーブル作成・変更・削除
  - カラム追加・変更・削除
  - インデックス作成
  - マイグレーション実行・ロールバック

  Examples:
  <example>user: 'ユーザーテーブルにカラムを追加したい' → migration起動</example>
  <example>user: '新しいログテーブルが必要' → migration起動</example>
```

#### 悪い例

```yaml
# 【トリガー】セクションがない
description: スキルを作成する際に使用。基本情報の収集、既存コード調査を行う。

# 箇条書きがない（読みづらい）
description: |
  【トリガー】スキル作成

  スキルを作成します。基本情報の収集と既存コード調査とスコープ評価と複数スキルへの分割提案とファイル構成設計と検証を包括的に実施します。

# Examplesがない
description: |
  【トリガー】スキル作成、新しいスキル

  Claude Codeスキル作成スキル。要件収集、コード調査を実施。

  作成フロー:
  - 基本情報の収集
  - 既存コード調査

# トリガーキーワードが少なすぎる（1-2個のみ）
description: |
  【トリガー】スキル作成

  Claude Codeスキル作成スキル...
```

#### トリガーキーワードの選定基準

- 直接的な行動表現: 「スキル作成」
- 類似表現: 「新規スキル」「カスタムスキル追加」
- 特定用途: 「Progressive Disclosure実装」
- **最少4個のトリガーキーワードを含める**

#### Examplesの書き方のコツ

- Context/User/Assistant/Commentaryパターンを使用
- 具体的なユースケースを示す
- 2個以上のExampleを含める

#### よくあるミス

- 【トリガー】セクションがない
- トリガーキーワードが少なすぎる（1-2個のみ）
- 箇条書きがない（読みづらい）
- Examplesがない
- 一人称・二人称を使用している

## 実行制御パラメータ

### disable-model-invocation

#### 概要

スキルの実行方法を制限。`true` に設定すると、ユーザーのみがスキルを明示的に呼び出し可能（`/skill-name` コマンド）。Claudeは自動起動しません。

#### 型・デフォルト値

- **型**: boolean（true/false）
- **デフォルト値**: false（Claudeが自動起動可能）

#### 使用ケース

- 副作用のあるワークフロー（コミット、デプロイ、メッセージ送信など）
- ユーザーが実行タイミングを制御したい場合
- 危険な操作を持つスキル

#### 具体例

```yaml
---
name: deploy-application
description: |
  【トリガー】アプリケーション展開、本番環境デプロイ

  アプリケーション展開を実行...
disable-model-invocation: true
---
```

#### プロジェクト内使用例

- `create-skill` スキルで使用: スキル作成は慎重なため、自動起動を禁止

### user-invocable

#### 概要

スキルの実行者を制限。`false` に設定すると、Claudeのみがスキルを実行可能（ユーザーは呼び出し不可）。

#### 型・デフォルト値

- **型**: boolean（true/false）
- **デフォルト値**: true（ユーザーが呼び出し可能）

#### 使用ケース

- バックグラウンド知識スキル（アクション不可の参考情報）
- 内部ワークフロー用スキル
- ユーザー向けには不要なスキル

#### 具体例

```yaml
---
name: internal-database-reference
description: DBスキーマ参照の内部スキル
user-invocable: false
---
```

## ツール・権限パラメータ

### allowed-tools

#### 概要

スキルが使用可能なツールを限定。指定されたツールのみ実行可能です。

#### 型・デフォルト値

- **型**: array（文字列の配列）または comma-separated string
- **デフォルト値**: 制限なし（すべてのツール使用可能）

#### 使用ケース

- セキュリティ制限（ファイルシステムアクセスを許可しないなど）
- 特定ツール実行のみを許可
- 権限の最小化原則
- 許可されたツールはユーザーの承認なしで使用可能

#### 具体例

```yaml
---
name: data-processor
description: データ処理スキル
allowed-tools: Read, Grep, Glob
---
```

```yaml
---
name: python-runner
description: Python実行スキル
allowed-tools: Bash(python *), Bash(node *)
---
```

## UI・UXパラメータ

### argument-hint

#### 概要

オートコンプリート時に表示される引数ヒント。スキルが引数を受け取る場合、ユーザーに期待される入力を示します。

#### 型・デフォルト値

- **型**: string
- **デフォルト値**: なし

#### 使用ケース

- スキルが引数を受け取る場合のガイド表示
- ユーザーに期待される入力形式を示す

#### 具体例

```yaml
---
name: analyze-issue
description: GitHubイシュー分析スキル
argument-hint: "[issue-number]"
---
```

```yaml
---
name: convert-file
description: ファイル形式変換スキル
argument-hint: "[filename] [format]"
---
```

## 実行環境パラメータ

### model

#### 概要

スキル実行時に使用するモデルを指定。特定のモデルを強制したい場合に使用します。

#### 型・デフォルト値

- **型**: string
- **デフォルト値**: 現在のセッションのモデル

#### 使用ケース

- 特定のモデルを強制したい場合
- モデル固有の能力が必要な場合

#### 具体例

```yaml
---
name: complex-analysis
description: 複雑な分析スキル（Opus必須）
model: claude-opus-4-5
---
```

### context

#### 概要

`fork`に設定するとスキルをフォークされたsubagent contextで実行。スキルを隔離された環境で実行します（会話履歴にアクセスさせない）。

#### 型・デフォルト値

- **型**: string（値: `"fork"`）
- **デフォルト値**: なし（通常のcontext）

#### 使用ケース

- スキルを隔離された環境で実行する必要がある場合
- 会話履歴にアクセスさせたくない場合

#### 制約

- 明示的な指示が必要（タスクがない場合、subagentは有意義な出力を返さない）

#### 具体例

```yaml
---
name: pr-summary
description: PR要約スキル
context: fork
agent: Explore
allowed-tools: Bash(gh *)
---
```

### agent

#### 概要

`context: fork`設定時に使用するsubagentタイプを指定。

#### 型・デフォルト値

- **型**: string
- **デフォルト値**: `general-purpose`

#### オプション値

- `Explore` - 読み取り専用ツール最適化（コードベース探索向け）
- `Plan` - 計画・分析向け
- `general-purpose` - 汎用（デフォルト）
- カスタムsubagent（`.claude/agents/`内）

#### 使用ケース

- `context: fork`と共に使用
- 特定のsubagentタイプを指定したい場合

#### 具体例

```yaml
---
name: explore-codebase
description: コードベース探索スキル
context: fork
agent: Explore
---
```

## ライフサイクルパラメータ

### hooks

#### 概要

スキルのライフサイクル内でフック実行を設定。スキル内で自動化タスク（コマンド実行、通知等）を関連付けます。

#### 型・デフォルト値

- **型**: object（YAML形式）
- **デフォルト値**: なし

#### 使用ケース

- スキル内で自動化タスク（コマンド実行、通知等）を関連付ける
- スキルのライフサイクルイベントに応じた処理

#### 参照

- [Hooks in skills and agents](https://code.claude.com/docs/en/hooks#hooks-in-skills-and-agents)のドキュメント参照

#### 具体例

```yaml
---
name: deployment-workflow
description: デプロイワークフロースキル
hooks:
  SessionStart:
    - type: command
      command: "echo 'Deployment started'"
---
```

## 動的コンテキスト注入

### 文字列置換変数

スキルコンテンツ内で動的な値を使用可能:

| 変数 | 説明 |
|:---|:---|
| `$ARGUMENTS` | スキル呼び出し時の全引数 |
| `$ARGUMENTS[N]` | N番目の引数（0ベース） |
| `$N` | `$ARGUMENTS[N]`の短縮形 |
| `${CLAUDE_SESSION_ID}` | 現在のセッションID |

### シェルコマンド実行

`` !`command` `` 構文でシェルコマンドを実行:

```yaml
---
name: pr-summary
context: fork
agent: Explore
allowed-tools: Bash(gh *)
---

PR diff: !`gh pr diff`
```

コマンドはSkillコンテンツ送信前に実行され、出力が置換される（前処理）。

## パラメータ組み合わせパターン

### パターン1: 自動実行スキル（デフォルト）

```yaml
---
name: analyzing-spreadsheets
description: |
  Explains code with visual diagrams. Use when explaining how code works.
---
```

**特徴**: Claudeが自動で起動。ユーザーも `/analyzing-spreadsheets` で明示呼び出し可能

### パターン2: ユーザー制御スキル（副作用あり）

```yaml
---
name: deploy-application
description: |
  Deploys application to production. Use when deploying.
disable-model-invocation: true
---
```

**特徴**: 重要な操作を保護。ユーザーが `/deploy-application` で明示的に実行

### パターン3: Claudeのみが使用するバックグラウンドスキル

```yaml
---
name: internal-database-reference
description: Internal skill for database schema reference
user-invocable: false
---
```

**特徴**: ユーザーは呼び出せない。Claude自身の参考用

### パターン4: 権限限定スキル（読み取り専用）

```yaml
---
name: code-explorer
description: Explores codebase with read-only access
allowed-tools: Read, Grep, Glob
---
```

**特徴**: ファイル書き込みなど危険な操作を禁止

### パターン5: 引数付きスキル

```yaml
---
name: analyze-issue
description: Analyzes GitHub issue by number
argument-hint: "[issue-number]"
---
```

**特徴**: オートコンプリートで引数ヒントを表示

### パターン6: 隔離環境で実行するスキル

```yaml
---
name: pr-summary
description: Summarizes pull request changes
context: fork
agent: Explore
allowed-tools: Bash(gh *)
---

PR diff: !`gh pr diff`
```

**特徴**: フォークされたsubagent contextで実行。会話履歴にアクセスしない

### パターン7: 特定モデルを強制するスキル

```yaml
---
name: complex-analysis
description: Complex code analysis requiring Opus
model: claude-opus-4-5
---
```

**特徴**: 特定のモデルを強制

## スキル作成時の要件収集

スキル作成時にユーザーへ確認すべき質問項目:

### 基本情報

- [ ] スキルの目的は何か？（1-2文で簡潔に）
- [ ] どのような場面で使用するか？（具体例3つ以上）
- [ ] トリガーキーワードは何か？（4個以上リストアップ）

### UI・UX設定

- [ ] スキルは引数を受け取るか？
  - Yes → `argument-hint: "[...]"` を指定してオートコンプリートヒントを表示
  - No → 指定なし

### 実行制御設定

- [ ] Claudeが自動で起動して良いか？
  - Yes → `disable-model-invocation: false`（デフォルト）
  - No（副作用あり） → `disable-model-invocation: true`
- [ ] ユーザーが呼び出す必要があるか？
  - Yes → `user-invocable: true`（デフォルト）
  - No（バックグラウンドスキル） → `user-invocable: false`

### ツール・権限設定

- [ ] ツール使用に制限が必要か？
  - Yes（セキュリティ重視） → `allowed-tools: [...]` を指定
  - No → 指定なし

### 実行環境設定

- [ ] 特定のモデルを強制する必要があるか？
  - Yes → `model: "claude-opus-4-5"` 等を指定
  - No → 指定なし（セッションのモデルを使用）
- [ ] スキルを隔離環境で実行する必要があるか？
  - Yes → `context: fork` および `agent: [Explore/Plan/general-purpose]` を指定
  - No → 指定なし

### ライフサイクル設定

- [ ] スキル実行時に自動化タスク（コマンド実行、通知等）が必要か？
  - Yes → `hooks:` セクションを追加
  - No → 指定なし

## 検証方法

### 作成時のチェックリスト

#### 必須パラメータ

- [ ] nameフィールドが正しく設定されているか
  - [ ] ジェランド形（動詞 + -ing）を使用しているか
  - [ ] 最大64文字以内か
  - [ ] 小文字・数字・ハイフンのみか
  - [ ] XMLタグ、予約語、日本語を使用していないか

- [ ] descriptionフィールドが正しく設定されているか
  - [ ] 複数行YAML形式（`|`）を使用しているか
  - [ ] 最大1024文字以内か
  - [ ] 第三人称で記述しているか
  - [ ] 【トリガー】セクションがあるか
  - [ ] トリガーキーワードは4個以上あるか
  - [ ] 簡潔な技術要約（1-2文）があるか
  - [ ] 箇条書きで主要機能が整理されているか
  - [ ] Examplesセクションがあるか
  - [ ] Exampleは2個以上あるか

#### オプショナルパラメータ

- [ ] argument-hintが必要な場合、正しく設定されているか
- [ ] disable-model-invocationが必要な場合、正しく設定されているか
- [ ] user-invocableが必要な場合、正しく設定されているか
- [ ] allowed-toolsが必要な場合、有効なツール名が指定されているか
- [ ] modelが必要な場合、有効なモデル識別子が指定されているか
- [ ] contextが必要な場合、`fork`が正しく設定されているか
- [ ] agentが必要な場合、有効なsubagentタイプが指定されているか
- [ ] hooksが必要な場合、正しいYAML形式で設定されているか

## よくある質問

### Q1: nameフィールドはなぜジェランド形でなければならないのか？

A: Claude Codeの公式仕様で、スキル名は「進行中のアクション」を表現するジェランド形が推奨されています。これにより、スキルが「何をしているか」が明確になり、ユーザーが理解しやすくなります。

### Q2: descriptionで一人称を使うとなぜ問題なのか？

A: descriptionはシステムプロンプトに注入されるため、一人称を使うとClaudeが混乱し、スキルの発見性が低下します。第三人称で記述することで、Claudeがスキルを「外部のツール」として正しく認識できます。

### Q3: トリガーキーワードは何個必要か？

A: 最低4個以上を推奨します。多様な自然言語表現に対応するため、類義語や関連語を含めます。例: 「スキル作成」「新規スキル」「カスタムスキル追加」「Progressive Disclosure実装」

### Q4: disable-model-invocationはいつ使うべきか？

A: 副作用のあるワークフロー（デプロイ、コミット、削除など）で使用します。ユーザーが実行タイミングを制御できるようにします。Claudeが誤って起動することを防ぎます。

### Q5: user-invocableをfalseにするとどうなるか？

A: Claudeのみがスキルを実行可能になり、ユーザーは `/skill-name` コマンドで呼び出せなくなります。バックグラウンド知識スキルや内部ワークフロー用スキルで使用します。

### Q6: allowed-toolsはどのような場合に使うか？

A: セキュリティ制限が必要な場合や、権限の最小化原則に従う場合に使用します。例えば、ファイル読み込みのみを許可してファイル書き込みを禁止する場合などです。

### Q7: contextとagentはどのように使い分けるか？

A: `context: fork`は隔離された環境で実行する場合に使用します。`agent`は`context: fork`と共に使用し、特定のsubagentタイプ（Explore/Plan等）を指定します。会話履歴にアクセスさせたくない場合に有効です。

### Q8: hooksは何に使うか？

A: スキルのライフサイクルイベント（SessionStart等）に応じて自動化タスクを実行する場合に使用します。例: デプロイ開始時の通知、テスト実行前の環境準備など。

## 参考資料

- [Extend Claude with skills - Claude Code Docs](https://code.claude.com/docs/en/skills)
- [Agent Skills Overview - Claude API Docs](https://platform.claude.com/docs/en/agents-and-tools/agent-skills/overview)
- [Agent Skills Best Practices - Claude API Docs](https://platform.claude.com/docs/en/agents-and-tools/agent-skills/best-practices)

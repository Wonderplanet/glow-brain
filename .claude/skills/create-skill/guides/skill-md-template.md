# SKILL.md テンプレートガイド

SKILL.mdファイルの作成ルールとテンプレートについて説明します。

## 基本原則

### SKILL.mdは「ほぼ目次」にする

**目標行数:** 30-50行（最大でも100行以下）

**役割:**
- スキルの概要を伝える
- 実行フローを示す
- 詳細な参照ファイルへの導線を提供

**やってはいけないこと:**
- 詳細な説明を書く
- コード例を含める
- チェックリストを含める
- 実装パターンを詳細に記述する

## 必須フォーマット

```yaml
---
name: "{動詞}ing {対象}"  # 例: Implementing Laravel Migrations
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
---

# {スキル名}

簡潔な1-2行の説明

## Instructions

### 1. {ステップ1のタイトル}

簡潔な説明（1-2行）
参照: **[ファイル名.md](ファイル名.md)**

### 2. {ステップ2のタイトル}

簡潔な説明（1-2行）
参照リスト:
- **[パターン1](pattern-1.md)**
- **[パターン2](pattern-2.md)**

## 参照ドキュメント

- **[ファイル名.md](ファイル名.md)** - 役割の簡潔な説明
```

## フロントマター（---で囲まれた部分）

frontmatterには以下のフィールドが基本的に必要です:
- `name`: スキル名（オプショナル、デフォルト: ディレクトリ名）
- `description`: スキルの説明（推奨）

**詳細なルール、制約、全10パラメータの説明は [frontmatterガイド](frontmatter-guide.md) を参照してください。**

### 最小限の要件

#### nameフィールド
- ジェランド形（動詞 + -ing）を使用
- 最大64文字
- 小文字・数字・ハイフンのみ

良い例: `creating-code-skills`, `implementing-api-endpoints`

#### descriptionフィールド
- 複数行YAML形式（`|`）を使用
- 最大1024文字
- 第三人称で記述
- 【トリガー】セクション（4個以上のキーワード）
- 簡潔な技術要約（1-2文）
- 箇条書きで主要機能を整理
- Examplesセクション（2個以上）

**テンプレート:**

```yaml
description: |
  【トリガー】キーワード1、キーワード2、キーワード3、キーワード4

  簡潔な技術要約（1-2文）。

  対応範囲/主要機能:
  - 機能1
  - 機能2

  Examples:
  <example>user: '例' → スキル起動</example>
```

**詳細なルール、良い例・悪い例は [frontmatterガイド](frontmatter-guide.md) を参照してください。**

## Instructions セクション

### ステップ数

**推奨:** 3-5ステップ
**最大:** 7ステップまで

### ステップの書き方

**形式:**

```markdown
### {番号}. {ステップのタイトル}

簡潔な説明（1-2行）
参照: **[ファイル名](ファイル名.md)**
```

**良い例:**

```markdown
### 1. スキルの基本情報を収集

ユーザーにスキルの目的、使用状況、対象範囲を質問します。

### 2. 既存コードを調査

対象範囲に基づいて実装パターン、命名規則、プロジェクト固有の制約を特定します。

### 3. スコープ評価と分割提案

整理したルールを分析し、必要に応じて複数スキルへの分割提案を行います。
参照: **[スコープ評価ガイド](guides/skill-scope-evaluation.md)**
```

**悪い例:**

```markdown
### 1. 最初のステップ

まず、ユーザーに以下の質問をします：
- スキルの目的は何ですか？
- どのような場面で使いますか？
- 対象範囲は何ですか？

次に、以下のポイントを確認します：
- ディレクトリ構造
- ファイル命名規則
- 既存の実装パターン

（詳細すぎる）
```

### 参照の記載方法

**単一ファイル参照:**

```markdown
参照: **[ファイル名](ファイル名.md)**
```

**複数ファイル参照:**

```markdown
参照リスト:
- **[パターン1](patterns/pattern-1.md)**
- **[パターン2](patterns/pattern-2.md)**
- **[例1](examples/example-1.md)**
```

## 参照ドキュメント セクション

### 構成

guides/, patterns/, examples/ のカテゴリごとに分類して記載します。

**テンプレート:**

```markdown
## 参照ドキュメント

### ガイド
- **[ファイル名](guides/xxx.md)** - 役割の簡潔な説明

### パターン
- **[ファイル名](patterns/xxx.md)** - 役割の簡潔な説明

### 実装例
- **[ファイル名](examples/xxx.md)** - 役割の簡潔な説明
```

### 説明の書き方

**良い例:**

```markdown
- **[スコープ評価](guides/skill-scope-evaluation.md)** - スキル分割の判断基準、複数分割案パターン
- **[単一スキルアプローチ](patterns/single-skill-approach.md)** - 1つのスキルとして作成する場合
- **[API開発の分割例](examples/split-example-api-dev.md)** - 3つのスキルに分割した実例
```

**悪い例:**

```markdown
# 説明が不十分
- **[スコープ評価](guides/skill-scope-evaluation.md)** - ガイド

# 説明が長すぎる
- **[スコープ評価](guides/skill-scope-evaluation.md)** - スキルを分割すべきかどうかを判断するための基準と、複数の分割案パターンを提示する方法について詳しく説明したガイドドキュメント
```

## 複数スキル作成時の追加セクション

複数のスキルに分割した場合、各スキルのSKILL.mdに「関連スキル」セクションを追加します。

```markdown
## 関連スキル

このスキルは以下のスキルと連携して動作します：

- **[api-research](../api-research/SKILL.md)** - API実装前の調査・分析
- **[api-testing](../api-testing/SKILL.md)** - API実装後のテスト

**典型的な使用フロー:**
1. `api-research` で既存実装を調査
2. **`api-implementation`** で実装（このスキル）
3. `api-testing` でテストを作成・実行
```

## 完成例

```yaml
---
name: Implementing API Endpoints
description: |
  【トリガー】新しいAPIを追加、エンドポイントを実装、API実装して、新規API作成

  新規APIエンドポイント追加の全体フロースキル。glow-schema確認からルーティング定義、Controller・ResultData・ResponseFactory実装、テストまでを統合。

  実装フロー:
  - glow-schema YAML仕様確認
  - ルーティング定義（routes/api.php）
  - Controller実装（バリデーション、UseCase呼び出し）
  - ResultData/ResponseFactory実装
  - テスト実装

  Examples:
  <example>
  Context: 新機能のAPI追加が必要
  user: '新しいガチャAPIを実装して'
  assistant: 'api-endpoint-implementationスキルを使用してガチャAPIの全体実装を進めます'
  <commentary>新規API実装の全体フローが必要</commentary>
  </example>

  <example>user: 'APIエンドポイントを追加したい' → api-endpoint-implementation起動</example>
---

# Implementing API Endpoints

新規APIエンドポイントを実装するための包括的なガイドです。

## Instructions

### 1. glow-schemaの確認

YAML定義からリクエストパラメータとレスポンス構造を確認します。
参照: **[api-schema-reference](../api-schema-reference/SKILL.md)**

### 2. ルーティングとController実装

ルートを定義し、Controllerにバリデーションとビジネスロジック呼び出しを実装します。

### 3. Domain層の実装

UseCase、Service、Repository、Modelを実装します。
参照: **[domain-layer](../domain-layer/SKILL.md)**

### 4. レスポンスの実装

ResponseFactoryでJSON形式のレスポンスを作成します。
参照: **[api-response](../api-response/SKILL.md)**

### 5. テストの実装

Unit Test、Feature Test、Scenarioテストを作成します。
参照: **[api-test-implementation](../api-test-implementation/SKILL.md)**

## 参照ドキュメント

### 関連スキル
- **[api-schema-reference](../api-schema-reference/SKILL.md)** - glow-schema YAML定義の確認方法
- **[domain-layer](../domain-layer/SKILL.md)** - ドメイン層の実装ガイド
- **[api-response](../api-response/SKILL.md)** - レスポンス実装ガイド
- **[api-test-implementation](../api-test-implementation/SKILL.md)** - テスト実装ガイド
```

## 検証チェックリスト

SKILL.md作成後、以下を確認してください：

### フロントマター（description）
- [ ] **【トリガー】セクションが先頭にあるか**
- [ ] **トリガーキーワードは4個以上あるか**
- [ ] **複数行YAML形式（`|`）を使用しているか**
- [ ] **簡潔な技術要約（1-2文）があるか**
- [ ] **箇条書きで主要機能が整理されているか**
- [ ] **Examplesセクションがあるか**
- [ ] **Exampleは2個以上あるか**
- [ ] **Context/User/Assistant/Commentaryパターンを使用しているか**

### 基本構成
- [ ] **行数は30-50行（最大100行以下）か**
- [ ] **nameフィールドはジェランド形（動詞 + -ing）か**
- [ ] **nameは64文字以内か**
- [ ] **nameは小文字・数字・ハイフンのみか**
- [ ] **descriptionは1024文字以内か**
- [ ] **descriptionは第三人称で記述されているか**
- [ ] Instructionsは3-5ステップ（最大7ステップ）か
- [ ] 各ステップの説明は1-2行に収まっているか
- [ ] 詳細な説明はすべて参照ファイルに記載されているか
- [ ] コード例やチェックリストは含まれていないか
- [ ] 参照ドキュメントセクションでカテゴリ分けされているか
- [ ] 複数スキルの場合、関連スキルセクションがあるか

## テスト方法（公式推奨）

スキル作成後は必ずテストを実施してください。公式ドキュメント（[Skill authoring best practices](https://platform.claude.com/docs/en/agents-and-tools/agent-skills/best-practices)）で推奨されている手順です。

### 1. 複数モデルでのテスト（必須）

**スキルの効果は使用するモデルに依存します。**実際に使用する全てのモデルでテストしてください。

**テスト観点:**

- **Claude Haiku**（軽量・経済的）
  - スキルが十分なガイダンスを提供しているか？
  - トリガーキーワードで自動起動するか？
  - 簡潔すぎて理解されないことはないか？

- **Claude Sonnet**（バランス型）
  - スキルが明確で効率的か？
  - 通常の使用シナリオで期待通り動作するか？

- **Claude Opus**（高性能推論）
  - スキルが過度な説明を避けているか？
  - 複雑な文脈でも適切に起動するか？

**テスト手順:**

```bash
# 1. 各モデルでClaudeを起動
claude --model sonnet  # または haiku, opus

# 2. descriptionに記載したトリガーキーワードで質問
例: 「マイグレーションを作成して」
例: 「新しいテーブルを作りたい」

# 3. スキルが自動起動することを確認
# 4. 期待通りの動作をすることを確認
```

### 2. Evaluation-Driven Development（推奨）

広範なドキュメント作成の**前に**評価を作成することが推奨されます。

**手順:**

1. **ギャップの特定**
   - スキルなしでClaudeに代表的なタスクを実行させる
   - 失敗箇所や不足している文脈を文書化

2. **評価シナリオの作成**
   - **最低3つ**のテストシナリオを作成
   - 実際の使用ケースを反映させる

3. **ベースライン測定**
   - スキルなしのパフォーマンスを測定
   - 比較基準を確立

4. **最小限の指示を作成**
   - ギャップに対応するだけの内容を記述
   - 過剰なドキュメント化を避ける

5. **イテレーション**
   - 評価を実行
   - ベースラインと比較
   - 必要に応じて改善

**評価構造の例:**

```json
{
  "skills": ["migration"],
  "query": "ユーザーテーブルにage_groupカラムを追加して",
  "expected_behavior": [
    "マイグレーションファイルを正しいディレクトリに作成",
    "up()メソッドでカラム追加を実装",
    "down()メソッドでロールバック処理を実装",
    "適切なカラム型を選択（enum, string等）"
  ]
}
```

### 3. チェックリスト（公式要件）

**Testing:**
- [ ] **最低3つの評価シナリオを作成したか**
- [ ] **Haiku、Sonnet、Opusの全モデルでテストしたか**
- [ ] **実際の使用シナリオでテストしたか**
- [ ] チームメンバーのフィードバックを反映したか（該当する場合）

### 4. トリガーキーワードの調整

テスト結果に基づいて、トリガーキーワードを調整します。

**調整が必要なケース:**

- スキルが起動しない → より一般的なキーワードを追加
- 無関係な場面で起動する → より具体的なキーワードに変更
- 特定のモデルで起動しない → そのモデル向けに追加キーワード

**調整例:**

```yaml
# 修正前: 起動しない
【トリガー】migration実行

# 修正後: 自然な表現を追加
【トリガー】マイグレーションを作成、マイグレーション実行、テーブルを作成、DBスキーマを変更
```

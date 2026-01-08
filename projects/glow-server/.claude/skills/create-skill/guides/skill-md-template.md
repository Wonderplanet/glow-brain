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
description: スキルの機能と使用タイミングを1行で説明（最大1024文字）
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

### name フィールド

**形式:** ジェランド形（動詞 + -ing）を使用

**良い例:**
- ✅ `Creating Code Skills`
- ✅ `Implementing API Endpoints`
- ✅ `Testing Laravel Applications`
- ✅ `Managing Database Migrations`

**悪い例:**
- ❌ `Code Skill Creation` （名詞形）
- ❌ `API Endpoint Implementation` （名詞形）
- ❌ `Create Skills` （動詞の原形）

### description フィールド

**形式:** スキルの機能と使用タイミングを1行で説明（最大1024文字）

**構成:**
- **前半:** 使用タイミング（「〜が必要な時に使用」「〜する際に使用」）
- **後半:** 機能の説明（何を実装するか、どう実装するか、何が得られるか）

**良い例:**

```yaml
description: 新しいClaude Codeスキルを作成する際に使用。基本情報の収集、既存コード調査、スコープ評価、複数スキルへの分割提案、ファイル構成設計、検証を包括的に実施する。
```

```yaml
description: API実装コードを作成する際に使用。マイグレーション、モデル、コントローラーの実装を行い、テストまで完了させる。
```

```yaml
description: 管理ツールでページネーションテーブルを実装する際に使用。RewardInfoGetTraitを使った報酬情報の取得と表示、N+1問題の回避を行う。
```

**悪い例:**

```yaml
# 使用タイミングが不明確
description: スキルを作成します。

# 機能が不明確
description: 新しいスキルを作成する際に使用。

# 長すぎる（1024文字を超える）
description: このスキルは新しいClaude Codeスキルを作成する際に使用します。まず基本情報を収集し、次に既存コードを調査し、その後ルールを整理して...（続く）
```

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
description: 新規APIエンドポイント追加が必要な時に使用。glow-schema確認からルーティング定義、Controller・ResultData・ResponseFactory実装、テストまでの全体フローを提供する。
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

- [ ] **行数は30-50行（最大100行以下）か**
- [ ] **nameフィールドはジェランド形（動詞 + -ing）か**
- [ ] **descriptionは使用タイミング + 機能説明の1行か**
- [ ] descriptionは最大1024文字以内か
- [ ] Instructionsは3-5ステップ（最大7ステップ）か
- [ ] 各ステップの説明は1-2行に収まっているか
- [ ] 詳細な説明はすべて参照ファイルに記載されているか
- [ ] コード例やチェックリストは含まれていないか
- [ ] 参照ドキュメントセクションでカテゴリ分けされているか
- [ ] 複数スキルの場合、関連スキルセクションがあるか

# API開発の分割例

API開発を3つのスキルに分割した実例を紹介します。

## 分割前の課題

### 元の構想: 統合スキル「api-development」

最初は「API開発」という大きなスキルで調査・実装・テストを全てカバーしようとしました。

**想定される内容:**
- 既存実装の調査
- 依存関係の特定
- マイグレーション作成
- モデル・コントローラー実装
- サービスロジック実装
- テストケース作成
- テスト実行・デバッグ

**問題点:**

1. **SKILL.mdが肥大化**
   - 想定200行以上
   - 目次として機能しない

2. **参照ファイルが多すぎる**
   - 15個以上の参照ファイルが必要
   - どのファイルを読むべきか分からない

3. **使用タイミングが不明確**
   - いつこのスキルを使うべきかわからない
   - 常に全ての内容が読み込まれてトークンを消費

4. **保守性が低い**
   - 一部を修正すると全体に影響
   - 特定のフェーズだけの更新が困難

## 分割案の検討

### スコープ評価

**判断基準の確認:**

- ✅ 対象ファイルが10個以上 → **15個以上**
- ✅ 実装パターンが5つ以上 → **調査・設計・実装・テストで7パターン**
- ✅ 異なるツール・ライブラリを3つ以上 → **Grep/Read/Edit/Migration/PHPUnit/Mockeryなど**
- ✅ ワークフローが複数フェーズ → **調査→設計→実装→テスト**
- ✅ SKILL.mdが100行を超えそう → **想定200行以上**

→ **分割が必要**

### 分割軸の決定

**検討した軸:**

1. **フェーズ別分割** ← 採用
   - 調査 → 実装 → テスト
   - 各フェーズで異なるツールとアプローチを使用
   - 使用タイミングが明確

2. 技術領域別分割
   - データベース層 → ビジネスロジック層 → API契約層
   - 各層で技術スタックが異なる
   - ただし、実装フローが分かりにくい

3. 機能別分割
   - CRUD → 複雑なクエリ → バッチ処理
   - 機能ごとに完結するが、横断的な知識が必要

**採用理由:**
- フェーズ別分割が最も自然な開発フロー
- 各フェーズで集中できる
- 必要なフェーズだけを使える

## 分割後の構成

### 1. api-research スキル（調査・分析）

**ファイル構成:**

```
.claude/skills/api-research/
├── SKILL.md (35行)
├── guides/
│   ├── search-strategies.md (120行)
│   └── dependency-analysis.md (95行)
├── patterns/
│   └── investigation-workflow.md (140行)
└── examples/
    └── existing-api-analysis.md (110行)
```

**SKILL.md (抜粋):**

```yaml
---
name: Researching API Implementations
description: 既存API実装を調査・分析する際に使用。コード構造、DB設計、依存関係の特定を行い、影響範囲を明確にする。
---

# Researching API Implementations

既存API実装の調査と分析をサポートします。

## Instructions

### 1. 対象範囲の特定

変更が必要なファイルと影響範囲を特定します。
参照: **[調査戦略](guides/search-strategies.md)**

### 2. 依存関係の分析

関連するモデル、サービス、コントローラーを特定します。
参照: **[依存関係分析](guides/dependency-analysis.md)**

### 3. 調査結果のまとめ

調査結果を整理し、実装方針を決定します。
参照: **[調査ワークフロー](patterns/investigation-workflow.md)**

## 参照ドキュメント

### ガイド
- **[調査戦略](guides/search-strategies.md)** - Glob/Grepの効率的な使い方
- **[依存関係分析](guides/dependency-analysis.md)** - 影響範囲の特定方法

### パターン
- **[調査ワークフロー](patterns/investigation-workflow.md)** - 調査の進め方

### 実装例
- **[既存API分析例](examples/existing-api-analysis.md)** - 実際の調査例

## 関連スキル

このスキルは以下のスキルと連携して動作します：

- **[api-implementation](../api-implementation/SKILL.md)** - 調査結果を元にした実装
- **[api-testing](../api-testing/SKILL.md)** - 実装後のテスト

**典型的な使用フロー:**
1. **`api-research`** で既存実装を調査（このスキル）
2. `api-implementation` で実装
3. `api-testing` でテストを作成・実行
```

**規模:**
- SKILL.md: 35行
- 参照ファイル: 4個（合計465行）
- 総行数: 約500行

### 2. api-implementation スキル（実装）

**ファイル構成:**

```
.claude/skills/api-implementation/
├── SKILL.md (45行)
├── guides/
│   ├── migration-guide.md (150行)
│   └── domain-layer-guide.md (180行)
├── patterns/
│   ├── controller-pattern.md (120行)
│   └── service-pattern.md (140行)
└── examples/
    ├── simple-endpoint.md (95行)
    └── complex-endpoint.md (130行)
```

**SKILL.md (抜粋):**

```yaml
---
name: Implementing API Endpoints
description: API実装コードを作成する際に使用。マイグレーション、モデル、コントローラー、サービスの実装を行い、レスポンス形式を整える。
---

# Implementing API Endpoints

API実装の全体フローをサポートします。

## Instructions

### 1. マイグレーションの作成

DBスキーマの変更を実装します。
参照: **[マイグレーションガイド](guides/migration-guide.md)**

### 2. Domain層の実装

Model/Repository/Service/UseCaseを実装します。
参照: **[Domain層ガイド](guides/domain-layer-guide.md)**

### 3. Controllerの実装

ルーティング、バリデーション、ビジネスロジック呼び出しを実装します。
参照リスト:
- **[Controllerパターン](patterns/controller-pattern.md)**
- **[Serviceパターン](patterns/service-pattern.md)**

### 4. レスポンスの実装

ResponseFactoryでJSON形式のレスポンスを作成します。

### 5. 実装例の参照

実装パターンを実例で確認します。
参照リスト:
- **[シンプルなエンドポイント](examples/simple-endpoint.md)**
- **[複雑なエンドポイント](examples/complex-endpoint.md)**

## 参照ドキュメント

### ガイド
- **[マイグレーションガイド](guides/migration-guide.md)** - DB変更の実装方法
- **[Domain層ガイド](guides/domain-layer-guide.md)** - ドメイン層の実装方法

### パターン
- **[Controllerパターン](patterns/controller-pattern.md)** - Controller実装パターン
- **[Serviceパターン](patterns/service-pattern.md)** - Service実装パターン

### 実装例
- **[シンプルなエンドポイント](examples/simple-endpoint.md)** - 基本的なCRUD実装例
- **[複雑なエンドポイント](examples/complex-endpoint.md)** - 複雑なビジネスロジック実装例

## 関連スキル

このスキルは以下のスキルと連携して動作します：

- **[api-research](../api-research/SKILL.md)** - 実装前の調査・分析
- **[api-testing](../api-testing/SKILL.md)** - 実装後のテスト

**典型的な使用フロー:**
1. `api-research` で既存実装を調査
2. **`api-implementation`** で実装（このスキル）
3. `api-testing` でテストを作成・実行
```

**規模:**
- SKILL.md: 45行
- 参照ファイル: 6個（合計815行）
- 総行数: 約860行

### 3. api-testing スキル（テスト）

**ファイル構成:**

```
.claude/skills/api-testing/
├── SKILL.md (40行)
├── guides/
│   ├── phpunit-guide.md (130行)
│   ├── factory-guide.md (110行)
│   └── mockery-guide.md (125行)
├── patterns/
│   ├── unit-test-pattern.md (140行)
│   ├── feature-test-pattern.md (160行)
│   └── scenario-test-pattern.md (145行)
└── examples/
    └── test-examples.md (150行)
```

**SKILL.md (抜粋):**

```yaml
---
name: Testing API Endpoints
description: APIテストを作成・実行する際に使用。Unit/Feature/Scenarioテストの実装とデバッグを行い、カバレッジを確保する。
---

# Testing API Endpoints

APIテストの実装をサポートします。

## Instructions

### 1. テストツールの理解

PHPUnit、Factory、Mockeryの使い方を確認します。
参照リスト:
- **[PHPUnitガイド](guides/phpunit-guide.md)**
- **[Factoryガイド](guides/factory-guide.md)**
- **[Mockeryガイド](guides/mockery-guide.md)**

### 2. Unitテストの実装

単体レベルのテストを実装します。
参照: **[Unitテストパターン](patterns/unit-test-pattern.md)**

### 3. Featureテストの実装

機能レベルのテストを実装します。
参照: **[Featureテストパターン](patterns/feature-test-pattern.md)**

### 4. Scenarioテストの実装

シナリオベースのテストを実装します。
参照: **[Scenarioテストパターン](patterns/scenario-test-pattern.md)**

### 5. テストの実行とデバッグ

テストを実行し、失敗した場合はデバッグします。
参照: **[テスト例](examples/test-examples.md)**

## 参照ドキュメント

### ガイド
- **[PHPUnitガイド](guides/phpunit-guide.md)** - PHPUnitの基本的な使い方
- **[Factoryガイド](guides/factory-guide.md)** - テストデータの作成方法
- **[Mockeryガイド](guides/mockery-guide.md)** - モック作成の方法

### パターン
- **[Unitテストパターン](patterns/unit-test-pattern.md)** - Unitテストの実装パターン
- **[Featureテストパターン](patterns/feature-test-pattern.md)** - Featureテストの実装パターン
- **[Scenarioテストパターン](patterns/scenario-test-pattern.md)** - Scenarioテストの実装パターン

### 実装例
- **[テスト例](examples/test-examples.md)** - 実際のテストコード例

## 関連スキル

このスキルは以下のスキルと連携して動作します：

- **[api-research](../api-research/SKILL.md)** - 調査・分析
- **[api-implementation](../api-implementation/SKILL.md)** - 実装

**典型的な使用フロー:**
1. `api-research` で既存実装を調査
2. `api-implementation` で実装
3. **`api-testing`** でテストを作成・実行（このスキル）
```

**規模:**
- SKILL.md: 40行
- 参照ファイル: 7個（合計960行）
- 総行数: 約1000行

## 分割の効果

### Before（統合スキル）

```
api-development スキル
├── SKILL.md (200行) ← 長すぎる
├── 参照ファイル (15個以上) ← 多すぎる
└── 総行数: 約2500行 ← 大きすぎる

問題点:
- 使用タイミングが不明確
- 全ての内容が常に読み込まれる
- トークン消費が大きい
- 保守性が低い
```

### After（分割スキル）

```
api-research スキル
├── SKILL.md (35行)
├── 参照ファイル (4個)
└── 総行数: 約500行

api-implementation スキル
├── SKILL.md (45行)
├── 参照ファイル (6個)
└── 総行数: 約860行

api-testing スキル
├── SKILL.md (40行)
├── 参照ファイル (7個)
└── 総行数: 約1000行

利点:
- 各スキルの責務が明確
- 使用タイミングが分かりやすい
- 必要なスキルだけを読み込める
- トークン消費を削減（必要なスキルのみ読み込み）
- 各スキルが独立して保守可能
```

### トークン消費の比較

**統合スキル:**
- 常に全体（約2500行）を読み込む
- 推定トークン: 約5000トークン

**分割スキル:**
- 調査フェーズ: api-research のみ（約500行）
- 実装フェーズ: api-implementation のみ（約860行）
- テストフェーズ: api-testing のみ（約1000行）
- 推定トークン: 約1000-2000トークン（必要なスキルのみ）

→ **最大60%のトークン削減**

## 運用での成功例

### 実際の使用フロー

**ステップ1: 調査フェーズ**
```
ユーザー: 「/api/user/profileエンドポイントを修正したい」
Claude: api-research スキルを使用
→ 既存実装を調査、依存関係を特定
```

**ステップ2: 実装フェーズ**
```
ユーザー: 「調査結果を元に実装してください」
Claude: api-implementation スキルを使用
→ マイグレーション、モデル、コントローラーを実装
```

**ステップ3: テストフェーズ**
```
ユーザー: 「実装のテストを作成してください」
Claude: api-testing スキルを使用
→ Unit/Feature/Scenarioテストを実装
```

### ユーザーフィードバック

> 「以前は『API開発スキル』を使うと全ての情報が一度に来て圧倒されていたが、分割後は必要なフェーズだけを使えるので分かりやすい」

> 「調査だけしたい時に api-research を使えば良いので、トークン消費が減って効率的」

> 「各スキルが独立しているので、テスト方法だけ更新したい時に api-testing だけ修正すれば良い」

## まとめ

API開発を3つのスキルに分割した結果：

**成功要因:**
- フェーズ別分割が開発フローに沿っている
- 各スキルの責務が明確
- 使用タイミングが分かりやすい
- トークン消費を最大60%削減
- 独立した保守が可能

**適用可能な他のケース:**
- 管理ツール開発（調査→実装→テスト）
- バッチ処理開発（調査→実装→テスト）
- リファクタリング（分析→設計→実装→検証）

この分割パターンは、明確なフェーズがある開発タスクに広く適用できます。

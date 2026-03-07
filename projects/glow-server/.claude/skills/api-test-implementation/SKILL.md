---
name: api-test-implementation
description: glow-server API開発におけるPHPUnitテスト実装をFactoryとMockeryパターンでサポート。Service、Repository、Model、Controller、UseCaseレイヤーのUnit/Feature/Scenarioテスト実装時に使用。テスト作成、PHPUnitテスト実装、unit/feature/scenarioテスト追加が必要な場合に使用。 (project)
---

# Implementing API Tests

glow-serverプロジェクトのAPI開発におけるPHPUnitテスト実装を支援するスキル。

## Instructions

### 1. テスト構造の理解

テストファイルの配置場所、命名規則、ディレクトリ構造を把握する。

参照: **[test-structure.md](test-structure.md)**

### 2. テストパターンの選択と実装

要件に応じて適切なテストパターン（Unit/Feature/Scenario）を選択し、実装する。

参照:
- **[test-patterns.md](test-patterns.md)** - 各テストパターンの実装方法
- **[examples.md](examples.md)** - 実装例

### 3. テストデータの準備

Factory、Mockery、Support Traitsを使ってテストデータを準備する。

参照:
- **[factory-guide.md](factory-guide.md)** - Factory使用方法
- **[mockery-guide.md](mockery-guide.md)** - Mockery使用方法
- **[support-traits-guide.md](support-traits-guide.md)** - Support Traits

### 4. テストの実行と検証

テストを実行し、失敗時にはデバッグを行う。

参照: **[test-execution.md](test-execution.md)**

## 参照ドキュメント

- **[test-structure.md](test-structure.md)** - テストファイル配置・命名規則
- **[test-patterns.md](test-patterns.md)** - Unit/Feature/Scenarioテストパターン
- **[factory-guide.md](factory-guide.md)** - Factory使用方法
- **[mockery-guide.md](mockery-guide.md)** - Mockery使用方法
- **[support-traits-guide.md](support-traits-guide.md)** - Support Traits
- **[test-execution.md](test-execution.md)** - テスト実行・デバッグ方法
- **[examples.md](examples.md)** - 実装例（UseCase/Service/Controller/Scenario）

## アーキテクチャドキュメント参照

テスト戦略とアーキテクチャの詳細は以下を参照してください：

| ドキュメント | 用途 |
|------------|------|
| `docs/01_project/architecture/07_テスト戦略.md` | レイヤーごとのテスト方針、モック使用指針 |
| `docs/01_project/architecture/01_レイヤードアーキテクチャ.md` | 各レイヤーの責務（テスト対象の理解） |

### レイヤーごとのテスト方針（クイックリファレンス）

| レイヤー | テスト種別 | モック使用 | 優先度 |
|---------|----------|----------|-------|
| **Controller** | 結合テスト | UseCaseのみ | 低 |
| **UseCase** | 結合テスト | 他ドメインDelegator | 中 |
| **Service/Repository/Model** | 単体テスト | 基本しない | 高 |

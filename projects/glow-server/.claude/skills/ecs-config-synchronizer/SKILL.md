---
name: "Synchronizing ECS Configurations"
description: api/adminの環境変数やDockerfile変更を検出し、codebuild配下のECS設定ファイル(taskdefinitions.json, buildspec.yml等)を自動調整する際に使用。新しい環境変数追加、Dockerfile変更、リソース要件変更時に、ECS Task Definitionとbuildspec設定の同期を支援する。
---

# ECS Configuration Synchronizer

api/adminディレクトリの変更を検出し、codebuild配下のECS設定ファイルを適切に調整します。

## Instructions

### 1. 変更の検出

api/adminディレクトリの以下の変更を検出：
- 環境変数の追加・変更・削除
- Dockerfileの変更
- リソース要件の変更

参照: **[detection-rules.md](detection-rules.md)**

### 2. ECS設定ファイルの構造理解

codebuild配下のファイル構造と役割を理解：
- Task Definition (taskdefinitions.json)
- BuildSpec (buildspec.yml)
- Image Definitions

参照:
- **[guides/taskdefinition-structure.md](guides/taskdefinition-structure.md)**
- **[guides/buildspec-variables.md](guides/buildspec-variables.md)**

### 3. パターン別の同期実装

検出した変更に応じて適切な同期処理を実行：
- 環境変数の同期: environment vs secrets判断
- Dockerfileビルド引数の同期

参照:
- **[patterns/env-variable-sync.md](patterns/env-variable-sync.md)**
- **[patterns/dockerfile-change-sync.md](patterns/dockerfile-change-sync.md)**

### 4. 検証

変更後の設定ファイルを検証：
- JSON構文チェック
- プレースホルダー形式の維持
- 必須フィールドの存在確認

## 参照ドキュメント

- **[detection-rules.md](detection-rules.md)** - 変更検出ルールと判断基準
- **[guides/taskdefinition-structure.md](guides/taskdefinition-structure.md)** - Task Definitionの構造解説
- **[guides/buildspec-variables.md](guides/buildspec-variables.md)** - BuildSpec変数とプレースホルダー
- **[patterns/env-variable-sync.md](patterns/env-variable-sync.md)** - 環境変数同期パターン
- **[patterns/dockerfile-change-sync.md](patterns/dockerfile-change-sync.md)** - Dockerfile変更同期パターン

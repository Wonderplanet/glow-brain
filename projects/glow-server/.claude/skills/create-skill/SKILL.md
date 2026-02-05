---
name: create-skill
description: |
  Claude Codeスキル作成。要件収集、既存コード調査、スコープ評価（単一/複数スキル分割判断）、ファイル構成設計（SKILL.md、guides/、patterns/、examples/）、Progressive Disclosureパターン適用、SKILL.mdテンプレート生成（30-50行）、検証・品質チェックを実施。以下の場合に使用: (1) 新しいスキルを作成する、(2) カスタムスキルを追加する、(3) 複雑なワークフローを複数スキルに分割する、(4) スキル構造を設計する、(5) 実装パターンをスキル化する、(6) Progressive Disclosure実装が必要
---

# Creating Code Skills

新しいClaude Codeスキルの作成をサポートします。Progressive Disclosureパターンに従い、複雑なスキルを複数に分割する場合の判定と提案を行います。

## Instructions

### 1. スキルの基本情報を収集

ユーザーにスキルの目的、使用状況、対象範囲を質問します。

### 2. 既存コードを調査

対象範囲に基づいて実装パターン、命名規則、プロジェクト固有の制約を特定します。

### 3. ルールを整理・分類

抽出したルールを共通ルール、パターン別ルール、制約・禁止事項、ベストプラクティスの軸で分類します。

### 4. スコープ評価と分割提案

整理したルールを分析し、必要に応じて複数スキルへの分割提案を行います。
参照: **[スコープ評価ガイド](guides/skill-scope-evaluation.md)**

### 5. ファイル構成の設計

Progressive Disclosureパターンに従ったディレクトリ構造を提案します。
参照: **[ファイル構成設計](guides/file-structure-design.md)**

### 6. SKILL.mdテンプレートの生成

各スキルのSKILL.mdを30-50行で作成します。
参照: **[テンプレートガイド](guides/skill-md-template.md)**

### 7. 参照ファイルの生成と検証

guides/patterns/examples/の各ファイルを生成し、整合性を確認します。
参照: **[検証チェックリスト](patterns/validation-checklist.md)**

## 参照ドキュメント

### ガイド
- **[スコープ評価](guides/skill-scope-evaluation.md)** - スキル分割の判断基準、複数分割案パターン
- **[ファイル構成設計](guides/file-structure-design.md)** - ディレクトリ構造、命名規則、ベストプラクティス
- **[テンプレートガイド](guides/skill-md-template.md)** - SKILL.md作成ルール

### パターン
- **[単一スキルアプローチ](patterns/single-skill-approach.md)** - 1つのスキルとして作成する場合
- **[分割スキルアプローチ](patterns/split-skill-approach.md)** - 複数スキルに分割する場合
- **[検証チェックリスト](patterns/validation-checklist.md)** - スキル完成後の確認項目

### 実装例
- **[API開発の分割例](examples/split-example-api-dev.md)** - 3つのスキルに分割した実例
- **[Admin開発の分割例](examples/split-example-admin.md)** - 2つのスキルに分割した実例
- **[プロジェクトの成功事例](examples/current-projects.md)** - migrationスキル、admin-reward-displayスキル

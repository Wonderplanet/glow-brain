---
name: game-spec-requirement-classifier
description: |
  【トリガー】要件抽出、要件分類、ゲーム体験仕様書から要件、仕様書分析、要件整理、実装前の要件整理

  ゲーム体験仕様書から要件を抽出し、6つのカテゴリに分類するスキル。クライアント・サーバー実装前の要件整理を支援。

  分類カテゴリ:
  - 機能要件（Functional Requirement）
  - 非機能要件（Non-Functional Requirement）
  - 運用要件（Operational Requirement）
  - 制約条件（Constraint）
  - 実装要件（Implementation Requirement）
  - ビジネス要件（Business Requirement）

  Examples:
  <example>
  Context: ゲーム体験仕様書からクライアント・サーバー実装を行う前段階
  user: 'この仕様書から要件を整理したい'
  assistant: 'game-spec-requirement-classifierスキルで要件を抽出・分類します'
  <commentary>実装前の要件整理が必要</commentary>
  </example>

  <example>user: 'ゲーム体験仕様書の要件分類をして' → game-spec-requirement-classifier起動</example>
  <example>user: '仕様書から要件を抽出したい' → game-spec-requirement-classifier起動</example>
disable-model-invocation: true
---

# ゲーム体験仕様書 要件抽出・分類スキル

ゲーム体験仕様書から要件を抽出し、6つのカテゴリに分類してクライアント・サーバー実装の準備を行います。

## Instructions

### 1. 仕様書の入力確認

ユーザーから提供されたゲーム体験仕様書のテキストを確認します。

### 2. 要件の抽出

仕様書から要件候補となる記述を抽出します。
参照: **[extraction-patterns.md](guides/extraction-patterns.md)**

### 3. 要件の分類

抽出した要件を6つのカテゴリに分類します。
参照: **[classification-rules.md](guides/classification-rules.md)**

### 4. 結果の出力

分類結果をMarkdownテーブル形式で出力します。
参照: **[output-format.md](templates/output-format.md)**

## 参照ドキュメント

### ガイド
- **[classification-rules.md](guides/classification-rules.md)** - 6つの要件分類の詳細定義と判定軸
- **[extraction-patterns.md](guides/extraction-patterns.md)** - 仕様書から要件を抽出する際のパターン

### テンプレート
- **[output-format.md](templates/output-format.md)** - 出力フォーマットのテンプレート

### 実装例
- **[mission-spec-example.md](examples/mission-spec-example.md)** - ミッション仕様書の分類例
- **[gacha-spec-example.md](examples/gacha-spec-example.md)** - ガチャ仕様書の分類例

## 関連スキル

このスキルは以下のスキルと連携して動作します:

- **[api-design-principles](../api-design-principles/SKILL.md)** - サーバー側: API設計書作成時の設計思想

**典型的な使用フロー:**

### サーバー実装の場合
1. **`game-spec-requirement-classifier`** で要件を抽出・分類（このスキル）
2. `api-design-principles` で設計原則を参照しながらAPI設計書を作成
3. サーバーAPI実装

### クライアント実装の場合
1. **`game-spec-requirement-classifier`** で要件を抽出・分類（このスキル）
2. クライアント設計書を作成（※今後、クライアント向けスキル作成予定）
3. クライアント実装

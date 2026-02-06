---
name: api-design-principles
description: |
  【トリガー】API設計書作成、DB設計検討、テーブル設計、既存流用検討、ミッション設計、仕様書分析

  API設計書作成時に設計思想を注入し、最適な設計判断を支援するスキル。

  設計原則:
  - データベース設計の慎重性（無駄なテーブル・列を増やさない）
  - 仕様書の批判的検討（既存実装での対応可能性を深く検討）
  - ミッション実装のコスト意識（既存流用を最優先）

  Examples:
  <example>
  Context: ゲーム体験仕様書からAPI設計書を作成する状況
  user: 'API設計書を作りたい'
  assistant: 'api-design-principlesスキルの設計原則を参照します'
  <commentary>設計思想の注入が必要</commentary>
  </example>

  <example>user: '新しいテーブルが必要か検討したい' → api-design-principles参照</example>
  <example>user: 'ミッションを既存流用できるか確認したい' → api-design-principles参照</example>
user-invocable: false
---

# API設計原則ガイド

## スキルの目的

API設計書を作成する際、設計思想についてのコンテキストを必要に応じて注入し、最適なAPI設計判断を支援します。

## 使用タイミング

以下の状況で本スキルの原則を参照してください:

- ゲーム体験仕様書からAPI設計書を作成する時
- 新規機能のDB設計を検討する時
- 既存機能の拡張・改修を設計する時
- サーバー実現方針を検討する時

## 設計原則

### 1. データベース設計の慎重性

**参照**: [principles/database-design.md](principles/database-design.md)

ユーザーデータの管理テーブルや列は、必須ではない限り無闇に増やしません。

### 2. 仕様書の批判的検討

**参照**: [principles/specification-review.md](principles/specification-review.md)

仕様書の内容をそのまま実装するのではなく、既存実装での対応可能性を深く検討します。

### 3. ミッション実装のコスト意識

**参照**: [principles/mission-reuse.md](principles/mission-reuse.md)

新規ミッション実装は高コストであるため、既存の無理のない流用を最優先に考えます。

## API設計書フォーマット

**参照**: [templates/api-design-template.md](templates/api-design-template.md)

標準的なAPI設計書の構成とセクションごとの記載内容を定義しています。

## 関連ドキュメント

- **要件分類**: `domain/tasks/design/api/rules/ゲーム体験仕様書_要件分類ルール.md`
- **サーバー要件判定**: `domain/tasks/design/api/rules/サーバー要件判定ルール.md`
- **サーバー実現方針**: `domain/tasks/design/api/rules/サーバー実現方針ルール.md`

## 原則の追加方法

新しい設計原則を追加する場合:

1. `principles/` 配下に新しいMarkdownファイルを作成
2. 本ファイルの「設計原則」セクションに項目を追加
3. frontmatterの `description` を更新（必要に応じて）

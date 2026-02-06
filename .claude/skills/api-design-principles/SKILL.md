# API設計原則ガイド

---
description: API設計書作成時に設計思想を注入し、最適な設計判断をサポート
keywords: ["API設計", "設計原則", "DB設計", "既存流用", "コスト意識"]
userInvocable: false
version: 1.0.0
status: stable
appliesTo: ["api-design", "database-design", "requirement-analysis"]
lastUpdated: 2025-02-07
---

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
3. 適切な `keywords` と `appliesTo` を更新

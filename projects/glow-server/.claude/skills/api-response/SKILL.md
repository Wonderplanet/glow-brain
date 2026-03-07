---
name: api-response
description: |
  glow-server APIのJSONレスポンス実装をResponseFactoryとResponseDataFactoryパターンを使って行う。以下の場合に使用: (1) 新しいAPIエンドポイントのレスポンス実装、(2) ResponseFactoryまたはResponseDataFactoryへのメソッド追加、(3) glow-schema YAML仕様に準拠したJSONレスポンスの作成、(4) レスポンスキーまたは返り値の定義。全ての日時データは StringUtil::convertToISO8601() を使ってISO8601形式にする必要がある。glow-schema YAML定義と一致するcamelCaseレスポンスキー、null安全性、PHPDoc型定義を保証する。 (project)
---

# Implementing API Responses

## Instructions

APIレスポンス実装の依頼を受けたら、以下の手順で実装してください。

### 1. [common-rules.md](common-rules.md) で必須ルールを確認

**最重要:**
- ✅ 日時データは必ず `StringUtil::convertToISO8601()` で変換
- レスポンスキーはcamelCase
- glow-schemaのYAML定義と一致

### 2. 実装パターンを選択

- **ResponseFactoryメソッド追加** → [response-factory-guide.md](response-factory-guide.md)
- **ResponseDataFactoryメソッド追加** → [response-data-factory-guide.md](response-data-factory-guide.md)

### 3. [examples.md](examples.md) からテンプレートをコピー

### 4. 実装後チェック

- [ ] 日時データを `StringUtil::convertToISO8601()` で変換
- [ ] レスポンスキーがglow-schemaと一致
- [ ] nullチェックを実装
- [ ] PHPDocで型定義

## 参照ドキュメント

- **[common-rules.md](common-rules.md)** - 必須ルールと禁止事項
- **[response-factory-guide.md](response-factory-guide.md)** - ResponseFactory実装手順
- **[response-data-factory-guide.md](response-data-factory-guide.md)** - ResponseDataFactory実装手順
- **[examples.md](examples.md)** - コピー可能なテンプレート集

---
name: "Implementing API Responses"
description: glow-serverでAPIレスポンスを実装する際に使用。ResponseFactoryまたはResponseDataFactoryにメソッドを追加してJSONレスポンスを作成する。日時データは必ずStringUtil::convertToISO8601()で変換し、レスポンスキーはglow-schemaのYAML定義と一致させる。
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

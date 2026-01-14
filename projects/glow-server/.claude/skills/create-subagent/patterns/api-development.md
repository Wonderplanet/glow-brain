# API開発系エージェントパターン

このドキュメントでは、API開発に関連するサブエージェントの実装パターンを説明します。

## パターン分類

### 1. API実装エージェント

新規APIエンドポイントの実装を担当するエージェント。

**推奨設定:**
- **model**: `sonnet` または `opus`（複雑なAPI設計の場合）
- **color**: `purple`

**description テンプレート:**
```yaml
description: |
  {API種別}の実装を専門に担当。{glow-schema定義/要件}から{Controller/UseCase/Repository等}を実装し、{バリデーション/レスポンス生成/テスト}まで一貫して対応。{具体的なAPI例}の実装時に使用。
```

**実装例:**
```markdown
---
name: api-endpoint-implementation
description: 新規APIエンドポイント追加が必要な時に使用。glow-schema確認からルーティング定義、Controller・ResultData・ResponseFactory実装、テストまでの全体フローを提供し、既存スキル（migration、domain-layer、api-request-validation、api-response、api-test-implementation）を適切な順序で統合する。
model: sonnet
color: purple
---
```

### 2. ドメイン層実装エージェント

ビジネスロジックとドメインモデルの実装を担当。

**推奨設定:**
- **model**: `sonnet`
- **color**: `purple`

**description テンプレート:**
```yaml
description: |
  Domain層の実装を担当。{Entity/Model/Repository/Service/UseCase}を{クリーンアーキテクチャ原則}に基づいて実装。{ドメイン分類（通常/Game/Resource/Common）}に応じた適切な配置と、{既存パターン}との整合性を確保。
```

### 3. API検証・バリデーションエージェント

リクエストパラメータの検証ロジックを実装。

**推奨設定:**
- **model**: `sonnet`
- **color**: `blue`

**description テンプレート:**
```yaml
description: |
  APIリクエストバリデーション実装専門。glow-schemaのYAML定義から{Laravel validation rules}を生成し、{Controller層}で適用。{型変換ルール}と{エラーメッセージ}の一貫性を保証。
```

### 4. APIレスポンス生成エージェント

JSONレスポンスの生成とフォーマットを担当。

**推奨設定:**
- **model**: `sonnet`
- **color**: `purple`

**description テンプレート:**
```yaml
description: |
  API JSONレスポンス実装専門。{ResponseFactory/ResponseDataFactory}にメソッドを追加し、{glow-schema YAML定義}と完全一致するレスポンス構造を生成。日時データは必ず{StringUtil::convertToISO8601()}で変換。
```

## 実装フロー

### 典型的なAPI開発フロー

```
1. スキーマ確認 (.claude/skills/api-schema-reference)
   ↓
2. マイグレーション (.claude/skills/migration)
   ↓
3. ドメイン層実装 (.claude/skills/domain-layer)
   ↓
4. バリデーション (.claude/skills/api-request-validation)
   ↓
5. レスポンス生成 (.claude/skills/api-response)
   ↓
6. テスト実装 (.claude/skills/api-test-implementation)
   ↓
7. テスト実行 (.claude/skills/api-test-runner)
```

エージェントはこのフロー全体または一部を自動化できます。

## glow-server 固有の考慮事項

### スキーマ参照

```markdown
## スキーマ確認

glow-schemaリポジトリのYAML定義を参照：
- リクエストパラメータ: `{endpoint}/request.yaml`
- レスポンス構造: `{endpoint}/response.yaml`

参照スキル: **api-schema-reference**
```

### ディレクトリ構造

```markdown
## ファイル配置

- Controller: `api/app/Http/Controllers/Api/{Version}/{ControllerName}.php`
- UseCase: `api/app/Domain/{DomainName}/UseCases/{UseCaseName}.php`
- Test: `api/tests/Feature/Api/{Version}/{TestName}.php`
```

### 実行環境

```markdown
## 実行コマンド

全てのコマンドはDocker環境で実行：

\`\`\`bash
# テスト実行
sail test

# マイグレーション
sail migrate

# コード品質チェック
sail check
\`\`\`
```

## 命名規則

### エージェント名

- **パターン**: `api-{機能}-{役割}`
- **例**:
  - `api-endpoint-implementation`
  - `api-request-validation`
  - `api-response-factory`
  - `api-domain-layer-builder`

### カラー選択基準

| 機能 | カラー | 理由 |
|------|--------|------|
| API実装全般 | `purple` | コア機能開発 |
| バリデーション | `blue` | 品質保証 |
| テスト関連 | `blue` | 品質保証 |
| スキーマ関連 | `yellow` | ドキュメント・設計 |

## エージェント作成チェックリスト

API開発系エージェントを作成する際の確認項目：

- [ ] glow-schemaとの連携方法が明記されている
- [ ] Docker環境（sail）での実行を前提としている
- [ ] api/adminどちらのディレクトリに対応するか明確
- [ ] 既存スキルとの統合方法が説明されている
- [ ] Laravel規約（PSR、命名規則）に準拠している
- [ ] テスト実装までカバーしている（または明示的に除外）
- [ ] エラーハンドリング方針が定義されている

## 関連スキル

API開発系エージェントが参照すべき既存スキル：

- **[migration](../../migration/)** - データベースマイグレーション
- **[domain-layer](../../domain-layer/)** - ドメイン層実装
- **[api-schema-reference](../../api-schema-reference/)** - スキーマ参照
- **[api-request-validation](../../api-request-validation/)** - リクエスト検証
- **[api-response](../../api-response/)** - レスポンス生成
- **[api-test-implementation](../../api-test-implementation/)** - テスト実装

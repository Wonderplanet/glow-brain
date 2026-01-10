# 環境変数同期パターン

api/adminの環境変数変更をECS Task Definitionに反映する実装パターンです。

## 目次

1. [環境変数追加のワークフロー](#環境変数追加のワークフロー)
2. [パターン1: environment配列への追加](#パターン1-environment配列への追加)
3. [パターン2: secrets配列への追加](#パターン2-secrets配列への追加)
4. [パターン3: 既存環境変数の変更](#パターン3-既存環境変数の変更)
5. [パターン4: 環境変数の削除](#パターン4-環境変数の削除)
6. [実装チェックリスト](#実装チェックリスト)

## 環境変数追加のワークフロー

### ステップ1: 変更内容の特定

```bash
# .env.exampleの差分確認
git diff api/.env.example
git diff admin/.env.example
```

**例**:
```diff
+ NEW_LAMBDA_FUNCTION_NAME=example-lambda
+ NEW_AWS_DYNAMODB_TABLE=example-table
+ NEW_API_SECRET_KEY=xxxxx
```

### ステップ2: environment vs secretsの判断

**判断フローチャート**:
```
環境変数名を確認
  ↓
PASSWORD/PASS/KEY/TOKEN/SECRET/CREDENTIAL を含む？
  ↓ YES → secrets配列
  ↓ NO
  ↓
HOST を含む？
  ↓ YES → secrets配列（例外あり）
  ↓ NO → environment配列
```

### ステップ3: API/Adminの区別

- **API関連**: `codebuild/artifacts/taskdefinitions.json`
- **Admin関連**: `codebuild/artifacts/taskdefinitions-admin.json`

### ステップ4: 該当コンテナの特定

**API**:
- `php`コンテナ: ほぼ全ての環境変数
- `nginx`コンテナ: 環境変数なし（通常）
- `datadog-agent`コンテナ: DataDog関連のみ

**Admin**:
- `php-admin`コンテナ: ほぼ全ての環境変数
- `nginx-admin`コンテナ: 環境変数なし（通常）

## パターン1: environment配列への追加

### 使用ケース

以下の環境変数を追加する場合：
- AWS リソース名（Bucket、Lambda、DynamoDB等）
- データベース名、ポート番号
- 環境名、リージョン

### 実装例1: AWS Lambda関数名の追加

**api/.env.example**:
```bash
NEW_NOTIFICATION_LAMBDA_FUNCTION_NAME=notification-lambda
```

**taskdefinitions.json** (`php`コンテナの`environment`配列):
```json
{
  "containerDefinitions": [
    {
      "name": "php",
      "environment": [
        // ... 既存の環境変数
        {
          "name": "MAINTENANCE_LAMBDA_FUNCTION_NAME",
          "value": "<PJ_NAME>-<INFRA_ENV>-maintenance-change-route"
        },
        {
          "name": "NEW_NOTIFICATION_LAMBDA_FUNCTION_NAME",
          "value": "<PJ_NAME>-<INFRA_ENV>-notification-lambda"
        }
      ]
    }
  ]
}
```

**ポイント**:
- `value`にはプレースホルダー `<PJ_NAME>-<INFRA_ENV>-` を使用
- 関連する環境変数の近くに配置（例: 他のLambda関数名の近く）

### 実装例2: DynamoDBテーブル名の追加

**admin/.env.example**:
```bash
AWS_DYNAMODB_USER_SESSION_TABLE=user-session-table
```

**taskdefinitions-admin.json** (`php-admin`コンテナ):
```json
{
  "containerDefinitions": [
    {
      "name": "php-admin",
      "environment": [
        // ... 既存の環境変数
        {
          "name": "AWS_DYNAMODB_MAINTENANCE_TABLE",
          "value": "<PJ_NAME>-<INFRA_ENV>-maintenance"
        },
        {
          "name": "AWS_DYNAMODB_USER_SESSION_TABLE",
          "value": "<PJ_NAME>-<INFRA_ENV>-user-session-table"
        }
      ]
    }
  ]
}
```

### 実装例3: 固定値の環境変数

**api/.env.example**:
```bash
NEW_EXTERNAL_API_TIMEOUT=30
```

**taskdefinitions.json**:
```json
{
  "name": "NEW_EXTERNAL_API_TIMEOUT",
  "value": "30"
}
```

**ポイント**: 環境依存しない固定値はそのまま記述

## パターン2: secrets配列への追加

### 使用ケース

以下の環境変数を追加する場合：
- パスワード、APIキー、トークン
- 認証情報、証明書
- データベースホスト（セキュリティ上の理由）

### 実装例1: 新しいAPIキーの追加

**api/.env.example**:
```bash
NEW_EXTERNAL_API_KEY=dummy-key
```

**taskdefinitions.json** (`php`コンテナの`secrets`配列):
```json
{
  "containerDefinitions": [
    {
      "name": "php",
      "secrets": [
        // ... 既存のsecrets
        {
          "name": "MOMENTO_API_KEY",
          "valueFrom": "<SECRET>:glow-momento-api-key::"
        },
        {
          "name": "NEW_EXTERNAL_API_KEY",
          "valueFrom": "<SECRET>:new-external-api-key::"
        }
      ]
    }
  ]
}
```

**ポイント**:
- `valueFrom`は`<SECRET>:シークレットキー名::`形式
- シークレットキー名はケバブケース（小文字＋ハイフン）

### 実装例2: データベース認証情報の追加

**admin/.env.example**:
```bash
NEW_DB_USERNAME=admin
NEW_DB_PASSWORD=dummy
```

**taskdefinitions-admin.json**:
```json
{
  "containerDefinitions": [
    {
      "name": "php-admin",
      "secrets": [
        // ... 既存のsecrets
        {
          "name": "NEW_DB_USERNAME",
          "valueFrom": "<SECRET>:new-db-user::"
        },
        {
          "name": "NEW_DB_PASSWORD",
          "valueFrom": "<SECRET>:new-db-pass::"
        }
      ]
    }
  ]
}
```

### シークレットキー名の命名規則

| 環境変数名 | シークレットキー名 |
|---|---|
| `NEW_EXTERNAL_API_KEY` | `new-external-api-key` |
| `NEW_DB_PASSWORD` | `new-db-pass` |
| `THIRD_PARTY_TOKEN` | `third-party-token` |
| `AWS_SECRET_ACCESS_KEY` | `aws-secret-access-key` |

**ルール**:
- 全て小文字
- アンダースコア → ハイフン
- `PASSWORD` → `pass`, `USERNAME` → `user` のように省略形を使用（既存パターンに合わせる）

## パターン3: 既存環境変数の変更

### ケース1: プレースホルダーパターンの変更

**変更前**:
```json
{
  "name": "AWS_BUCKET",
  "value": "<PJ_NAME>-<INFRA_ENV>-master"
}
```

**変更後** (バケット名規則変更):
```json
{
  "name": "AWS_BUCKET",
  "value": "<PJ_NAME>-<INFRA_ENV>-v2-master"
}
```

### ケース2: environment → secrets への移行

セキュリティ強化のため、機密性の高い値をsecretsに移行：

**変更前** (environment配列):
```json
{
  "name": "EXTERNAL_API_ENDPOINT",
  "value": "https://api.example.com"
}
```

**変更後** (secrets配列に移動):
```json
// environment配列から削除
// secrets配列に追加
{
  "name": "EXTERNAL_API_ENDPOINT",
  "valueFrom": "<SECRET>:external-api-endpoint::"
}
```

## パターン4: 環境変数の削除

### 削除手順

1. **コード内で使用されていないことを確認**:
   ```bash
   # api/admin配下で環境変数が使用されていないか検索
   grep -r "env('OLD_VAR_NAME')" api/
   grep -r "getenv('OLD_VAR_NAME')" api/
   ```

2. **taskdefinitions.jsonから削除**:
   - environment配列またはsecrets配列から該当エントリを削除

3. **.env.exampleから削除**:
   - api/.env.exampleまたはadmin/.env.exampleから削除

### 削除例

**変更前**:
```json
{
  "containerDefinitions": [
    {
      "name": "php",
      "environment": [
        {
          "name": "DEPRECATED_FEATURE_FLAG",
          "value": "false"
        }
      ]
    }
  ]
}
```

**変更後**:
```json
{
  "containerDefinitions": [
    {
      "name": "php",
      "environment": [
        // DEPRECATED_FEATURE_FLAG を削除
      ]
    }
  ]
}
```

## 実装チェックリスト

### 環境変数追加時

- [ ] api/.env.exampleまたはadmin/.env.exampleに追加されているか
- [ ] environment/secretsの判断は適切か
- [ ] プレースホルダー（`<PJ_NAME>`, `<INFRA_ENV>`等）を使用しているか
- [ ] シークレットキー名は既存の命名規則に従っているか
- [ ] 関連する環境変数の近くに配置したか
- [ ] API/Adminの区別は正しいか
- [ ] 該当するコンテナ（php/php-admin）に追加したか

### environment配列への追加時

```json
// ✅ 正しい例
{
  "name": "NEW_LAMBDA_FUNCTION_NAME",
  "value": "<PJ_NAME>-<INFRA_ENV>-new-lambda"
}

// ❌ 間違った例
{
  "name": "NEW_LAMBDA_FUNCTION_NAME",
  "value": "glow-dev-new-lambda"  // 環境固有の値
}
```

### secrets配列への追加時

```json
// ✅ 正しい例
{
  "name": "NEW_API_KEY",
  "valueFrom": "<SECRET>:new-api-key::"
}

// ❌ 間違った例
{
  "name": "NEW_API_KEY",
  "valueFrom": "arn:aws:secretsmanager:..."  // ARN直接記述
}

// ❌ 間違った例
{
  "name": "NEW_API_KEY",
  "value": "dummy-key"  // secretsにvalueは使えない
}
```

### JSON構文確認

```bash
# 構文チェック
jq . codebuild/artifacts/taskdefinitions.json
jq . codebuild/artifacts/taskdefinitions-admin.json
```

## 複数環境変数の一括追加例

### ケース: 新しい外部サービス連携

**api/.env.example**:
```bash
NEW_SERVICE_API_URL=https://api.newservice.com
NEW_SERVICE_API_KEY=dummy-key
NEW_SERVICE_TIMEOUT=30
```

**taskdefinitions.json**:
```json
{
  "containerDefinitions": [
    {
      "name": "php",
      "environment": [
        // ... 既存の環境変数
        {
          "name": "NEW_SERVICE_API_URL",
          "value": "https://api.newservice.com"
        },
        {
          "name": "NEW_SERVICE_TIMEOUT",
          "value": "30"
        }
      ],
      "secrets": [
        // ... 既存のsecrets
        {
          "name": "NEW_SERVICE_API_KEY",
          "valueFrom": "<SECRET>:new-service-api-key::"
        }
      ]
    }
  ]
}
```

**ポイント**:
- 関連する環境変数はグループ化して配置
- URLやタイムアウトはenvironment、APIキーはsecrets

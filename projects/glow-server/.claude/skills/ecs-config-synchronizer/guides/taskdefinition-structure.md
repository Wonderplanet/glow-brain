# Task Definition構造ガイド

ECS Task Definitionの構造と、各セクションの役割を解説します。

## 目次

1. [ファイル概要](#ファイル概要)
2. [containerDefinitions](#containerdefinitions)
3. [environment vs secrets](#environment-vs-secrets)
4. [タスクレベル設定](#タスクレベル設定)
5. [プレースホルダー一覧](#プレースホルダー一覧)

## ファイル概要

### 対象ファイル

```
codebuild/artifacts/
├── taskdefinitions.json        # API用タスク定義
└── taskdefinitions-admin.json  # Admin用タスク定義
```

### 基本構造

```json
{
  "containerDefinitions": [/* コンテナ定義の配列 */],
  "family": "<PJ_NAME>-<INFRA_ENV>-api-def",
  "taskRoleArn": "arn:aws:iam::<AWS_ACCOUNT_ID>:role/...",
  "executionRoleArn": "arn:aws:iam::<AWS_ACCOUNT_ID>:role/...",
  "networkMode": "awsvpc",
  "volumes": [/* ボリューム定義 */],
  "requiresCompatibilities": ["EC2"],
  "cpu": "8192",
  "memory": "16384",
  "runtimePlatform": {
    "cpuArchitecture": "ARM64",
    "operatingSystemFamily": "LINUX"
  }
}
```

## containerDefinitions

### API用コンテナ構成

`taskdefinitions.json`には3つのコンテナ：

```json
"containerDefinitions": [
  {
    "name": "php",              // PHPアプリケーション
    "image": "<PHP_IMAGE>",
    // ...
  },
  {
    "name": "nginx",            // Webサーバー
    "image": "<NGINX_IMAGE>",
    // ...
  },
  {
    "name": "datadog-agent",    // APM監視
    "image": "<DATADOG_IMAGE>",
    // ...
  }
]
```

### Admin用コンテナ構成

`taskdefinitions-admin.json`には2つのコンテナ：

```json
"containerDefinitions": [
  {
    "name": "php-admin",        // PHP管理ツール
    "image": "<PHP_IMAGE>",
    // ...
  },
  {
    "name": "nginx-admin",      // Webサーバー
    "image": "<NGINX_IMAGE>",
    // ...
  }
]
```

### コンテナ定義の主要フィールド

```json
{
  "name": "php",
  "image": "<PHP_IMAGE>",       // ビルド時に実際のECRイメージURLに置換
  "cpu": 0,                     // 0 = タスクレベルCPUを共有
  "portMappings": [             // ポートマッピング
    {
      "name": "php-9000-tcp",
      "containerPort": 9000,
      "hostPort": 9000,
      "protocol": "tcp",
      "appProtocol": "http"
    }
  ],
  "essential": true,            // このコンテナが停止したらタスク全体を停止
  "environment": [/* 環境変数 */],
  "secrets": [/* 機密情報 */],
  "mountPoints": [/* ボリュームマウント */],
  "dependsOn": [/* 依存関係 */],
  "ulimits": [/* リソース制限 */],
  "logConfiguration": {/* CloudWatch Logs設定 */},
  "healthCheck": {/* ヘルスチェック設定 */}
}
```

## environment vs secrets

### environment（プレーンテキスト）

**用途**: 機密性のない設定値

**例（API）**:
```json
"environment": [
  {
    "name": "APP_ENV",
    "value": "<INFRA_ENV>"      // プレースホルダー使用
  },
  {
    "name": "TIDB_PORT",
    "value": "4000"             // 固定値
  },
  {
    "name": "MASTER_DB_HOST",
    "value": "db.<INFRA_ENV>.<PJ_NAME>.internal"
  }
]
```

**例（Admin）**:
```json
"environment": [
  {
    "name": "AWS_BUCKET",
    "value": "<PJ_NAME>-<INFRA_ENV>-master"
  },
  {
    "name": "MAINTENANCE_LAMBDA_FUNCTION_NAME",
    "value": "<PJ_NAME>-<INFRA_ENV>-maintenance-change-route"
  }
]
```

### secrets（AWS Secrets Manager）

**用途**: 機密情報（パスワード、APIキー等）

**フォーマット**:
```json
"secrets": [
  {
    "name": "環境変数名",
    "valueFrom": "<SECRET>:シークレットキー::"
  }
]
```

**例（API）**:
```json
"secrets": [
  {
    "name": "TIDB_HOST",
    "valueFrom": "<SECRET>:glow-tidb-host::"
  },
  {
    "name": "TIDB_PASSWORD",
    "valueFrom": "<SECRET>:glow-tidb-api-pass::"
  },
  {
    "name": "MOMENTO_API_KEY",
    "valueFrom": "<SECRET>:glow-momento-api-key::"
  }
]
```

**例（Admin）**:
```json
"secrets": [
  {
    "name": "ADMIN_DB_PASSWORD",
    "valueFrom": "<SECRET>:glow-aurora-admin-pass::"
  },
  {
    "name": "AWS_ACCESS_KEY_ID",
    "valueFrom": "<SECRET>:aws-access-key::"
  },
  {
    "name": "SHEET_API_CREDENTIALS",
    "valueFrom": "<SECRET>:sheet-api-credentials::"
  }
]
```

### 判断基準

| 環境変数名パターン | 配置先 | 例 |
|---|---|---|
| `*_PASSWORD`, `*_PASS` | secrets | `TIDB_PASSWORD` |
| `*_API_KEY`, `*_KEY` | secrets | `MOMENTO_API_KEY` |
| `*_TOKEN` | secrets | `GIT_USER_TOKEN` |
| `*_SECRET` | secrets | `CLIENT_SECRET` |
| `*_CREDENTIAL*` | secrets | `SHEET_API_CREDENTIALS` |
| `*_HOST` | secrets | `TIDB_HOST` (例外) |
| `*_PORT` | environment | `TIDB_PORT` |
| `*_DATABASE` | environment | `TIDB_DATABASE` |
| `AWS_*_BUCKET` | environment | `AWS_BUCKET` |
| `*_LAMBDA_*` | environment | `MAINTENANCE_LAMBDA_FUNCTION_NAME` |
| `APP_ENV` | environment | `APP_ENV` |

**注意**: `*_HOST`は通常secretsに配置されます（インフラ情報の保護のため）

## タスクレベル設定

### CPU/メモリ設定

**API**:
```json
"cpu": "8192",      // 8 vCPU
"memory": "16384"   // 16 GB
```

**Admin**:
```json
"cpu": "2048",      // 2 vCPU
"memory": "4096"    // 4 GB
```

### ボリューム設定

**API**:
```json
"volumes": [
  {
    "name": "sharesock",        // PHP-FPMソケット共有
    "host": {}
  },
  {
    "name": "datadog-socket"    // DataDogソケット共有
  }
]
```

**Admin**:
```json
"volumes": [
  {
    "name": "glow_client_asset"  // クライアントアセット共有
  }
]
```

### IAMロール

```json
"taskRoleArn": "arn:aws:iam::<AWS_ACCOUNT_ID>:role/<PJ_NAME2>EcsTaskRole",
"executionRoleArn": "arn:aws:iam::<AWS_ACCOUNT_ID>:role/<PJ_NAME2>EcsTaskExecutionRole"
```

- `taskRoleArn`: タスク実行時のAWS APIアクセス権限
- `executionRoleArn`: ECSエージェントがイメージpullやログ出力に使用

## プレースホルダー一覧

buildspec.ymlでビルド時に置換されるプレースホルダー：

| プレースホルダー | 説明 | 置換例 |
|---|---|---|
| `<PHP_IMAGE>` | PHPコンテナイメージURL | `123456.dkr.ecr.ap-northeast-1.amazonaws.com/glow-dev-api-php:abc1234` |
| `<NGINX_IMAGE>` | NginxコンテナイメージURL | `123456.dkr.ecr.ap-northeast-1.amazonaws.com/glow-dev-api-nginx:abc1234` |
| `<DATADOG_IMAGE>` | DataDogコンテナイメージURL | `123456.dkr.ecr.ap-northeast-1.amazonaws.com/glow-dev-api-datadog:abc1234` |
| `<SECRET>` | Secrets Manager ARN | `arn:aws:secretsmanager:ap-northeast-1:123456:secret:glow-dev-secrets` |
| `<INFRA_ENV>` | インフラ環境名 | `dev`, `stg`, `prod` |
| `<PJ_NAME>` | プロジェクト名（小文字） | `glow` |
| `<PJ_NAME2>` | プロジェクト名（大文字） | `GLOW` |
| `<AWS_DEFAULT_REGION>` | AWSリージョン | `ap-northeast-1` |
| `<AWS_ACCOUNT_ID>` | AWSアカウントID | `123456789012` |

### 使用例

```json
// ❌ 間違い: 実際の値を直接記述
"value": "glow-dev-master"

// ✅ 正しい: プレースホルダーを使用
"value": "<PJ_NAME>-<INFRA_ENV>-master"
```

## 実装時の注意点

### ✅ 正しい実装

```json
// 新しい環境変数を追加する場合
{
  "name": "NEW_LAMBDA_FUNCTION_NAME",
  "value": "<PJ_NAME>-<INFRA_ENV>-new-lambda"  // プレースホルダー使用
}

// 新しいSecretsを追加する場合
{
  "name": "NEW_API_KEY",
  "valueFrom": "<SECRET>:new-api-key::"  // プレースホルダー使用
}
```

### ❌ 間違った実装

```json
// 環境固有の値を直接記述
{
  "name": "NEW_LAMBDA_FUNCTION_NAME",
  "value": "glow-dev-new-lambda"  // ❌ dev固定は不可
}

// Secrets Manager ARNを直接記述
{
  "name": "NEW_API_KEY",
  "valueFrom": "arn:aws:secretsmanager:ap-northeast-1:123456:secret:..."  // ❌
}
```

### 配列の順序

- `environment`配列: アルファベット順は不要だが、関連する変数は近くに配置
- `secrets`配列: 同様に関連変数を近くに配置

### コンテナ間依存

```json
"dependsOn": [
  {
    "containerName": "php",
    "condition": "HEALTHY"  // または "START"
  }
]
```

- `HEALTHY`: ヘルスチェック成功まで待機
- `START`: 起動開始のみ待機

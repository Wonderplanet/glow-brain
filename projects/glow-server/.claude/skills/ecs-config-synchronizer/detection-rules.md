# 変更検出ルール

api/adminディレクトリの変更を検出し、ECS設定ファイルへの反映が必要かを判断するルールです。

## 目次

1. [環境変数の変更検出](#環境変数の変更検出)
2. [Dockerfileの変更検出](#dockerfileの変更検出)
3. [リソース要件の変更検出](#リソース要件の変更検出)
4. [検出トリガーのチェックリスト](#検出トリガーのチェックリスト)

## 環境変数の変更検出

### 検出対象ファイル

以下のファイルでの環境変数変更を検出：

```
api/.env
api/.env.example
admin/.env
admin/.env.example
```

### 検出パターン

#### 1. 新しい環境変数の追加

**検出条件**:
- `.env.example`に新しい環境変数が追加された
- コード内で新しい`env()`や`getenv()`呼び出しが追加された

**判断基準**:
```php
// ✅ 検出すべきパターン
env('NEW_AWS_BUCKET')           // 新しいAWS関連設定
env('NEW_DB_CONNECTION')        // 新しいDB接続設定
env('NEW_EXTERNAL_API_URL')     // 新しい外部API設定
```

#### 2. 既存環境変数の変更

**検出条件**:
- 環境変数の型が変更された（boolean → string等）
- 環境変数のデフォルト値が大きく変更された

#### 3. 機密情報かどうかの判断

**Secrets Manager（secrets）に配置すべき環境変数**:
```
# ✅ secretsに配置すべき
- パスワード: *_PASSWORD, *_PASS
- APIキー: *_API_KEY, *_KEY
- トークン: *_TOKEN
- 認証情報: *_SECRET, *_CREDENTIAL
- 証明書: *_CERT, *_SSL_CA
```

**環境変数（environment）に配置すべき環境変数**:
```
# ✅ environmentに配置すべき
- ホスト名: *_HOST
- ポート番号: *_PORT
- データベース名: *_DATABASE
- リージョン: AWS_DEFAULT_REGION
- バケット名: AWS_*_BUCKET
- 環境名: APP_ENV, INFRA_ENV
```

### 実装例

```bash
# 環境変数の差分検出
git diff api/.env.example

# 新規追加された環境変数例
+ NEW_LAMBDA_FUNCTION_NAME=example-lambda
+ NEW_AWS_DYNAMODB_TABLE=example-table
+ NEW_API_ENDPOINT_URL=https://api.example.com
```

## Dockerfileの変更検出

### 検出対象ファイル

```
docker/envs/ecs/php/Dockerfile
docker/envs/ecs/php-admin/Dockerfile
docker/envs/ecs/nginx/Dockerfile
docker/envs/ecs/nginx-admin/Dockerfile
docker/envs/ecs/datadog/Dockerfile
```

### 検出パターン

#### 1. ビルド引数（ARG）の追加・変更

**検出条件**:
```dockerfile
# ✅ 検出すべき変更
ARG NEW_BUILD_ARG           # 新しいビルド引数追加
ARG MOMENTO_API_KEY         # 既存の引数変更
```

**反映先**: `buildspec.yml`または`buildspec-admin.yml`の`build`フェーズ

#### 2. ベースイメージの変更

**検出条件**:
```dockerfile
# ✅ 検出すべき変更
FROM php:8.2-fpm → FROM php:8.3-fpm
```

**影響**: 通常はbuildspecへの反映不要だが、破壊的変更の可能性があるため警告

#### 3. EXPOSE ポートの変更

**検出条件**:
```dockerfile
# ✅ 検出すべき変更
EXPOSE 9000 → EXPOSE 9001
```

**反映先**: `taskdefinitions.json`の`portMappings`

## リソース要件の変更検出

### 検出対象

アプリケーションの要件変更により、ECSタスクのCPU/メモリ調整が必要な場合：

#### 検出条件

1. **パフォーマンス問題の報告**
   - アプリケーションログでOOMエラー
   - CPU使用率が常時80%超

2. **新機能の追加**
   - メモリを大量消費する処理追加（画像処理、大量データ処理等）
   - バックグラウンドジョブの追加

3. **依存ライブラリの追加**
   - `composer.json`に大規模ライブラリ追加
   - `package.json`にビルドツール追加

#### 現在のリソース設定

```json
// taskdefinitions.json (API)
"cpu": "8192",     // 8 vCPU
"memory": "16384"  // 16 GB

// taskdefinitions-admin.json (Admin)
"cpu": "2048",     // 2 vCPU
"memory": "4096"   // 4 GB
```

## 検出トリガーのチェックリスト

スキル実行前に以下を確認：

### 環境変数関連
- [ ] `.env.example`に新しい変数が追加された
- [ ] コード内で新しい`env()`呼び出しがある
- [ ] 既存環境変数の用途が変更された

### Dockerfile関連
- [ ] `docker/envs/ecs/`配下のDockerfileが変更された
- [ ] 新しい`ARG`が追加された
- [ ] ビルドプロセスが変更された

### リソース関連
- [ ] メモリを大量消費する新機能を追加した
- [ ] パフォーマンス問題が報告されている
- [ ] 大規模ライブラリを追加した

### その他
- [ ] 新しいコンテナを追加する必要がある
- [ ] コンテナ間の依存関係が変更された
- [ ] ヘルスチェック設定を変更する必要がある

## 注意事項

### ❌ 検出しなくてよい変更

以下の変更はECS設定への反映が不要：

1. **ローカル開発専用の設定**
   ```
   NGINX_PORT=8080              # ローカルDocker Composeのみ
   REDIS_PORT=6379              # ローカル専用
   ```

2. **git merge-driver保護対象ファイル**
   ```
   .env
   .mcp.json
   docker-compose.yml
   ```

3. **テストコード内の環境変数**
   ```php
   // tests/ 配下のenv()呼び出しは検出不要
   ```

### ✅ 必ず確認すべき点

1. **プレースホルダーの維持**
   - taskdefinitions.jsonでは`<INFRA_ENV>`等のプレースホルダーを維持
   - 実際の値ではなくプレースホルダーで記述

2. **API/Admin の区別**
   - API用: `taskdefinitions.json`, `buildspec.yml`
   - Admin用: `taskdefinitions-admin.json`, `buildspec-admin.yml`

3. **変更の影響範囲**
   - 環境変数追加 → taskdefinitionsのみ
   - Dockerfile ARG → buildspec + taskdefinitions
   - ポート変更 → taskdefinitions + buildspec (health check等)

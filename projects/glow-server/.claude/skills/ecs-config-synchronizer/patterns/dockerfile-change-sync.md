# Dockerfile変更同期パターン

docker/envs/ecs配下のDockerfile変更をbuildspec.ymlとtaskdefinitions.jsonに反映する実装パターンです。

## 目次

1. [Dockerfile変更のワークフロー](#dockerfile変更のワークフロー)
2. [パターン1: ビルド引数（ARG）の追加](#パターン1-ビルド引数argの追加)
3. [パターン2: ポート番号の変更](#パターン2-ポート番号の変更)
4. [パターン3: ベースイメージの変更](#パターン3-ベースイメージの変更)
5. [パターン4: 新しいコンテナの追加](#パターン4-新しいコンテナの追加)
6. [実装チェックリスト](#実装チェックリスト)

## Dockerfile変更のワークフロー

### ステップ1: 変更内容の特定

```bash
# Dockerfileの差分確認
git diff docker/envs/ecs/php/Dockerfile
git diff docker/envs/ecs/php-admin/Dockerfile
git diff docker/envs/ecs/nginx/Dockerfile
git diff docker/envs/ecs/nginx-admin/Dockerfile
```

### ステップ2: 影響範囲の判断

| 変更内容 | buildspec.yml | taskdefinitions.json |
|---|---|---|
| `ARG`追加 | ✅ 必要 | ❌ 不要 |
| `EXPOSE`変更 | ❌ 不要 | ✅ 必要 |
| `FROM`変更 | ❌ 通常不要 | ❌ 不要（警告のみ） |
| 新コンテナ追加 | ✅ 必要 | ✅ 必要 |

### ステップ3: API/Adminの区別

- **API Dockerfile変更**: `buildspec.yml`, `taskdefinitions.json`
- **Admin Dockerfile変更**: `buildspec-admin.yml`, `taskdefinitions-admin.json`

## パターン1: ビルド引数（ARG）の追加

### 使用ケース

Dockerビルド時に外部から値を渡す必要がある場合：
- APIキー、トークン
- ビルド時設定
- Git認証情報

### 実装例1: 新しいAPIキーをビルド引数として追加

**Dockerfile (docker/envs/ecs/php/Dockerfile)**:
```dockerfile
ARG MOMENTO_API_KEY
ARG NEW_BUILD_TIME_API_KEY

RUN composer install --no-dev --optimize-autoloader
RUN some-setup-command --api-key=${NEW_BUILD_TIME_API_KEY}
```

**buildspec.yml**:

**1. env.secrets-managerに追加**:
```yaml
env:
  secrets-manager:
    DB_HOST: glow-${INFRA_ENV}-secrets:glow-tidb-host
    MOMENTO_API_KEY: glow-${INFRA_ENV}-secrets:glow-momento-api-key
    NEW_BUILD_TIME_API_KEY: glow-${INFRA_ENV}-secrets:new-build-time-api-key
```

**2. buildフェーズに--build-arg追加**:
```yaml
phases:
  build:
    commands:
      - |
        docker build \
          --build-arg MOMENTO_API_KEY="${MOMENTO_API_KEY}" \
          --build-arg NEW_BUILD_TIME_API_KEY="${NEW_BUILD_TIME_API_KEY}" \
          --build-arg BUILDKIT_INLINE_CACHE=1 \
          -f docker/envs/ecs/php/Dockerfile \
          -t ${PHP_REPOSITORY}:latest \
        .
```

### 実装例2: Admin用Git認証情報の変更

**Dockerfile (docker/envs/ecs/php-admin/Dockerfile)**:
```dockerfile
ARG GIT_USER
ARG GIT_TOKEN
ARG GIT_SSH_KEY
ARG NEW_GIT_DEPLOY_KEY

RUN git config --global credential.helper store
RUN echo "https://${GIT_USER}:${GIT_TOKEN}@github.com" > ~/.git-credentials
```

**buildspec-admin.yml**:

**1. env.parameter-storeに追加**:
```yaml
env:
  parameter-store:
    GIT_USER_ID: git-user-id
    GIT_USER_TOKEN: git-user-token
    NEW_GIT_DEPLOY_KEY: new-git-deploy-key
```

**2. buildフェーズに--build-arg追加**:
```yaml
phases:
  build:
    commands:
      - |
        docker build \
          --build-arg GIT_USER="${GIT_USER_ID}" \
          --build-arg GIT_TOKEN="${GIT_USER_TOKEN}" \
          --build-arg NEW_GIT_DEPLOY_KEY="${NEW_GIT_DEPLOY_KEY}" \
          -f docker/envs/ecs/php-admin/Dockerfile \
        .
```

## パターン2: ポート番号の変更

### 使用ケース

コンテナのポート設定を変更する場合：
- PHP-FPMポート変更
- Nginxポート変更

### 実装例: PHP-FPMポートを9000→9001に変更

**Dockerfile (docker/envs/ecs/php/Dockerfile)**:
```dockerfile
# php-fpm設定ファイルで変更
RUN sed -i 's/listen = 9000/listen = 9001/g' /usr/local/etc/php-fpm.d/www.conf
EXPOSE 9001
```

**taskdefinitions.json** (`php`コンテナ):
```json
{
  "containerDefinitions": [
    {
      "name": "php",
      "portMappings": [
        {
          "name": "php-9001-tcp",
          "containerPort": 9001,
          "hostPort": 9001,
          "protocol": "tcp",
          "appProtocol": "http"
        }
      ]
    }
  ]
}
```

**注意**:
- Nginxの設定ファイルも合わせて変更が必要（`fastcgi_pass php:9001;`）
- 既存環境への影響が大きいため、通常は変更しない

## パターン3: ベースイメージの変更

### 使用ケース

PHPやNginxのバージョンアップ時：

**Dockerfile変更例**:
```dockerfile
# 変更前
FROM php:8.2-fpm-alpine

# 変更後
FROM php:8.3-fpm-alpine
```

### 対応方法

**buildspec.ymlへの影響**: 通常なし

**taskdefinitions.jsonへの影響**: なし

**注意事項**:
- ビルドは自動的に新しいイメージを使用
- 破壊的変更の可能性があるため、必ずローカルで動作確認
- PHP拡張のインストールコマンドに変更が必要な場合あり

### 確認ポイント

```bash
# ローカルでビルドテスト
docker build -f docker/envs/ecs/php/Dockerfile -t test-php .
docker run -it test-php php -v
docker run -it test-php php -m  # 拡張モジュール確認
```

## パターン4: 新しいコンテナの追加

### 使用ケース

新しいサービスコンテナを追加する場合：
- Redis
- Elasticsearch
- 別のマイクロサービス

### 実装例: Redisコンテナの追加（架空）

**1. Dockerfileを作成**:
```bash
docker/envs/ecs/redis/Dockerfile
```

**2. buildspec.ymlに追加**:

**pre_buildフェーズ**:
```yaml
phases:
  pre_build:
    commands:
      # 既存のリポジトリ定義の後に追加
      - REDIS_REPOSITORY=${AWS_ACCOUNT_ID}.dkr.ecr.${AWS_DEFAULT_REGION}.amazonaws.com/${PJ_NAME}-${INFRA_ENV}-api-redis
      - docker pull ${REDIS_REPOSITORY}:latest-cache || true
```

**buildフェーズ**:
```yaml
phases:
  build:
    commands:
      # 他のビルドコマンドの後に追加
      - |
        docker build \
          -f docker/envs/ecs/redis/Dockerfile \
          -t ${REDIS_REPOSITORY}:latest \
          -t ${REDIS_REPOSITORY}:latest-cache \
          -t ${REDIS_REPOSITORY}:${COMMIT_HASH} \
        .
```

**post_buildフェーズ**:
```yaml
phases:
  post_build:
    commands:
      # イメージpush
      - docker push ${REDIS_REPOSITORY}:latest
      - docker push ${REDIS_REPOSITORY}:${COMMIT_HASH}

      # プレースホルダー置換に追加
      - |
        sed -i \
          -e "s;<REDIS_IMAGE>;${REDIS_REPOSITORY}:${COMMIT_HASH};g" \
          codebuild/artifacts/taskdefinitions.json
```

**3. taskdefinitions.jsonに追加**:

```json
{
  "containerDefinitions": [
    // 既存のphp, nginx, datadog-agent
    {
      "name": "redis",
      "image": "<REDIS_IMAGE>",
      "cpu": 0,
      "portMappings": [
        {
          "name": "redis-6379-tcp",
          "containerPort": 6379,
          "hostPort": 6379,
          "protocol": "tcp"
        }
      ],
      "essential": false,
      "environment": [],
      "logConfiguration": {
        "logDriver": "awslogs",
        "options": {
          "awslogs-create-group": "true",
          "awslogs-group": "/ecs/<PJ_NAME>-<INFRA_ENV>-app-task",
          "awslogs-region": "<AWS_DEFAULT_REGION>",
          "awslogs-stream-prefix": "redis"
        }
      }
    }
  ]
}
```

**4. imagedefinitions.jsonに追加**:

```json
[
  {
    "name": "php",
    "imageUri": "<PHP_IMAGE>"
  },
  {
    "name": "nginx",
    "imageUri": "<NGINX_IMAGE>"
  },
  {
    "name": "redis",
    "imageUri": "<REDIS_IMAGE>"
  }
]
```

## マイグレーション実行時の環境変数

### ケース: マイグレーション用の新しい環境変数

**背景**: buildspecのpost_buildフェーズでマイグレーション実行時に新しい環境変数が必要

**例**:
```yaml
phases:
  post_build:
    commands:
      - |
        docker run \
          -e TIDB_HOST=${DB_HOST} \
          -e TIDB_PASSWORD=${DB_PASSWORD} \
          -e NEW_MIGRATION_ENV_VAR=${NEW_MIGRATION_ENV_VAR} \
          -dit --init --name php ${PHP_REPOSITORY}:latest
      - docker exec php bash -c "php /var/www/api/artisan migrate"
```

**注意**:
- マイグレーション用環境変数もenv.secrets-managerから取得
- タスク定義の環境変数とは別に、マイグレーション実行時のみ必要な場合もある

## 実装チェックリスト

### ビルド引数追加時

- [ ] Dockerfileに`ARG`を追加したか
- [ ] buildspec.ymlの`env`セクションに変数定義を追加したか（secrets-manager or parameter-store）
- [ ] buildspec.ymlの`build`フェーズに`--build-arg`を追加したか
- [ ] 機密情報はsecrets-manager、非機密情報はparameter-storeに配置したか
- [ ] API/Adminの区別は正しいか

### ポート変更時

- [ ] Dockerfileの`EXPOSE`を変更したか
- [ ] taskdefinitions.jsonの`portMappings`を変更したか
- [ ] コンテナ名に合わせたポート名（`php-9001-tcp`等）を使用したか
- [ ] 依存するコンテナ（nginx等）の設定も変更したか

### 新しいコンテナ追加時

- [ ] Dockerfileを作成したか
- [ ] buildspec.ymlのpre_buildにリポジトリ定義を追加したか
- [ ] buildspec.ymlのbuildにビルドコマンドを追加したか
- [ ] buildspec.ymlのpost_buildにpushコマンドを追加したか
- [ ] buildspec.ymlのpost_buildにプレースホルダー置換を追加したか
- [ ] taskdefinitions.jsonにコンテナ定義を追加したか
- [ ] imagedefinitions.jsonにイメージ定義を追加したか
- [ ] 必要に応じてコンテナ間依存関係（`dependsOn`）を設定したか

### ベースイメージ変更時

- [ ] ローカルでビルドテストを実施したか
- [ ] PHP拡張のインストールコマンドに問題ないか確認したか
- [ ] 破壊的変更がないか確認したか

## 実装例のまとめ

### ✅ 正しい実装例

**Dockerfile**:
```dockerfile
ARG NEW_API_KEY
RUN setup-command --key=${NEW_API_KEY}
```

**buildspec.yml**:
```yaml
env:
  secrets-manager:
    NEW_API_KEY: glow-${INFRA_ENV}-secrets:new-api-key

phases:
  build:
    commands:
      - |
        docker build \
          --build-arg NEW_API_KEY="${NEW_API_KEY}" \
          -f docker/envs/ecs/php/Dockerfile \
        .
```

### ❌ 間違った実装例

**buildspec.yml**:
```yaml
# ❌ env定義なしでビルド引数を渡そうとする
phases:
  build:
    commands:
      - |
        docker build \
          --build-arg NEW_API_KEY="hardcoded-value" \
        .
```

```yaml
# ❌ ビルド引数を渡していない
env:
  secrets-manager:
    NEW_API_KEY: glow-${INFRA_ENV}-secrets:new-api-key

phases:
  build:
    commands:
      - |
        docker build \
          # --build-arg NEW_API_KEY="${NEW_API_KEY}" が欠落
          -f docker/envs/ecs/php/Dockerfile \
        .
```

## デバッグ方法

### buildspecのテスト

```bash
# ローカルで環境変数を設定してビルドテスト
export NEW_API_KEY="test-key"
docker build \
  --build-arg NEW_API_KEY="${NEW_API_KEY}" \
  -f docker/envs/ecs/php/Dockerfile \
  -t test-build \
  .
```

### Task Definitionの検証

```bash
# JSON構文チェック
jq . codebuild/artifacts/taskdefinitions.json

# プレースホルダーが残っているか確認
grep "<.*>" codebuild/artifacts/taskdefinitions.json
```

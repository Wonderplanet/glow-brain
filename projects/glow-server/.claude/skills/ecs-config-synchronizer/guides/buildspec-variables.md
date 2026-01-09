# BuildSpec変数とプレースホルダー

AWS CodeBuildのbuildspec.ymlファイルの構造と、変数管理方法を解説します。

## 目次

1. [ファイル概要](#ファイル概要)
2. [環境変数の取得方法](#環境変数の取得方法)
3. [ビルドフェーズ](#ビルドフェーズ)
4. [プレースホルダー置換](#プレースホルダー置換)
5. [Dockerfile ARGの渡し方](#dockerfile-argの渡し方)

## ファイル概要

### 対象ファイル

```
codebuild/
├── buildspec.yml        # API用ビルド定義
├── buildspec-admin.yml  # Admin用ビルド定義
└── buildspec2.yml       # (未調査)
```

### 基本構造

```yaml
version: 0.2
env:
  variables:          # 環境変数（プレーンテキスト）
  parameter-store:    # AWS Systems Manager Parameter Store
  secrets-manager:    # AWS Secrets Manager
phases:
  pre_build:         # ビルド前処理
  build:             # ビルド処理
  post_build:        # ビルド後処理
artifacts:
  files:             # 成果物ファイル
```

## 環境変数の取得方法

### 1. variables（プレーンテキスト）

**用途**: ビルドプロセス内でのみ使用する非機密情報

```yaml
env:
  variables:
    DOCKER_BUILDKIT: 1
```

### 2. parameter-store（SSM Parameter Store）

**用途**: 複数環境で共有する設定値

```yaml
env:
  parameter-store:
    DOCKER_USER: dockerhub-user
    DOCKER_TOKEN: dockerhub-token
    AWS_ACCOUNT_ID: account-id
    DB_PASS: db-pass
```

### 3. secrets-manager（Secrets Manager）

**用途**: 環境別の機密情報、動的に環境を切り替える

**API (`buildspec.yml`)**:
```yaml
env:
  secrets-manager:
    DB_HOST: glow-${INFRA_ENV}-secrets:glow-tidb-host
    DB_USERNAME: glow-${INFRA_ENV}-secrets:glow-tidb-user
    DB_PASSWORD: glow-${INFRA_ENV}-secrets:glow-tidb-pass
    MASTER_DB_USERNAME: glow-${INFRA_ENV}-secrets:db-root-user
    MASTER_DB_PASSWORD: glow-${INFRA_ENV}-secrets:db-root-password
    MOMENTO_API_KEY: glow-${INFRA_ENV}-secrets:glow-momento-api-key
```

**Admin (`buildspec-admin.yml`)**:
```yaml
env:
  parameter-store:
    GIT_USER_ID: git-user-id
    GIT_USER_TOKEN: git-user-token
    GIT_SSH_KEY: git-ssh-key
  secrets-manager:
    DB_USERNAME: glow-${INFRA_ENV}-secrets:db-root-user
    DB_PASSWORD: glow-${INFRA_ENV}-secrets:db-root-password
    MOMENTO_API_KEY: glow-${INFRA_ENV}-secrets:glow-momento-api-key
```

**ポイント**:
- `${INFRA_ENV}`はCodeBuildプロジェクトで定義される環境変数
- Secrets Managerのキー名は`secret-name:json-key::`形式

## ビルドフェーズ

### pre_build（ビルド前処理）

**目的**: ECRログイン、Dockerイメージのpull

```yaml
phases:
  pre_build:
    commands:
      # ECRログイン
      - aws ecr get-login-password --region $AWS_DEFAULT_REGION | docker login --username AWS --password-stdin ${AWS_ACCOUNT_ID}.dkr.ecr.${AWS_DEFAULT_REGION}.amazonaws.com

      # ECRリポジトリURLを変数化
      - PHP_REPOSITORY=${AWS_ACCOUNT_ID}.dkr.ecr.${AWS_DEFAULT_REGION}.amazonaws.com/${PJ_NAME}-${INFRA_ENV}-api-php
      - NGINX_REPOSITORY=${AWS_ACCOUNT_ID}.dkr.ecr.${AWS_DEFAULT_REGION}.amazonaws.com/${PJ_NAME}-${INFRA_ENV}-api-nginx

      # コミットハッシュ取得（イメージタグに使用）
      - COMMIT_HASH=$(echo $CODEBUILD_RESOLVED_SOURCE_VERSION | cut -c 1-7)

      # Docker Hubログイン
      - echo ${DOCKER_TOKEN} | docker login -u ${DOCKER_USER} --password-stdin

      # キャッシュイメージpull
      - docker pull ${PHP_REPOSITORY}:latest-cache || true
      - docker pull ${NGINX_REPOSITORY}:latest-cache || true
```

### build（ビルド処理）

**目的**: Dockerイメージのビルド

**API例**:
```yaml
phases:
  build:
    commands:
      # PHPイメージビルド
      - |
        docker build \
          --build-arg MOMENTO_API_KEY="${MOMENTO_API_KEY}" \
          --build-arg BUILDKIT_INLINE_CACHE=1 \
          -f docker/envs/ecs/php/Dockerfile \
          -t ${PHP_REPOSITORY}:latest \
          -t ${PHP_REPOSITORY}:latest-cache \
          -t ${PHP_REPOSITORY}:${COMMIT_HASH} \
          --cache-from ${PHP_REPOSITORY}:latest-cache \
        .

      # Nginxイメージビルド
      - |
        docker build \
          --build-arg BUILDKIT_INLINE_CACHE=1 \
          -f docker/envs/ecs/nginx/Dockerfile \
          -t ${NGINX_REPOSITORY}:latest \
          -t ${NGINX_REPOSITORY}:latest-cache \
          -t ${NGINX_REPOSITORY}:${COMMIT_HASH} \
          --cache-from ${NGINX_REPOSITORY}:latest-cache \
        .
```

**Admin例**:
```yaml
phases:
  build:
    commands:
      # PHP-Adminイメージビルド（Git認証情報を渡す）
      - |
        docker build \
          --build-arg GIT_USER="${GIT_USER_ID}" \
          --build-arg GIT_TOKEN="${GIT_USER_TOKEN}" \
          --build-arg GIT_SSH_KEY="${GIT_SSH_KEY}" \
          --build-arg MOMENTO_API_KEY="${MOMENTO_API_KEY}" \
          -f docker/envs/ecs/php-admin/Dockerfile \
          -t ${PHP_REPOSITORY}:latest \
        .

      # ビルド成果物をホストにコピー（nginx用）
      - docker create --name temp-php-admin ${PHP_REPOSITORY}:${COMMIT_HASH}
      - docker cp temp-php-admin:/var/www/admin/public/build ./admin/public/build
      - docker rm temp-php-admin
```

### post_build（ビルド後処理）

**目的**: マイグレーション実行、イメージpush、成果物ファイル生成

**API例**:
```yaml
phases:
  post_build:
    commands:
      # マイグレーション実行用コンテナ起動
      - |
        docker run \
          -e TIDB_MYSQL_ATTR_SSL_CA=${TIDB_MYSQL_ATTR_SSL_CA} \
          -e DB_CONNECTION=tidb \
          -e TIDB_HOST=${DB_HOST} \
          -e TIDB_PASSWORD=${DB_PASSWORD} \
          -dit --init --name php ${PHP_REPOSITORY}:latest

      # マイグレーション実行
      - docker exec php bash -c "php /var/www/api/artisan migrate"

      # イメージpush
      - docker push ${PHP_REPOSITORY}:latest
      - docker push ${PHP_REPOSITORY}:${COMMIT_HASH}

      # プレースホルダー置換（後述）
      - PJ_NAME2=$(echo ${PJ_NAME} | sed "s/\(.*\)/\U\1/")
      - sed -i -e "s;<PHP_IMAGE>;${PHP_REPOSITORY}:${COMMIT_HASH};g" codebuild/artifacts/taskdefinitions.json
```

## プレースホルダー置換

### 置換対象ファイル

`post_build`フェーズで以下のファイルのプレースホルダーを置換：

```
codebuild/artifacts/
├── imagedefinitions.json      # イメージURL置換のみ
├── imagedefinitions-admin.json
├── taskdefinitions.json       # イメージURL + 環境変数
└── taskdefinitions-admin.json
```

### imagedefinitions.jsonの置換

**目的**: CodeDeployにイメージURLを渡す

```yaml
- |
  sed -i \
    -e "s;<PHP_IMAGE>;${PHP_REPOSITORY}:${COMMIT_HASH};g" \
    -e "s;<NGINX_IMAGE>;${NGINX_REPOSITORY}:${COMMIT_HASH};g" \
    -e "s;<DATADOG_IMAGE>;${DATADOG_REPOSITORY}:${COMMIT_HASH};g" \
    codebuild/artifacts/imagedefinitions.json
```

### taskdefinitions.jsonの置換

**目的**: ECS Task Definitionに環境固有の値を設定

```yaml
- PJ_NAME2=$(echo ${PJ_NAME} | sed "s/\(.*\)/\U\1/")  # 小文字→大文字変換
- |
  sed -i \
    -e "s;<PHP_IMAGE>;${PHP_REPOSITORY}:${COMMIT_HASH};g" \
    -e "s;<NGINX_IMAGE>;${NGINX_REPOSITORY}:${COMMIT_HASH};g" \
    -e "s;<DATADOG_IMAGE>;${DATADOG_REPOSITORY}:${COMMIT_HASH};g" \
    -e "s;<SECRET>;${SECRET};g" \
    -e "s;<INFRA_ENV>;${INFRA_ENV};g" \
    -e "s;<PJ_NAME>;${PJ_NAME};g" \
    -e "s;<PJ_NAME2>;${PJ_NAME2};g" \
    -e "s;<APP_ENV>;${APP_ENV};g" \
    -e "s;<AWS_DEFAULT_REGION>;${AWS_DEFAULT_REGION};g" \
    -e "s;<AWS_ACCOUNT_ID>;${AWS_ACCOUNT_ID};g" \
    codebuild/artifacts/taskdefinitions.json
```

### 置換変数の出所

| プレースホルダー | 変数名 | 出所 |
|---|---|---|
| `<PHP_IMAGE>` | `${PHP_REPOSITORY}:${COMMIT_HASH}` | pre_buildで生成 |
| `<SECRET>` | `${SECRET}` | CodeBuildプロジェクト環境変数 |
| `<INFRA_ENV>` | `${INFRA_ENV}` | CodeBuildプロジェクト環境変数 |
| `<PJ_NAME>` | `${PJ_NAME}` | CodeBuildプロジェクト環境変数 |
| `<PJ_NAME2>` | `${PJ_NAME2}` | post_buildで生成（大文字変換） |
| `<AWS_ACCOUNT_ID>` | `${AWS_ACCOUNT_ID}` | parameter-store |

## Dockerfile ARGの渡し方

### ビルド引数が必要な場合

Dockerfileで`ARG`を使用している場合、buildspecで`--build-arg`を指定：

**Dockerfile例**:
```dockerfile
ARG MOMENTO_API_KEY
RUN some-command --api-key=${MOMENTO_API_KEY}
```

**buildspec.yml**:
```yaml
phases:
  build:
    commands:
      - |
        docker build \
          --build-arg MOMENTO_API_KEY="${MOMENTO_API_KEY}" \
          -f docker/envs/ecs/php/Dockerfile \
          -t ${PHP_REPOSITORY}:latest \
        .
```

### 現在使用されているビルド引数

**API (`buildspec.yml`)**:
```yaml
--build-arg MOMENTO_API_KEY="${MOMENTO_API_KEY}"
--build-arg BUILDKIT_INLINE_CACHE=1
```

**Admin (`buildspec-admin.yml`)**:
```yaml
--build-arg GIT_USER="${GIT_USER_ID}"
--build-arg GIT_TOKEN="${GIT_USER_TOKEN}"
--build-arg GIT_SSH_KEY="${GIT_SSH_KEY}"
--build-arg MOMENTO_API_KEY="${MOMENTO_API_KEY}"
--build-arg BUILDKIT_INLINE_CACHE=1
```

### 新しいARGを追加する場合

**手順**:

1. **Dockerfileに追加**:
   ```dockerfile
   ARG NEW_BUILD_ARG
   RUN echo "Using ${NEW_BUILD_ARG}"
   ```

2. **buildspecのenv.secrets-managerまたはenv.parameter-storeに追加**:
   ```yaml
   env:
     secrets-manager:
       NEW_BUILD_ARG: glow-${INFRA_ENV}-secrets:new-build-arg
   ```

3. **buildspecのbuildフェーズに--build-arg追加**:
   ```yaml
   - |
     docker build \
       --build-arg NEW_BUILD_ARG="${NEW_BUILD_ARG}" \
       --build-arg MOMENTO_API_KEY="${MOMENTO_API_KEY}" \
       -f docker/envs/ecs/php/Dockerfile \
     .
   ```

## マイグレーション実行

### API

```yaml
- |
  docker run \
    -e TIDB_MYSQL_ATTR_SSL_CA=${TIDB_MYSQL_ATTR_SSL_CA} \
    -e DB_CONNECTION=tidb \
    -e TIDB_DATABASE=${INFRA_ENV} \
    -e TIDB_HOST=${DB_HOST} \
    -e TIDB_PORT=4000 \
    -e TIDB_USERNAME=${DB_USERNAME} \
    -e TIDB_PASSWORD=${DB_PASSWORD} \
    # ... 他のDB接続情報
    -dit --init --name php ${PHP_REPOSITORY}:latest
- docker exec php bash -c "php /var/www/api/artisan migrate"
```

### Admin

```yaml
- |
  docker run \
    -e DB_CONNECTION=admin \
    -e ADMIN_DB_DATABASE=admin \
    -e ADMIN_DB_HOST=db-write.${INFRA_ENV}.glow.internal \
    -e ADMIN_DB_USERNAME=${DB_USERNAME} \
    -e ADMIN_DB_PASSWORD=${DB_PASSWORD} \
    # ... 他のDB接続情報
    -dit --init --name php ${PHP_REPOSITORY}:latest
- docker exec php bash -c "php /var/www/admin/artisan migrate --database=admin --path=database/migrations"
```

**注意**: マイグレーション用の環境変数は、secrets-managerから取得した値を使用

## 成果物（artifacts）

```yaml
artifacts:
  files:
    - codebuild/artifacts/*
```

**生成されるファイル**:
- `imagedefinitions.json` / `imagedefinitions-admin.json`
- `taskdefinitions.json` / `taskdefinitions-admin.json`
- `appspec.yaml` / `appspec-admin.yaml`

これらがCodeDeployパイプラインに渡され、ECSデプロイに使用されます。

## 実装時の注意点

### ✅ 正しい実装

```yaml
# 新しいビルド引数を追加
env:
  secrets-manager:
    NEW_API_KEY: glow-${INFRA_ENV}-secrets:new-api-key

phases:
  build:
    commands:
      - |
        docker build \
          --build-arg NEW_API_KEY="${NEW_API_KEY}" \
          --build-arg MOMENTO_API_KEY="${MOMENTO_API_KEY}" \
          -f docker/envs/ecs/php/Dockerfile \
        .
```

### ❌ 間違った実装

```yaml
# ❌ 環境固有の値をハードコード
phases:
  build:
    commands:
      - |
        docker build \
          --build-arg NEW_API_KEY="dev-api-key-12345" \
        .
```

### マルチライン記述

`|`（パイプ）を使用してコマンドを複数行に分割：

```yaml
commands:
  - |
    docker build \
      --build-arg ARG1="${VAR1}" \
      --build-arg ARG2="${VAR2}" \
      -f Dockerfile \
      -t image:tag \
    .
```

**注意**: 末尾の`.`を忘れずに（dockerコンテキストパス）

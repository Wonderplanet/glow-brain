# Artisanコマンド実行例

glow-serverのLaravel Artisanコマンドをsailコマンドで実行する方法。

## 重要な前提

- **実行場所**: glow-serverルートディレクトリ
- **cd禁止**: `cd api`や`cd admin`は使わない
- **API用**: `sail artisan`
- **Admin用**: `sail admin artisan`

---

## よく使うArtisanコマンド

### tinker（対話型シェル）

```bash
# API用tinker起動
sail artisan tinker

# Admin用tinker起動
sail admin artisan tinker

# tinker内での操作例
>>> $user = UsrUser::find(1);
>>> $user->name;
>>> DB::table('usr_users')->count();
>>> exit
```

### キャッシュ操作

```bash
# キャッシュクリア
sail artisan cache:clear
sail admin artisan cache:clear

# コンフィグキャッシュクリア
sail artisan config:clear
sail admin artisan config:clear

# ルートキャッシュクリア
sail artisan route:clear
sail admin artisan route:clear

# ビューキャッシュクリア
sail artisan view:clear
sail admin artisan view:clear

# 全キャッシュクリア
sail artisan optimize:clear
sail admin artisan optimize:clear
```

### ルート確認

```bash
# 全ルート一覧
sail artisan route:list
sail admin artisan route:list

# 特定のルートを検索
sail artisan route:list --name=user
sail artisan route:list --path=api/v1/users
```

### データベース操作

```bash
# マイグレーション
sail artisan migrate
sail admin artisan migrate

# シーダー実行
sail artisan db:seed
sail artisan db:seed --class=UsrUserSeeder
sail admin artisan db:seed

# マイグレーション＋シーダー実行
sail artisan migrate --seed
sail admin artisan migrate --seed
```

### コマンド作成

```bash
# カスタムコマンド作成
sail artisan make:command SendNotifications
sail admin artisan make:command AdminCleanup
```

### その他の便利なコマンド

```bash
# イベントリスナー一覧
sail artisan event:list

# ジョブ実行（キュー）
sail artisan queue:work
sail artisan queue:retry all

# スケジュール確認
sail artisan schedule:list

# メンテナンスモード
sail artisan down
sail artisan up
```

---

## 間違い例

### よくある間違い

```bash
# ❌ cd api は不要
cd api && php artisan tinker

# ❌ cd api してから相対パス実行は不要
cd api && ../tools/bin/sail-wp artisan tinker

# ❌ Dockerコマンド直接実行は不要
docker compose exec php php artisan tinker

# ❌ cd admin は不要
cd admin && php artisan tinker

# ✅ 正しい
sail artisan tinker
sail admin artisan tinker
```

---

## sailコマンドの仕組み

### sail artisan の内部動作

`sail artisan`は内部的に以下を実行します：

```bash
# tools/bin/sail-wpスクリプト内（204-213行目）
docker compose exec php php artisan [引数]
```

### sail admin artisan の内部動作

`sail admin artisan`は内部的に以下を実行します：

```bash
# tools/bin/sail-wpスクリプト内（92-109行目、204-213行目）
# admin/.envを読み込み、php-adminコンテナで実行
docker compose exec php-admin php artisan [引数]
```

---

## その他のsailコマンド

### composer（依存関係管理）

```bash
# composer install
sail composer install

# composer update
sail composer update

# パッケージ追加
sail composer require laravel/sanctum

# パッケージ削除
sail composer remove laravel/sanctum
```

### php（PHPコマンド直接実行）

```bash
# PHPバージョン確認
sail php -v

# PHPスクリプト実行
sail php scripts/sample.php
```

### shell/bash（コンテナ内シェル起動）

```bash
# API用コンテナのシェル起動
sail shell

# または
sail bash

# Admin用コンテナのシェル起動
sail admin shell

# コンテナ内での操作例
$ pwd
/var/www/html/api
$ ls -la
$ exit
```

### mysql（MySQLクライアント起動）

```bash
# MySQLクライアント起動
sail mysql

# MySQL内での操作例
mysql> SHOW DATABASES;
mysql> USE localDB;
mysql> SHOW TABLES;
mysql> SELECT * FROM usr_users LIMIT 10;
mysql> exit
```

### redis（Redisクライアント起動）

```bash
# Redisクライアント起動
sail redis

# Redis内での操作例
127.0.0.1:6379> KEYS *
127.0.0.1:6379> GET some_key
127.0.0.1:6379> exit
```

---

## 典型的な使用例

### 開発中のキャッシュクリア

```bash
# 設定変更後のキャッシュクリア
sail artisan config:clear
sail artisan cache:clear
sail artisan route:clear
sail artisan view:clear

# 全クリア（推奨）
sail artisan optimize:clear
```

### tinkerでのデバッグ

```bash
# tinker起動
sail artisan tinker

# データ確認
>>> UsrUser::count();
>>> UsrUser::where('name', 'like', '%test%')->get();
>>> DB::table('usr_users')->where('id', 1)->first();

# データ操作（注意して実行）
>>> $user = UsrUser::find(1);
>>> $user->name = 'Updated Name';
>>> $user->save();

# 終了
>>> exit
```

### ルート確認

```bash
# API全ルート確認
sail artisan route:list

# 特定のエンドポイント検索
sail artisan route:list | grep user
sail artisan route:list --path=api/v1/users
```

### データベース初期化

```bash
# マイグレーション実行＋シーダー実行
sail artisan migrate:fresh --seed

# 特定DB接続のみ
sail artisan migrate:fresh --database=mst --seed
```

---

## まとめ

**全てのコマンドはglow-serverルートから実行:**

```bash
# ✅ 正しい実行方法
sail artisan tinker
sail artisan cache:clear
sail artisan route:list
sail composer install
sail php -v
sail shell
sail mysql

# ❌ 絶対にやってはいけないこと
cd api && php artisan tinker
cd api && ../tools/bin/sail-wp artisan tinker
docker compose exec php php artisan tinker
```

**API用とAdmin用の使い分け:**

```bash
# API用
sail artisan tinker
sail artisan cache:clear
sail composer install
sail shell

# Admin用
sail admin artisan tinker
sail admin artisan cache:clear
sail admin composer install
sail admin shell
```

**よく使うコマンド一覧:**

| コマンド | 説明 |
|---------|------|
| `sail artisan tinker` | 対話型シェル起動 |
| `sail artisan cache:clear` | キャッシュクリア |
| `sail artisan optimize:clear` | 全キャッシュクリア |
| `sail artisan route:list` | ルート一覧表示 |
| `sail artisan migrate` | マイグレーション実行 |
| `sail composer install` | 依存関係インストール |
| `sail shell` | コンテナ内シェル起動 |
| `sail mysql` | MySQLクライアント起動 |
| `sail redis` | Redisクライアント起動 |

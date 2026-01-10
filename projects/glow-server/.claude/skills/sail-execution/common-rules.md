# sail-wpコマンド実行の基本ルール

## 絶対に守るべき3つのルール

### 1. 常にglow-serverルートディレクトリから実行

```bash
# 現在地確認
pwd
# /Users/username/.../glow-server であることを確認

# ✅ glow-serverルートから実行
sail phpcs app/Http/Controllers/EncyclopediaController.php
```

**理由**: sail-wpスクリプト（`tools/bin/sail-wp`）は相対パスで.envファイルを読み込むため、glow-serverルートから実行する必要があります。

### 2. `cd api`や`cd admin`は絶対に使わない

```bash
# ❌ 間違い
cd api && php artisan test
cd api && ../tools/bin/sail-wp test
cd admin && ../tools/bin/sail-wp admin test

# ✅ 正しい
sail test
sail admin test
```

**理由**: sailコマンドが自動的に適切なディレクトリ（apiまたはadmin）を判断し、適切なDockerコンテナで実行します。

### 3. `sail`（API用）と`sail admin`（Admin用）を使い分ける

```bash
# ✅ API用（デフォルト）
sail artisan migrate
sail phpcs app/Http/Controllers/UserController.php
sail test

# ✅ Admin用
sail admin artisan migrate
sail admin phpcs app/Http/Controllers/AdminController.php
sail admin test
```

**理由**: `sail`はapi/ディレクトリのLaravelを実行、`sail admin`はadmin/ディレクトリのLaravelを実行します。

---

## よくある間違い vs 正しい実行方法

### phpcs（コーディング規約チェック）

| ❌ 間違い | ✅ 正しい |
|---------|---------|
| `cd api && ../tools/bin/sail-wp exec php vendor/bin/phpcs --standard=phpcs.xml app/Http/Controllers/EncyclopediaController.php` | `sail phpcs app/Http/Controllers/EncyclopediaController.php` |
| `cd api && php vendor/bin/phpcs app/Http/Controllers/EncyclopediaController.php` | `sail phpcs app/Http/Controllers/EncyclopediaController.php` |
| `docker compose exec php php vendor/bin/phpcs app/Http/Controllers/EncyclopediaController.php` | `sail phpcs app/Http/Controllers/EncyclopediaController.php` |

### phpcbf（自動修正）

| ❌ 間違い | ✅ 正しい |
|---------|---------|
| `cd api && ../tools/bin/sail-wp exec php vendor/bin/phpcbf --standard=phpcs.xml app/Http/Controllers/EncyclopediaController.php` | `sail phpcbf app/Http/Controllers/EncyclopediaController.php` |
| `docker compose exec php php vendor/bin/phpcbf app/Http/Controllers/EncyclopediaController.php` | `sail phpcbf app/Http/Controllers/EncyclopediaController.php` |

### phpstan（静的解析）

| ❌ 間違い | ✅ 正しい |
|---------|---------|
| `cd api && ../tools/bin/sail-wp exec php vendor/bin/phpstan analyse --memory-limit=-1 app/Http/Controllers` | `sail phpstan app/Http/Controllers` |
| `docker compose exec php php vendor/bin/phpstan analyse app/Http/Controllers` | `sail phpstan app/Http/Controllers` |

### deptrac（アーキテクチャチェック）

| ❌ 間違い | ✅ 正しい |
|---------|---------|
| `cd api && ../tools/bin/sail-wp exec php vendor/bin/deptrac analyse` | `sail deptrac` |
| `docker compose exec php php vendor/bin/deptrac analyse` | `sail deptrac` |

### test（PHPUnit）

| ❌ 間違い | ✅ 正しい |
|---------|---------|
| `cd api && php artisan test` | `sail test` |
| `cd api && ../tools/bin/sail-wp test` | `sail test` |
| `docker compose exec php php artisan test` | `sail test` |
| `./tools/bin/sail-wp test` ※ | `sail test` |

※ `./tools/bin/sail-wp`は絶対パス表記のため動作しますが、エイリアス`sail`を使うのが推奨です。

### artisan（Laravel Artisan）

| ❌ 間違い | ✅ 正しい |
|---------|---------|
| `cd api && php artisan migrate` | `sail artisan migrate` |
| `cd api && ../tools/bin/sail-wp artisan migrate` | `sail artisan migrate` |
| `docker compose exec php php artisan migrate` | `sail artisan migrate` |
| `cd admin && php artisan migrate` | `sail admin artisan migrate` |

---

## sail-wpの仕組み

### sailコマンドの正体

`sail`は`tools/bin/sail-wp`スクリプトへのエイリアスです。

```bash
# エイリアス設定（通常は~/.zshrcや~/.bashrcに設定済み）
alias sail='./tools/bin/sail-wp'
```

### sailコマンドの動作

1. **引数で処理を分岐**
   - `sail phpcs` → `php vendor/bin/phpcs --standard=phpcs.xml`を実行
   - `sail phpstan` → `php vendor/bin/phpstan analyse --memory-limit=-1`を実行
   - `sail test` → `php artisan test`を実行
   - `sail artisan` → `php artisan`を実行

2. **適切なコンテナを自動選択**
   - `sail` → php または php-datadog コンテナで実行
   - `sail admin` → php-admin コンテナで実行

3. **適切な.envファイルを読み込み**
   - `sail` → `api/.env`を読み込み
   - `sail admin` → `admin/.env`を読み込み

### なぜcdが不要なのか

**tools/bin/sail-wp（抜粋）:**
```bash
# 92-109行目
if [ "$1" == "admin" ]; then
    shift 1
    DOTENV_PATH="$(dirname $0)/../../admin/.env"
    export APP_SERVICE='php-admin'
else
    DOTENV_PATH="$(dirname $0)/../../api/.env"
    export APP_SERVICE=${APP_SERVICE:-$DEFAULT_APP_SERVICE}
fi
```

sail-wpスクリプトが自動的に：
- `sail` → `api/.env`を読み込み、phpコンテナで実行
- `sail admin` → `admin/.env`を読み込み、php-adminコンテナで実行

つまり、**cdでディレクトリを移動しなくても、sailコマンドが自動的に適切なディレクトリとコンテナを判断**します。

---

## チェックリスト

実行前に以下を確認してください：

- [ ] 現在地はglow-serverルートディレクトリか（`pwd`で確認）
- [ ] `cd api`や`cd admin`を使っていないか
- [ ] API用コマンドは`sail`、Admin用コマンドは`sail admin`を使っているか
- [ ] Dockerコンテナに直接入る（`docker compose exec`）コマンドを使っていないか
- [ ] `../tools/bin/sail-wp`のような相対パス表記ではなく、`sail`エイリアスを使っているか

---

## まとめ

**絶対に覚えておくべきこと:**

1. **常にglow-serverルートディレクトリから実行**
2. **`cd api`や`cd admin`は絶対に使わない**
3. **API用は`sail`、Admin用は`sail admin`**
4. **Dockerコマンドを直接使わない（sailが自動処理）**
5. **sailコマンドがディレクトリとコンテナを自動判断**

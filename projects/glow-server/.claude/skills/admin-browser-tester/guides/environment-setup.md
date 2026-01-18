# 環境セットアップガイド

admin動作確認を実施する前に、必要な環境情報を取得する方法を説明します。

## 事前確認項目

テスト実施前に以下を確認します：

1. **実装内容の確認**
   - 変更ファイルの特定
   - 追加機能の把握
   - テスト対象の明確化

2. **Docker環境の起動状態確認**
   ```bash
   docker compose ps
   ```
   - すべてのコンテナが「Up」状態であることを確認
   - 特にnginx、phpコンテナが起動していること

3. **ポート番号の確認**
   - .envファイルからNGINX_ADMIN_PORTを取得
   - デフォルト値は8081

## ポート番号の取得方法

### 方法1: .envファイルから直接確認

```bash
# .envファイルから取得
grep NGINX_ADMIN_PORT .env
```

出力例:
```
NGINX_ADMIN_PORT=8081
```

### 方法2: Bashコマンドで環境変数として取得

```bash
# 環境変数として取得（.envが読み込まれている場合）
echo $NGINX_ADMIN_PORT
```

## 認証情報の確認

### デフォルト認証情報

通常、以下のデフォルト認証情報が使用されます：

- **メールアドレス**: `admin@wonderpla.net`
- **パスワード**: `admin`

### ログイン失敗時の確認手順

ログインに失敗した場合、以下の順序で確認：

#### 1. database/seedersを確認

```bash
# AdminUserSeeder.phpの内容を確認
cat admin/database/seeders/AdminUserSeeder.php
```

シーダーファイルで設定されているメールアドレスとパスワードを確認します。

#### 2. .envファイルを確認

```bash
# 環境変数として認証情報が定義されている場合
grep ADMIN_EMAIL .env
grep ADMIN_PASSWORD .env
```

#### 3. artisan tinkerで確認

```bash
# 管理者ユーザーの存在確認
./tools/bin/sail-wp admin artisan tinker
```

Tinker内で実行:
```php
// 管理者ユーザーを検索
\App\Models\AdminUser::all();

// メールアドレスで検索
\App\Models\AdminUser::where('email', 'admin@wonderpla.net')->first();
```

#### 4. ユーザーが存在しない場合の対処

```bash
# シーダーを実行して管理者ユーザーを作成
./tools/bin/sail-wp admin artisan db:seed --class=AdminUserSeeder
```

または、Tinker内で直接作成:
```php
\App\Models\AdminUser::create([
    'name' => 'Admin User',
    'email' => 'admin@wonderpla.net',
    'password' => \Hash::make('admin'),
]);
```

## admin URLの構成

admin URLは以下の形式で構成されます：

```
http://localhost:{NGINX_ADMIN_PORT}/admin
```

例:
- ポート番号が8081の場合: `http://localhost:8081/admin`
- ポート番号が8082の場合: `http://localhost:8082/admin`

## Docker環境が起動していない場合

```bash
# Dockerコンテナの起動
./tools/bin/sail-wp up -d

# 起動完了まで待機（30秒程度）
sleep 30

# 起動状態の確認
docker compose ps
```

## スクリーンショット保存先の準備

テスト実行時にスクリーンショットを撮影するため、保存先ディレクトリを準備します：

```bash
# .claude/tmpディレクトリの作成（存在しない場合）
mkdir -p .claude/tmp
```

**重要**: `.claude/tmp/` ディレクトリはGit管理対象外です（.gitignoreに記載）。

## 環境確認チェックリスト

テスト実施前に以下をチェック：

- [ ] 実装内容を確認した
- [ ] Docker環境が起動している（`docker compose ps`で確認）
- [ ] NGINX_ADMIN_PORTを確認した（デフォルト: 8081）
- [ ] 認証情報を確認した（デフォルト: admin@wonderpla.net / admin）
- [ ] `.claude/tmp/` ディレクトリが存在する
- [ ] admin URLにアクセスできることを確認した

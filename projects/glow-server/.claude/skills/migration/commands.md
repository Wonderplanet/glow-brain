# マイグレーションコマンドガイド

## 重要：コマンドの実行場所とsailの使い方

### ❌ やってはいけないこと

```bash
# ❌ cd api は不要
cd api && php artisan make:migration ...

# ❌ cd admin も不要
cd admin && php artisan make:migration ...

# ❌ Dockerコンテナ内に直接入らない
docker compose exec php php artisan migrate
```

### ✅ 正しい実行方法

**glow-serverプロジェクトのルートディレクトリで実行してください：**

```bash
# ✅ API用コマンド（glow-serverルートから）
sail artisan migrate
sail artisan make:migration create_usr_examples_table

# ✅ Admin用コマンド（glow-serverルートから）
sail admin artisan migrate
sail admin artisan make:migration create_adm_examples_table
```

**sailコマンドの仕組み：**
- `sail artisan` → `api/`ディレクトリのLaravelを実行
- `sail admin artisan` → `admin/`ディレクトリのLaravelを実行
- 自動的に適切なDockerコンテナで実行される
- ディレクトリ移動（cd）は一切不要

## マイグレーション作成

### API用マイグレーション作成

```bash
# usr/log/sys用（デフォルトディレクトリ）
sail artisan make:migration create_usr_user_profiles_table
sail artisan make:migration add_level_column_to_usr_user_profiles_table

# テーブル作成用テンプレート付き
sail artisan make:migration create_usr_examples_table --create=usr_examples

# テーブル変更用テンプレート付き
sail artisan make:migration add_status_to_usr_examples_table --table=usr_examples
```

**mst/mng用の場合：**
```bash
# 1. まず作成（デフォルト場所に作成される）
sail artisan make:migration create_mst_units_table

# 2. 作成されたファイルを適切なサブディレクトリに移動
mv api/database/migrations/20XX_XX_XX_XXXXXX_create_mst_units_table.php \
   api/database/migrations/mst/

# 3. ファイルを開いて$connectionプロパティを追加
# protected $connection = Database::MST_CONNECTION;
```

### Admin用マイグレーション作成

```bash
# admin用マイグレーション作成
sail admin artisan make:migration create_adm_user_ban_histories_table

# テーブル作成用テンプレート付き
sail admin artisan make:migration create_adm_examples_table --create=adm_examples

# テーブル変更用テンプレート付き
sail admin artisan make:migration add_reason_to_adm_user_ban_histories_table --table=adm_user_ban_histories
```

## マイグレーション実行

### 基本的な実行

```bash
# API用：全DB接続のマイグレーションを実行
sail artisan migrate

# Admin用：admin DBのマイグレーションを実行
sail admin artisan migrate

# 本番環境で強制実行（確認プロンプトをスキップ）
sail artisan migrate --force
sail admin artisan migrate --force
```

### DB接続を指定して実行

```bash
# 特定のDB接続のみ実行
sail artisan migrate --database=mst
sail artisan migrate --database=mng
sail artisan migrate --database=tidb  # usr/log/sysはすべてtidb接続

# 注意: usr, log, sysは個別指定できない（すべてtidb接続）
```

### ステータス確認

```bash
# API用マイグレーション状態確認（全DB接続）
sail artisan migrate:status

# 特定DB接続のステータス確認
sail artisan migrate:status --database=mst
sail artisan migrate:status --database=mng
sail artisan migrate:status --database=tidb  # usr/log/sys共通

# Admin用マイグレーション状態確認
sail admin artisan migrate:status
```

### ドライラン（実行せずSQLだけ確認）

```bash
# 実行されるSQLを確認（実際には実行しない）
sail artisan migrate --pretend
sail artisan migrate --database=mst --pretend

# Admin用ドライラン
sail admin artisan migrate --pretend
```

## ロールバック

### 基本的なロールバック

```bash
# 最後のバッチをロールバック
sail artisan migrate:rollback
sail admin artisan migrate:rollback

# 指定ステップ数ロールバック
sail artisan migrate:rollback --step=1
sail artisan migrate:rollback --step=3

# 特定DB接続のみロールバック
sail artisan migrate:rollback --database=mst
sail artisan migrate:rollback --database=mng
sail artisan migrate:rollback --database=tidb  # usr/log/sys共通
```

### リフレッシュ・リセット

```bash
# 全マイグレーションをロールバックして再実行
sail artisan migrate:refresh
sail admin artisan migrate:refresh

# 全テーブルを削除してマイグレーション再実行
sail artisan migrate:fresh
sail admin artisan migrate:fresh
```

## よくある間違いと修正方法

### 間違い1: cd api を使ってしまう

```bash
# ❌ 間違い
cd api && php artisan make:migration create_usr_examples_table

# ✅ 正解（glow-serverルートから）
sail artisan make:migration create_usr_examples_table
```

### 間違い2: --pathオプションを使ってしまう

```bash
# ❌ 間違い（pathオプションは不要）
sail artisan make:migration create_usr_examples_table --path=database/migrations

# ✅ 正解
sail artisan make:migration create_usr_examples_table
```

### 間違い3: Dockerコマンドを直接使ってしまう

```bash
# ❌ 間違い
docker compose exec php php artisan migrate
docker compose exec php-admin php artisan migrate

# ✅ 正解
sail artisan migrate
sail admin artisan migrate
```

## 典型的なワークフロー

### usr/log/sysテーブルの作成フロー

```bash
# 1. glow-serverルートディレクトリにいることを確認
pwd  # /path/to/glow-server であることを確認

# 2. マイグレーションファイル作成
sail artisan make:migration create_usr_user_profiles_table

# 3. ファイルを編集（api/database/migrations/ に作成される）
# エディタでファイルを開いて実装

# 4. ドライランで確認
sail artisan migrate --pretend

# 5. 実行
sail artisan migrate

# 6. ステータス確認
sail artisan migrate:status
```

### mstテーブルの作成フロー

```bash
# 1. glow-serverルートディレクトリにいることを確認
pwd  # /path/to/glow-server であることを確認

# 2. マイグレーションファイル作成
sail artisan make:migration create_mst_units_table

# 3. 作成されたファイルをmstサブディレクトリに移動
mv api/database/migrations/20XX_XX_XX_XXXXXX_create_mst_units_table.php \
   api/database/migrations/mst/

# 4. ファイルを編集
# - $connectionプロパティを追加: protected $connection = Database::MST_CONNECTION;
# - テーブル定義を実装

# 5. ドライラン
sail artisan migrate --database=mst --pretend

# 6. 実行
sail artisan migrate --database=mst

# 7. ステータス確認
sail artisan migrate:status --database=mst
```

### adminテーブルの作成フロー

```bash
# 1. glow-serverルートディレクトリにいることを確認
pwd  # /path/to/glow-server であることを確認

# 2. Admin用マイグレーションファイル作成
sail admin artisan make:migration create_adm_user_ban_histories_table

# 3. ファイルを編集（admin/database/migrations/ に作成される）
# エディタでファイルを開いて実装

# 4. ドライランで確認
sail admin artisan migrate --pretend

# 5. 実行
sail admin artisan migrate

# 6. ステータス確認
sail admin artisan migrate:status
```

## その他の便利なコマンド

### マイグレーションファイルの確認

```bash
# API用マイグレーションファイル一覧
ls -la api/database/migrations/
ls -la api/database/migrations/mst/
ls -la api/database/migrations/mng/

# Admin用マイグレーションファイル一覧
ls -la admin/database/migrations/
```

### 実行履歴の確認

```bash
# tinkerで直接確認
sail artisan tinker
# >>> DB::table('migrations')->orderBy('id', 'desc')->get();
# >>> exit

# Admin用
sail admin artisan tinker
# >>> DB::table('migrations')->orderBy('id', 'desc')->get();
# >>> exit
```

## まとめ

**絶対に覚えておくべきこと：**

1. **常にglow-serverルートディレクトリから実行**
2. **`cd api`や`cd admin`は不要**
3. **API用は`sail artisan`、Admin用は`sail admin artisan`**
4. **Dockerコマンドを直接使わない（sailが自動処理）**
5. **--pathオプションは基本的に不要**

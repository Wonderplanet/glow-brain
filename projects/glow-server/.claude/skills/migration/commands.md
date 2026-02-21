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

**mst/opr用の場合：**
```bash
# mst DBのマイグレーション作成（pathオプションで直接mstディレクトリに作成）
sail artisan make:migration create_mst_units_table --path=database/migrations/mst
sail artisan make:migration add_name_to_mst_units_table --path=database/migrations/mst

# テーブル作成用テンプレート付き
sail artisan make:migration create_mst_units_table --create=mst_units --path=database/migrations/mst

# テーブル変更用テンプレート付き
sail artisan make:migration add_status_to_mst_units_table --table=mst_units --path=database/migrations/mst

# 注意: 作成後、ファイルを開いて$connectionプロパティを追加すること
# protected $connection = Database::MST_CONNECTION;
```

**mng用の場合：**
```bash
# mng DBのマイグレーション作成（pathオプションで直接mngディレクトリに作成）
sail artisan make:migration create_mng_configs_table --path=database/migrations/mng
sail artisan make:migration add_value_to_mng_configs_table --path=database/migrations/mng

# テーブル作成用テンプレート付き
sail artisan make:migration create_mng_configs_table --create=mng_configs --path=database/migrations/mng

# テーブル変更用テンプレート付き
sail artisan make:migration add_type_to_mng_configs_table --table=mng_configs --path=database/migrations/mng

# 注意: 作成後、ファイルを開いて$connectionプロパティを追加すること
# protected $connection = Database::MNG_CONNECTION;
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
```

### DB接続を指定して実行

```bash
# mst/opr DBのみ実行
sail artisan migrate --database=mst --path=database/migrations/mst

# mng DBのみ実行
sail artisan migrate --database=mng --path=database/migrations/mng

# tidb（usr/log/sys）のみ実行
sail artisan migrate --database=tidb --path=database/migrations

# 注意: usr, log, sysは個別指定できない（すべてtidb接続）
```

### ステータス確認

```bash
# API用マイグレーション状態確認（全DB接続）
sail artisan migrate:status

# 特定DB接続のステータス確認
sail artisan migrate:status --database=mst --path=database/migrations/mst
sail artisan migrate:status --database=mng --path=database/migrations/mng
sail artisan migrate:status --database=tidb --path=database/migrations

# Admin用マイグレーション状態確認
sail admin artisan migrate:status
```

### ドライラン（実行せずSQLだけ確認）

```bash
# 全DB接続のSQLを確認（実際には実行しない）
sail artisan migrate --pretend

# mst/opr DBのみドライラン
sail artisan migrate --database=mst --path=database/migrations/mst --pretend

# mng DBのみドライラン
sail artisan migrate --database=mng --path=database/migrations/mng --pretend

# tidb（usr/log/sys）のみドライラン
sail artisan migrate --database=tidb --path=database/migrations --pretend

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
sail artisan migrate:rollback --database=mst --path=database/migrations/mst
sail artisan migrate:rollback --database=mng --path=database/migrations/mng
sail artisan migrate:rollback --database=tidb --path=database/migrations
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

### 間違い2: Dockerコマンドを直接使ってしまう

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
sail artisan migrate --database=tidb --path=database/migrations --pretend

# 5. 実行
sail artisan migrate --database=tidb --path=database/migrations

# 6. ステータス確認
sail artisan migrate:status --database=tidb --path=database/migrations
```

### mst/oprテーブルの作成フロー

```bash
# 1. glow-serverルートディレクトリにいることを確認
pwd  # /path/to/glow-server であることを確認

# 2. マイグレーションファイル作成（pathオプションで直接mstディレクトリに作成）
sail artisan make:migration create_mst_units_table --path=database/migrations/mst

# 3. ファイルを編集（api/database/migrations/mst/ に作成される）
# - $connectionプロパティを追加: protected $connection = Database::MST_CONNECTION;
# - テーブル定義を実装

# 4. ドライラン
sail artisan migrate --database=mst --path=database/migrations/mst --pretend

# 5. 実行
sail artisan migrate --database=mst --path=database/migrations/mst

# 6. ステータス確認
sail artisan migrate:status --database=mst --path=database/migrations/mst
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

## まとめ

**絶対に覚えておくべきこと：**

1. **常にglow-serverルートディレクトリから実行**
2. **`cd api`や`cd admin`は不要**
3. **API用は`sail artisan`、Admin用は`sail admin artisan`**
4. **Dockerコマンドを直接使わない（sailが自動処理）**
5. **mst/opr/mng用のマイグレーション作成・実行時は必ず`--path`オプションを指定**

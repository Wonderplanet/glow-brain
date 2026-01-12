# マイグレーション実行コマンド実行例

glow-serverのマイグレーション操作をsailコマンドで実行する方法。

## 重要な前提

- **実行場所**: glow-serverルートディレクトリ
- **cd禁止**: `cd api`や`cd admin`は使わない
- **API用**: `sail artisan`
- **Admin用**: `sail admin artisan`

---

## マイグレーション作成

### API用マイグレーション作成

```bash
# usr/log/sys用（デフォルト）
sail artisan make:migration create_usr_user_profiles_table

# テーブル作成用テンプレート付き
sail artisan make:migration create_usr_examples_table --create=usr_examples

# テーブル変更用テンプレート付き
sail artisan make:migration add_status_to_usr_examples_table --table=usr_examples
```

**mst/mng用の場合:**

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

### 間違い例

```bash
# ❌ cd api は不要
cd api && php artisan make:migration create_usr_examples_table

# ❌ cd api してから相対パス実行は不要
cd api && ../tools/bin/sail-wp artisan make:migration create_usr_examples_table

# ❌ Dockerコマンド直接実行は不要
docker compose exec php php artisan make:migration create_usr_examples_table

# ❌ --pathオプションは不要
sail artisan make:migration create_usr_examples_table --path=database/migrations

# ✅ 正しい
sail artisan make:migration create_usr_examples_table
```

---

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

### ドライラン（実行せずSQLだけ確認）

```bash
# 実行されるSQLを確認（実際には実行しない）
sail artisan migrate --pretend
sail artisan migrate --database=mst --pretend

# Admin用ドライラン
sail admin artisan migrate --pretend
```

### 間違い例

```bash
# ❌ cd api は不要
cd api && php artisan migrate

# ❌ cd api してから相対パス実行は不要
cd api && ../tools/bin/sail-wp artisan migrate

# ❌ Dockerコマンド直接実行は不要
docker compose exec php php artisan migrate

# ❌ cd admin は不要
cd admin && php artisan migrate

# ✅ 正しい
sail artisan migrate
sail admin artisan migrate
```

---

## ステータス確認

### 基本的な確認

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

### 間違い例

```bash
# ❌ cd api は不要
cd api && php artisan migrate:status

# ❌ Dockerコマンド直接実行は不要
docker compose exec php php artisan migrate:status

# ✅ 正しい
sail artisan migrate:status
```

---

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

### 間違い例

```bash
# ❌ cd api は不要
cd api && php artisan migrate:rollback

# ❌ Dockerコマンド直接実行は不要
docker compose exec php php artisan migrate:rollback

# ✅ 正しい
sail artisan migrate:rollback
```

---

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

---

## よくある使い方

### 開発中の確認

```bash
# マイグレーションファイル作成
sail artisan make:migration create_usr_user_profiles_table

# ファイル編集後、ドライランで確認
sail artisan migrate --pretend

# 問題なければ実行
sail artisan migrate

# 状態確認
sail artisan migrate:status
```

### テーブル構造変更

```bash
# カラム追加マイグレーション作成
sail artisan make:migration add_level_column_to_usr_user_profiles_table --table=usr_user_profiles

# ファイル編集後、ドライランで確認
sail artisan migrate --pretend

# 実行
sail artisan migrate

# 問題があればロールバック
sail artisan migrate:rollback --step=1
```

### マイグレーションのやり直し

```bash
# 最後のマイグレーションをロールバック
sail artisan migrate:rollback --step=1

# ファイル修正

# ドライランで確認
sail artisan migrate --pretend

# 再実行
sail artisan migrate
```

---

## sailコマンドの仕組み

### sail artisan の内部動作

`sail artisan migrate`は内部的に以下を実行します：

```bash
# tools/bin/sail-wpスクリプト内（204-213行目）
docker compose exec php php artisan migrate [引数]
```

### sail admin artisan の内部動作

`sail admin artisan migrate`は内部的に以下を実行します：

```bash
# tools/bin/sail-wpスクリプト内（92-109行目、204-213行目）
# admin/.envを読み込み、php-adminコンテナで実行
docker compose exec php-admin php artisan migrate [引数]
```

---

## まとめ

**全てのコマンドはglow-serverルートから実行:**

```bash
# ✅ 正しい実行方法
sail artisan make:migration create_usr_examples_table
sail artisan migrate
sail artisan migrate:status
sail artisan migrate:rollback

# ❌ 絶対にやってはいけないこと
cd api && php artisan migrate
cd api && ../tools/bin/sail-wp artisan migrate
docker compose exec php php artisan migrate
```

**API用とAdmin用の使い分け:**

```bash
# API用
sail artisan make:migration create_usr_examples_table
sail artisan migrate
sail artisan migrate:status

# Admin用
sail admin artisan make:migration create_adm_examples_table
sail admin artisan migrate
sail admin artisan migrate:status
```

**必ず守るべきルール:**

1. **常にglow-serverルートディレクトリから実行**
2. **`cd api`や`cd admin`は不要**
3. **API用は`sail artisan`、Admin用は`sail admin artisan`**
4. **Dockerコマンドを直接使わない（sailが自動処理）**
5. **--pathオプションは基本的に不要**

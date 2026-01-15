# マイグレーション実装リファレンス

## 目次

- [実装時のチェックポイント](#実装時のチェックポイント)
- [コーディング規約](#コーディング規約)
- [TiDB固有の機能](#tidb固有の機能)
- [トラブルシューティング](#トラブルシューティング)
- [ベストプラクティス](#ベストプラクティス)

## 実装時のチェックポイント

### 1. ファイル配置場所の確認

テーブル接頭辞からDB接続を判断し、正しい配置場所を選択してください。

| テーブル接頭辞 | DB接続 | 配置場所 | $connection必要 |
|---------------|--------|----------|-----------------|
| `mst_*`, `opr_*` | mst | `api/database/migrations/mst/` | ✅ 必要 |
| `mng_*` | mng | `api/database/migrations/mng/` | ✅ 必要 |
| `usr_*` | usr (tidb) | `api/database/migrations/` | ❌ 不要 |
| `log_*` | log (tidb) | `api/database/migrations/` | ❌ 不要 |
| `sys_*` | sys (tidb) | `api/database/migrations/` | ❌ 不要 |
| `adm_*` | admin | `admin/database/migrations/` | ❌ 不要 |

**判断基準**:
- **MySQL系（mst/mng）**: サブディレクトリ配置 + `$connection`プロパティ必須
- **TiDB系（usr/log/sys）**: ルート直下配置、`$connection`不要
- **Admin系**: admin側のルート配置、`$connection`不要

### 2. $connectionプロパティの設定

mst/mng接続を使う場合は必ず設定してください：

```php
use App\Domain\Constants\Database;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION; // または MNG_CONNECTION

    // ...
}
```

**注意点**:
- `Database::MST_CONNECTION`と`Database::MNG_CONNECTION`は定数として定義済み
- usr/log/sys/adminではデフォルト接続を使うため不要

### 3. timestamp型の使い分け

**すべてのtimestampカラムで`timestampTz()`を使用してください：**

```php
// すべてのDB接続で統一（mst/mng/usr/log/sys/admin）
$table->timestampTz('created_at');
$table->timestampTz('updated_at');
$table->timestampTz('start_at')->nullable();
$table->timestampTz('end_at')->nullable();
```

**重要**:
- `timestamps()`ヘルパーは使用しない（timestampTzではなくtimestampが作成されるため）
- 必ず明示的に`timestampTz()`を2行記述する
- timezone対応を統一することでデータの一貫性を保つ

### 4. コメントの追加

全てのテーブルとカラムに日本語コメントを追加してください：

```php
Schema::create('usr_example', function (Blueprint $table) {
    $table->string('id', 255)->primary()->comment('UUID');
    $table->string('user_id', 255)->comment('ユーザーID');
    $table->integer('level')->comment('レベル');

    $table->comment('ユーザー情報テーブル');
});
```

**コメント記載のポイント**:
- カラムの用途を明確に記述
- 特殊な制約や仕様がある場合は詳しく記載
- テーブル全体の役割も`$table->comment()`で説明

### 5. インデックスの命名

インデックス名は明示的に指定してください：

```php
// 単一カラムのインデックス
$table->index('user_id', 'user_id_index');
$table->index('created_at', 'created_at_index');

// 複合インデックス
$table->index(['user_id', 'status'], 'user_id_status_index');

// ユニークインデックス
$table->unique('email', 'email_unique');
$table->unique(['year', 'month', 'currency_code'], 'year_month_currency_code_unique');
```

**命名規則**:
- 単一カラム: `{column_name}_index` または `{column_name}_unique`
- 複合インデックス: `{column1}_{column2}_index` または短縮形
- 長すぎる場合は適宜省略（MySQLは64文字制限）

### 6. 主キーの設定

```php
// UUID文字列を主キーにする場合
$table->string('id', 255)->primary()->comment('UUID');

// 自動増分IDを使う場合（adminなど）
$table->id(); // bigIncrements('id')のエイリアス

// 複合主キー
$table->primary(['user_id', 'item_id']);
```

**注意点**:
- TiDBでは主キーの変更ができないため、初期作成時に正しく設定する
- ホットスポット問題を避けるため、TiDBではランダムなUUIDを推奨

## コーディング規約

### ファイル命名規則

```
{YYYY_mm_dd_HHiiss}_{説明的な名前}.php
```

**良い例**:
- `2025_10_22_120000_create_usr_user_profiles.php`
- `2025_10_22_130000_add_level_to_usr_user_profiles.php`
- `2025_10_22_140000_create_index_on_usr_devices.php`

**避けるべき例**:
- `2025_10_22_120000_migration.php` （何をするか不明）
- `2025_10_22_120000_update.php` （何を更新するか不明）

### テーブル命名規則

**基本ルール**:
- DB接頭辞（mst_/mng_/usr_/log_/sys_/adm_）は必須
- 複数形を使用（例: `users`, `messages`, `profiles`）

**例外: i18n系テーブル**:
- 多言語対応テーブルは`_i18n`で終わる（複数形にしない）
- ✅ 正しい: `mng_messages_i18n`, `opr_gachas_i18n`, `mst_units_i18n`
- ❌ 間違い: `mng_messages_i18ns`, `opr_gachas_i18ns`

詳細な命名規則とパターンについては **[naming-conventions.md](naming-conventions.md)** を参照してください。

### クラス構造

```php
<?php

declare(strict_types=1); // PHP 8推奨

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION; // 必要な場合のみ

    public function up(): void
    {
        // マイグレーション処理
    }

    public function down(): void
    {
        // ロールバック処理
    }
};
```

### down()メソッドの実装

必ずロールバック処理を実装してください：

```php
// テーブル作成の場合
public function down(): void
{
    Schema::dropIfExists('usr_example');
}

// カラム追加の場合
public function down(): void
{
    Schema::table('usr_example', function (Blueprint $table) {
        $table->dropColumn(['level', 'exp']);
    });
}

// インデックス追加の場合
public function down(): void
{
    Schema::table('usr_example', function (Blueprint $table) {
        $table->dropIndex('user_id_status_index');
    });
}
```

## TiDB固有の機能

### TTL（Time To Live）設定

ログテーブルなど、古いデータを自動削除したい場合に使用：

```php
use Illuminate\Support\Facades\DB;

public function up(): void
{
    // TiDBかどうかを確認
    if (!$this->shouldRun()) {
        return;
    }

    // created_atから31日後に自動削除
    DB::statement("ALTER TABLE `log_example` TTL = `created_at` + INTERVAL 31 DAY");

    // 1日1回実行
    DB::statement("ALTER TABLE `log_example` TTL_JOB_INTERVAL = '24h'");

    // TTLを有効化
    DB::statement("ALTER TABLE `log_example` TTL_ENABLE = 'ON'");
}

public function down(): void
{
    if (!$this->shouldRun()) {
        return;
    }

    DB::statement("ALTER TABLE `log_example` REMOVE TTL");
}

private function shouldRun(): bool
{
    // TiDB環境でのみ実行（cluster_infoテーブルの存在確認）
    $result = DB::select(
        "SELECT COUNT(*) as count FROM information_schema.tables
         WHERE table_schema = 'information_schema' AND table_name = 'cluster_info'"
    );
    return $result[0]->count > 0;
}
```

**TTL設定のポイント**:
- `shouldRun()`でTiDB環境かチェック（MySQLでエラーを避ける）
- ログテーブルなど大量データが蓄積するテーブルに有効
- INTERVALは日数で指定（30 DAY, 90 DAYなど）
- TTL_JOB_INTERVALで実行頻度を調整（'24h', '1h'など）

### ホットスポット問題の回避

TiDBで自動増分IDを使う場合の注意：

```php
// ❌ 避けるべき（ホットスポット問題）
$table->bigIncrements('id');

// ✅ 推奨（ランダムなUUID）
$table->string('id', 255)->primary()->comment('UUID');

// アプリケーション側でUUID生成
use WonderPlanet\Domain\Currency\Utils\DBUtility;
$id = DBUtility::generateUUID(); // ランダムなUUID v4
```

## トラブルシューティング

### マイグレーションが実行されない

**症状**: `sail artisan migrate`を実行してもマイグレーションが実行されない

**確認ポイント**:
1. ファイル配置場所が正しいか
   ```bash
   ls -la api/database/migrations/
   ls -la api/database/migrations/mst/
   ls -la api/database/migrations/mng/
   ```

2. `$connection`プロパティが正しく設定されているか（mst/mng用）
   ```php
   protected $connection = Database::MST_CONNECTION;
   ```

3. マイグレーション履歴テーブルを確認
   ```bash
   sail artisan migrate:status
   sail artisan migrate:status --database=mst
   ```

4. ファイル名の重複がないか確認
   ```bash
   find api/database/migrations -name "*.php" | sort
   ```

### ロールバックできない

**症状**: `sail artisan migrate:rollback`が失敗する

**原因と対処**:

1. **down()メソッドが未実装**
   - down()メソッドを正しく実装してください

2. **TiDBでPRIMARY KEY変更を試みている**
   - TiDBではPRIMARY KEYの変更不可
   - テーブルを再作成する必要がある場合は、データ移行も考慮

3. **外部キー制約が原因**
   ```php
   // 外部キー制約を一時的に無効化
   DB::statement('SET FOREIGN_KEY_CHECKS=0');
   Schema::dropIfExists('table_name');
   DB::statement('SET FOREIGN_KEY_CHECKS=1');
   ```

### 本番環境で実行できない

**症状**: 本番環境でマイグレーションが実行されない

**対処法**:
```bash
# --forceオプションを使用
sail artisan migrate --force

# ドライランで事前確認
sail artisan migrate --pretend --force
```

**注意**:
- 本番環境では必ずバックアップを取ってから実行
- 可能な限りドライランで確認
- 段階的に実行（DB接続ごと）

### マイグレーションがロックされている

**症状**: 「Another migration process is running」エラー

**対処法**:
```bash
# キャッシュクリア
sail artisan cache:clear

# または手動でロック解除
sail artisan tinker
# >>> Cache::forget('illuminate:cache:migration');
```

## ベストプラクティス

### 1. マイグレーションは小さく保つ

**推奨**:
- 1つのマイグレーションファイルで1つの論理的な変更
- 関連するテーブルをまとめる（例: メインテーブル + 多言語テーブル）

**避けるべき**:
- 無関係な複数テーブルの変更を1ファイルにまとめる
- 大規模なデータ移行を含む（別途データマイグレーション用のコマンドを作成）

### 2. 常にdown()を実装

ロールバック可能にすることで、問題発生時の復旧が容易になります。

```php
// ✅ 良い例
public function down(): void
{
    Schema::dropIfExists('usr_example');
}

// ❌ 避けるべき
public function down(): void
{
    // 何もしない
}
```

### 3. テスト環境で確認

本番適用前に必ずテスト環境で確認してください：

```bash
# 1. ドライランで確認
sail artisan migrate --pretend

# 2. 実行
sail artisan migrate

# 3. ロールバックテスト
sail artisan migrate:rollback

# 4. 再実行テスト
sail artisan migrate
```

### 4. コメントを丁寧に記述

```php
// ✅ 良い例
$table->string('user_id', 255)->comment('ユーザーID（usr_users.id）');
$table->enum('status', ['Active', 'Inactive'])->comment('ステータス: Active=有効, Inactive=無効');

// ❌ 避けるべき
$table->string('user_id', 255)->comment('ID');
$table->enum('status', ['Active', 'Inactive']); // コメントなし
```

### 5. DB接続を意識する

テーブル接頭辞とDB接続の対応を常に確認してください：

```php
// ✅ 正しい組み合わせ
// mst接続 → mst_*, opr_* テーブル
protected $connection = Database::MST_CONNECTION;
Schema::create('mst_units', ...);

// ❌ 間違った組み合わせ
// mst接続なのにusr_テーブル
protected $connection = Database::MST_CONNECTION;
Schema::create('usr_profiles', ...); // これは間違い
```

### 6. 既存コードを参考にする

似たようなテーブル構造のマイグレーションを参考にすると、一貫性が保たれます：

```bash
# mst関連のマイグレーションを検索
ls api/database/migrations/mst/

# 特定のパターンを検索
grep -r "mst_units" api/database/migrations/
```

### 7. マイグレーション実行順序を考慮

外部キーや依存関係がある場合、実行順序を意識してください：

```php
// ✅ 良い例（親テーブル → 子テーブルの順）
public function up(): void
{
    // 先に親テーブル作成
    Schema::create('mng_messages', function (Blueprint $table) {
        $table->string('id')->primary();
        // ...
    });

    // 次に子テーブル作成（外部キー参照）
    Schema::create('mng_messages_i18n', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->string('mng_message_id');
        // ...
    });
}

// down()では逆順で削除
public function down(): void
{
    Schema::dropIfExists('mng_messages_i18n'); // 先に子テーブル
    Schema::dropIfExists('mng_messages');      // 次に親テーブル
}
```

### 8. デフォルト値の設定

NOT NULL制約のカラムには適切なデフォルト値を設定してください：

```php
// ✅ 良い例
$table->integer('level')->unsigned()->default(1)->comment('レベル');
$table->bigInteger('exp')->default(0)->comment('経験値');
$table->boolean('is_active')->default(true)->comment('有効フラグ');

// ❌ 避けるべき（デフォルト値なしでNOT NULL）
$table->integer('level')->unsigned()->comment('レベル'); // エラーになる可能性
```

### 9. カラム長の適切な設定

```php
// ID系
$table->string('id', 255); // UUID用

// ユーザー入力テキスト
$table->string('nickname', 100); // 短めのテキスト
$table->string('email', 255);    // メールアドレス

// 長文
$table->text('description');      // 中程度の長さ
$table->longText('content');      // 非常に長いコンテンツ

// 数値
$table->integer('count')->unsigned(); // 0以上の整数
$table->bigInteger('amount');         // 大きな数値
$table->decimal('price', 20, 6);      // 高精度な小数
```

### 10. Enum型の使用

選択肢が固定の場合はEnum型を使用してください：

```php
$table->enum('status', ['Draft', 'Published', 'Archived'])
    ->default('Draft')
    ->comment('ステータス: Draft=下書き, Published=公開, Archived=アーカイブ');

$table->enum('platform', ['iOS', 'Android', 'Web'])
    ->comment('プラットフォーム');
```

**注意点**:
- 値の追加・変更が頻繁な場合は文字列型を検討
- MySQLでは後からEnumの値を変更するのが面倒

# usr/log/sys接続のマイグレーション実装例

## 概要

- **DB接続**: usr (TiDB), log (TiDB), sys (TiDB)
- **ファイル配置**: `api/database/migrations/` （ルートディレクトリ）
- **$connectionプロパティ**: ❌ **不要**（デフォルトのTiDB接続を使用）
- **テーブル接頭辞**: `usr_*`, `log_*`, `sys_*`

**重要**: すべてのDB接続で共通のルール（timestampTz使用、created_at/updated_atの配置、after()指定など）は [common-rules.md](common-rules.md) を参照してください。

## テーブル作成の実装例

### 例1: log接続 - ログテーブル作成

**ファイル**: `api/database/migrations/2025_04_14_120314_create_log_artwork_fragments_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('log_artwork_fragments', function (Blueprint $table) {
            $table->string('id')->primary()->comment('ID');
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('nginx_request_id', 255)->comment('APIリクエスト単位でNginxにて生成されるユニークID');
            $table->string('request_id', 255)->comment('APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID');
            $table->integer('logging_no')->comment('APIリクエスト中でのログの順番');
            $table->string('mst_artwork_fragment_id', 255)->comment('mst_artwork_fragments.id');
            $table->string('content_type', 255)->comment('原画のかけらを入手したコンテンツのタイプ');
            $table->string('target_id', 255)->comment('原画のかけらを入手したコンテンツ');
            $table->unsignedSmallInteger('is_complete_artwork')->comment('原画が完成したかどうか: 1: 原画が完成した, 0: 原画未完成');
            $table->timestampsTz(); // created_at, updated_at を作成
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_artwork_fragments');
    }
};
```

**ポイント**:
- `$connection` プロパティは**不要**（デフォルト接続を使用）
- `timestampsTz()` ヘルパーでcreated_atとupdated_atを作成可能
- ログテーブルには各種ID（nginx_request_id, request_id, logging_no）を含める
- テーブル名は `log_` 接頭辞 + 複数形（`log_artwork_fragments`）

## テーブル変更の実装例

### 例2: usr接続 - カラム追加

**ファイル**: `api/database/migrations/2025_01_29_115952_add_column_usr_stages.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // 変更前のテーブル構造をコメントで記載すると理解しやすい
    // CREATE TABLE `usr_stages` (
    //     `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `mst_stage_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `clear_status` tinyint NOT NULL,
    //     `clear_count` bigint NOT NULL,
    //     `created_at` timestamp NULL DEFAULT NULL,
    //     `updated_at` timestamp NULL DEFAULT NULL,
    //     PRIMARY KEY (`id`),
    //     UNIQUE KEY `usr_stages_usr_user_id_mst_stage_id_unique` (`usr_user_id`,`mst_stage_id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // 変更内容: clear_countの後に、`clear_time_ms` int unsigned DEFAULT NULL COMMENT 'クリアタイム(ミリ秒)'を追加

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('usr_stages', function (Blueprint $table) {
            $table->unsignedInteger('clear_time_ms')
                ->nullable()
                ->comment('クリアタイム(ミリ秒)')
                ->after('clear_count'); // clear_countの後に追加
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_stages', function (Blueprint $table) {
            $table->dropColumn('clear_time_ms');
        });
    }
};
```

**ポイント**:
- `Schema::table()` を使用してテーブル変更
- `after()` メソッドでカラムの位置を指定
- 変更前のテーブル構造をコメントで記載すると理解しやすい
- down()では追加したカラムを削除

### 例3: usr接続 - 複数カラムの削除

**ファイル**: `api/database/migrations/2025_08_16_044616_remove_mst_avatar_columns_from_usr_user_profiles_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('usr_user_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'mst_avatar_head_id',
                'mst_avatar_body_id',
                'mst_avatar_background_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_user_profiles', function (Blueprint $table) {
            $table->string('mst_avatar_head_id', 255)->nullable()->comment('アバター頭部ID');
            $table->string('mst_avatar_body_id', 255)->nullable()->comment('アバター本体ID');
            $table->string('mst_avatar_background_id', 255)->nullable()->comment('アバター背景ID');
        });
    }
};
```

**ポイント**:
- 複数カラムを一度に削除する場合は配列で指定
- down()でロールバック時にカラムを復元（型や制約も正確に記述）

### 例4: log接続 - インデックス追加

**ファイル**: `api/database/migrations/2025_04_29_132602_add_index_to_log_refund_tables.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('log_refund_bank_paid_coins', function (Blueprint $table) {
            $table->index('usr_user_id');
            $table->index('created_at');
        });

        Schema::table('log_refund_currency_paids', function (Blueprint $table) {
            $table->index('usr_user_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_refund_bank_paid_coins', function (Blueprint $table) {
            $table->dropIndex(['usr_user_id']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('log_refund_currency_paids', function (Blueprint $table) {
            $table->dropIndex(['usr_user_id']);
            $table->dropIndex(['created_at']);
        });
    }
};
```

**ポイント**:
- `index()` メソッドでインデックスを追加
- down()では `dropIndex()` で配列形式でカラム名を指定
- ログテーブルでは検索に使うカラムにインデックスを追加することが多い

### 例5: usr接続 - NULL制約の変更

**ファイル**: `api/database/migrations/2025_07_07_073813_alter_usr_pvps_table_not_nullable.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('usr_pvps', function (Blueprint $table) {
            // NULLを許可していたカラムをNOT NULLに変更
            $table->string('last_opponent_id', 255)->nullable(false)->change();
            $table->unsignedInteger('last_opponent_score')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_pvps', function (Blueprint $table) {
            // NOT NULLをNULL許可に戻す
            $table->string('last_opponent_id', 255)->nullable()->change();
            $table->unsignedInteger('last_opponent_score')->nullable()->change();
        });
    }
};
```

**ポイント**:
- `change()` メソッドで既存カラムの定義を変更
- `nullable(false)` でNOT NULL制約を追加
- `nullable()` または `nullable(true)` でNULL許可に変更

## コマンド実行

### マイグレーション作成

```bash
# glow-serverルートディレクトリから実行

# usr/log/sys用マイグレーション作成（自動的にapi/database/migrations/に作成される）
sail artisan make:migration create_usr_user_profiles_table
sail artisan make:migration create_log_logins_table
sail artisan make:migration add_column_to_usr_stages_table

# テーブル作成用テンプレート付き
sail artisan make:migration create_usr_examples_table --create=usr_examples

# テーブル変更用テンプレート付き
sail artisan make:migration add_status_to_usr_examples_table --table=usr_examples
```

### マイグレーション実行

```bash
# glow-serverルートディレクトリから実行

# すべてのDB接続でマイグレーション実行（usr/log/sys含む）
sail artisan migrate

# usr/log/sys接続を指定して実行（すべてtidb接続）
sail artisan migrate --database=tidb

# 注意: usr, log, sysは個別指定できない（すべて同じtidb接続を使用）

# ドライラン（SQLを確認するだけ）
sail artisan migrate --pretend
sail artisan migrate --database=tidb --pretend

# ステータス確認
sail artisan migrate:status
sail artisan migrate:status --database=tidb
```

## TiDB特有の注意点

usr/log/sys接続はTiDBを使用しているため、以下の点に注意してください：

### ホットスポット問題

- **問題**: 連番IDや時刻ベースのIDは書き込みが集中する（ホットスポット）
- **対策**: UUIDやランダム文字列をPKに使用

```php
// ❌ 避けるべき（ホットスポット発生）
$table->id(); // AUTO_INCREMENT
$table->bigIncrements('id');

// ✅ 推奨（分散書き込み）
$table->string('id', 255)->primary()->comment('UUID');
```

### AUTO_INCREMENTの制限

TiDBではAUTO_INCREMENTに制限があるため、可能な限りUUIDを使用してください。

```php
// アプリケーション側でUUID生成
use Illuminate\Support\Str;

$id = Str::uuid()->toString();
```

## チェックポイント

- [ ] `$connection` プロパティを**設定していないか**（usr/log/sysでは不要）
- [ ] ファイルを `api/database/migrations/` ルートに配置しているか
- [ ] テーブル名に正しい接頭辞（usr_/log_/sys_）を付けているか
- [ ] timestampTz()を使用しているか（timestamps()ヘルパーは使用可能だが非推奨）
- [ ] TiDBの場合、PKにUUIDを使用しているか（AUTO_INCREMENT回避）
- [ ] down()メソッドで適切にロールバック処理を記述しているか

# admin接続のマイグレーション実装例

## 概要

- **DB接続**: admin (MySQL)
- **ファイル配置**: `admin/database/migrations/`
- **$connectionプロパティ**: ❌ **不要**（adminアプリのデフォルト接続）
- **テーブル接頭辞**: `adm_*`

**重要**: すべてのDB接続で共通のルール（timestampTz使用、created_at/updated_atの配置、after()指定など）は [common-rules.md](common-rules.md) を参照してください。

## テーブル作成の実装例

### 例1: admin接続 - 昇格タグ管理テーブル作成

**ファイル**: `admin/database/migrations/2025_04_26_000000_create_adm_promotion_tags_table.php`

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
        Schema::create('adm_promotion_tags', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->text('description')->nullable()->comment('メモ');
            $table->timestampsTz(); // created_at, updated_at を作成

            $table->comment('昇格タグの管理テーブル');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adm_promotion_tags');
    }
};
```

**ポイント**:
- `$connection` プロパティは**不要**（adminアプリのデフォルト接続）
- `timestampsTz()` ヘルパーでcreated_atとupdated_atを作成可能
- テーブルコメントで用途を明記
- テーブル名は `adm_` 接頭辞 + 複数形（`adm_promotion_tags`）

### 例2: admin接続 - 為替レート管理テーブル作成

**ファイル**: `admin/database/migrations/2023_12_27_052102_wp_curency_create_adm_foreign_currency_rates.php`

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
        Schema::create('adm_foreign_currency_rates', function (Blueprint $table) {
            $table->id();
            $table->date('reference_date')->comment('基準日');
            $table->string('currency_code', 3)->comment('通貨コード(ISO 4217)');
            $table->decimal('rate', 10, 4)->comment('為替レート（対JPY）');
            $table->timestampsTz();

            $table->unique(['reference_date', 'currency_code']);
            $table->comment('為替レート管理テーブル');
        });

        Schema::create('adm_foreign_currency_daily_rates', function (Blueprint $table) {
            $table->id();
            $table->date('rate_date')->comment('レート適用日');
            $table->string('currency_code', 3)->comment('通貨コード(ISO 4217)');
            $table->decimal('rate', 10, 4)->comment('為替レート（対JPY）');
            $table->timestampsTz();

            $table->unique(['rate_date', 'currency_code']);
            $table->index('rate_date');
            $table->comment('為替レート（日次）管理テーブル');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adm_foreign_currency_daily_rates');
        Schema::dropIfExists('adm_foreign_currency_rates');
    }
};
```

**ポイント**:
- `id()` メソッドでAUTO_INCREMENT主キー作成（adminはMySQL）
- `decimal()` で精度の高い数値型を使用（為替レート）
- `unique()` で複合ユニーク制約を設定
- 複数テーブルを作成する場合、drop時は逆順で削除

### 例3: admin接続 - ユーザーBAN操作履歴テーブル

**ファイル**: `admin/database/migrations/2024_XX_XX_XXXXXX_create_adm_user_ban_operate_histories_table.php`

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
        Schema::create('adm_user_ban_operate_histories', function (Blueprint $table) {
            $table->id();
            $table->string('usr_user_id', 255)->comment('対象ユーザーID（usr_users.id）');
            $table->enum('operation_type', ['Ban', 'Unban'])->comment('操作種別');
            $table->string('operator_id', 255)->comment('操作者ID（adm_users.id）');
            $table->text('reason')->nullable()->comment('操作理由');
            $table->timestampsTz();

            $table->index('usr_user_id');
            $table->index('operator_id');
            $table->index('created_at');
            $table->comment('ユーザーBAN操作履歴テーブル');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adm_user_ban_operate_histories');
    }
};
```

**ポイント**:
- 履歴テーブルは `_histories` で終わる（複数形）
- `enum()` で操作種別を定義
- 検索に使うカラムにインデックスを追加
- `text()` 型で長文を保存（reason）

## テーブル変更の実装例

### 例4: admin接続 - カラム追加とテーブル作成を同時実行

**ファイル**: `admin/database/migrations/2025_04_26_000000_create_adm_promotion_tags_table.php`

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
        // 新規テーブル作成
        Schema::create('adm_promotion_tags', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->text('description')->nullable()->comment('メモ');
            $table->timestampsTz();

            $table->comment('昇格タグの管理テーブル');
        });

        // 既存テーブルにカラム追加
        Schema::table('adm_informations', function (Blueprint $table) {
            $table->timestampTz('content_change_at')
                ->nullable(false)
                ->comment('お知らせの内容が変更された日時')
                ->after('post_notice_end_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // テーブル削除
        Schema::dropIfExists('adm_promotion_tags');

        // カラム削除
        Schema::table('adm_informations', function (Blueprint $table) {
            $table->dropColumn('content_change_at');
        });
    }
};
```

**ポイント**:
- 1つのマイグレーションで複数の操作を実行可能
- `Schema::create()` と `Schema::table()` を同時に使用
- `nullable(false)` でNOT NULL制約を明示
- down()では逆順で元に戻す

### 例5: admin接続 - カラム型変更（longText）

**ファイル**: `admin/database/migrations/2024_07_22_015059_alter_column_long_text_to_adm_message_distribution_inputs.php`

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
        Schema::table('adm_message_distribution_inputs', function (Blueprint $table) {
            // text型からlongText型に変更
            $table->longText('input_values')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adm_message_distribution_inputs', function (Blueprint $table) {
            // longText型からtext型に戻す
            $table->text('input_values')->change();
        });
    }
};
```

**ポイント**:
- `change()` メソッドでカラム型を変更
- `text()` は最大65,535文字、`longText()` は最大4,294,967,295文字
- down()で元の型に戻せるようにする

### 例6: admin接続 - Google認証情報追加

**ファイル**: `admin/database/migrations/2024_09_04_185814_add_adm_user_table_google_info.php`

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
        Schema::table('adm_users', function (Blueprint $table) {
            $table->string('google_id')->nullable()->unique()->comment('Google ID');
            $table->string('google_email')->nullable()->comment('Googleメールアドレス');
            $table->text('google_avatar')->nullable()->comment('Googleアバター画像URL');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adm_users', function (Blueprint $table) {
            $table->dropColumn(['google_id', 'google_email', 'google_avatar']);
        });
    }
};
```

**ポイント**:
- `unique()` で一意制約を追加
- `nullable()` でNULL許可（Google認証を使わないユーザーもいる）
- 複数カラムを追加する場合も個別に定義
- down()では配列形式で一度に削除可能

## コマンド実行

### マイグレーション作成

```bash
# glow-serverルートディレクトリから実行

# admin用マイグレーション作成（自動的にadmin/database/migrations/に作成される）
sail admin artisan make:migration create_adm_promotion_tags_table

# テーブル作成用テンプレート付き
sail admin artisan make:migration create_adm_examples_table --create=adm_examples

# テーブル変更用テンプレート付き
sail admin artisan make:migration add_reason_to_adm_user_ban_histories_table --table=adm_user_ban_histories
```

### マイグレーション実行

```bash
# glow-serverルートディレクトリから実行

# admin DBのマイグレーション実行
sail admin artisan migrate

# 本番環境で強制実行（確認プロンプトをスキップ）
sail admin artisan migrate --force

# ドライラン（SQLを確認するだけ）
sail admin artisan migrate --pretend

# ステータス確認
sail admin artisan migrate:status

# ロールバック
sail admin artisan migrate:rollback
sail admin artisan migrate:rollback --step=1
```

## admin接続の特徴

### 管理画面専用のDB

- adminアプリケーションで使用する管理画面専用のデータを保存
- お知らせ管理、バナー管理、ユーザー管理操作履歴など
- API側（usr/log/sys）のデータとは分離

### AUTO_INCREMENTの使用

MySQLなので、AUTO_INCREMENT主キーを安全に使用できます。

```php
// ✅ 管理画面では問題なく使用可能
$table->id(); // BIGINT UNSIGNED AUTO_INCREMENT

// または
$table->bigIncrements('id');
```

### i18n対応

管理画面でも多言語対応が必要な場合は、i18nテーブルを作成します。

```php
// ✅ 正しい: i18nテーブルは複数形にしない
Schema::create('adm_informations_i18n', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('adm_information_id')->comment('adm_informations.id');
    $table->enum('language', ['ja', 'en'])->default('ja');
    $table->string('title', 255);
    $table->text('body');
    $table->timestampsTz();

    $table->unique(['adm_information_id', 'language']);
});

// ❌ 間違い: adm_informations_i18ns（i18nsは禁止）
```

## チェックポイント

- [ ] `$connection` プロパティを**設定していないか**（adminでは不要）
- [ ] ファイルを `admin/database/migrations/` に配置しているか
- [ ] テーブル名に `adm_` 接頭辞を付けているか
- [ ] テーブル名は複数形になっているか（i18nテーブル除く）
- [ ] timestampsTz()またはtimestampTz()を使用しているか
- [ ] 管理操作の履歴テーブルには操作者IDと操作日時を含めているか
- [ ] down()メソッドで適切にロールバック処理を記述しているか

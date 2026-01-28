# マイグレーション共通ルール

すべてのDB接続（mst/mng/usr/log/sys/admin）で共通して守るべきルールをまとめています。

## 目次

1. [timestampTz()の使用（必須）](#1-timestamptzの使用必須)
2. [created_at/updated_atは必ず最後の列](#2-created_atupdated_atは必ず最後の列)
3. [カラム追加時のafter()指定（必須）](#3-カラム追加時のafter指定必須)
4. [enum型の使用制限（TiDBでは禁止）](#4-enum型の使用制限tidbでは禁止)
5. [コメントの記述](#5-コメントの記述)
6. [down()メソッドの実装](#6-downメソッドの実装)

---

## 1. timestampTz()の使用（必須）

すべてのDB接続で、timestamp型のカラムには**必ずtimestampTz()を使用**してください。

### ✅ 正しい例

```php
// 個別に定義
$table->timestampTz('created_at');
$table->timestampTz('updated_at');

// timestampsTz()ヘルパーも使用可能（created_at, updated_atを一度に作成）
$table->timestampsTz();

// その他のタイムスタンプカラム
$table->timestampTz('start_at')->comment('開始日時');
$table->timestampTz('expired_at')->comment('有効期限');
```

### ❌ 間違った例

```php
// ❌ timestamps()ヘルパーは使用禁止（timestampが作成されてしまう）
$table->timestamps();

// ❌ timestamp()は使用禁止
$table->timestamp('created_at');
$table->timestamp('updated_at');
```

### 理由

- `timestampTz()`: タイムゾーン情報を含むTIMESTAMP型（推奨）
- `timestamp()`: タイムゾーン情報なしのTIMESTAMP型（非推奨）
- 全DBで統一してtimestampTz()を使用することで、タイムゾーン処理を一貫させる

---

## 2. created_at/updated_atは必ず最後の列

タイムスタンプカラム（created_at、updated_at）は、テーブル定義の**最後の列**として配置してください。

### ✅ 正しい例

```php
Schema::create('mst_units', function (Blueprint $table) {
    $table->string('id', 255)->primary();
    $table->string('name', 255)->comment('ユニット名');
    $table->integer('level')->comment('レベル');
    $table->integer('rarity')->comment('レアリティ');

    // ✅ タイムスタンプは必ず最後
    $table->timestampTz('created_at');
    $table->timestampTz('updated_at');

    // または
    $table->timestampsTz(); // created_at, updated_atを一度に作成
});
```

### ❌ 間違った例

```php
Schema::create('mst_units', function (Blueprint $table) {
    $table->string('id', 255)->primary();
    $table->timestampTz('created_at');    // ❌ 途中にある
    $table->timestampTz('updated_at');    // ❌ 途中にある
    $table->string('name', 255)->comment('ユニット名');
    $table->integer('level')->comment('レベル');
});
```

### 理由

- テーブル構造の可読性向上
- カラム追加時の位置を明確化（created_atの前に追加）
- チーム内での統一規約

---

## 3. カラム追加時のafter()指定（必須）

既存テーブルにカラムを追加する場合、**必ずcreated_atの直前の列を指定してafter()を使用**してください。

### ✅ 正しい例

```php
// 既存テーブル構造:
// id, name, level, created_at, updated_at

Schema::table('mst_units', function (Blueprint $table) {
    // levelカラムの後（created_atの前）に追加
    $table->integer('max_level')->comment('最大レベル')->after('level');
});

// 結果:
// id, name, level, max_level, created_at, updated_at ✅
```

### ❌ 間違った例

```php
// ❌ after()を指定しない
Schema::table('mst_units', function (Blueprint $table) {
    $table->integer('max_level')->comment('最大レベル');
});
// 結果: id, name, level, created_at, updated_at, max_level ❌
// → updated_atの後に追加されてしまう

// ❌ updated_atの後に追加
Schema::table('mst_units', function (Blueprint $table) {
    $table->integer('max_level')->comment('最大レベル')->after('updated_at');
});
// 結果: id, name, level, created_at, updated_at, max_level ❌
```

### after()指定の手順

1. 現在のテーブル構造を確認（`DESCRIBE テーブル名`）
2. created_atの直前のカラム名を特定
3. after('直前のカラム名')を指定してカラム追加

**例: 複数カラムを追加する場合**

```php
// 既存: id, name, level, created_at, updated_at

Schema::table('mst_units', function (Blueprint $table) {
    // 1つ目: levelの後に追加
    $table->integer('max_level')->comment('最大レベル')->after('level');

    // 2つ目: max_levelの後に追加
    $table->integer('min_level')->comment('最小レベル')->after('max_level');
});

// 結果: id, name, level, max_level, min_level, created_at, updated_at ✅
```

### 重要な注意点

- **after()を省略すると、カラムは最後（updated_atの後）に追加される**
- 必ずcreated_atの前に来るように配置すること
- 複数カラムを追加する場合、追加順序に注意

---

## 4. enum型の使用制限（TiDBでは禁止）

enum型はDB接続によって使用可否が異なります。

### ✅ enum型を使用できるDB接続

- **mst** (MySQL)
- **mng** (MySQL)
- **admin** (MySQL)

```php
// ✅ mst/mng/admin では使用可能
Schema::create('mst_units', function (Blueprint $table) {
    $table->enum('rarity', ['N', 'R', 'SR', 'SSR'])->comment('レアリティ');
    $table->enum('status', ['Active', 'Inactive'])->comment('ステータス');
});
```

### ❌ enum型を使用してはいけないDB接続

- **usr** (TiDB)
- **log** (TiDB)
- **sys** (TiDB)

**理由**:
- TiDBテーブルは大量のレコードを持つため、頻繁なALTER TABLEは避けるべき
- enum型に要素を追加するにはALTER TABLEが必要
- ユーザーデータやログデータは増え続けるため、ALTER TABLEの実行が困難

### TiDBでの代替案: varchar(255)を使用

```php
// ✅ usr/log/sys では varchar を使用
Schema::create('usr_user_profiles', function (Blueprint $table) {
    $table->string('id', 255)->primary();
    $table->string('user_id', 255)->unique();
    $table->string('status', 255)->default('Active')->comment('ステータス: Active, Inactive, Banned');
    $table->timestampTz('created_at');
    $table->timestampTz('updated_at');
});
```

**varchar使用時の注意点**:
- コメントに取りうる値を明記する（例: `'ステータス: Active, Inactive, Banned'`）
- アプリケーション層でバリデーションを行う
- 将来的に値を追加してもALTER TABLE不要

### 比較表

| DB接続 | enum型 | 推奨型 | 理由 |
|--------|--------|--------|------|
| mst | ✅ 使用可 | enum | マスターデータ、レコード数が少ない |
| mng | ✅ 使用可 | enum | 運営データ、レコード数が少ない |
| usr | ❌ 禁止 | varchar(255) | ユーザーデータ、大量レコード |
| log | ❌ 禁止 | varchar(255) | ログデータ、大量レコード |
| sys | ❌ 禁止 | varchar(255) | システムデータ、大量レコード |
| admin | ✅ 使用可 | enum | 管理画面データ、レコード数が少ない |

### 実装例の比較

**mst/mng/admin（enum使用）:**
```php
Schema::create('mst_units', function (Blueprint $table) {
    $table->string('id', 255)->primary();
    $table->enum('rarity', ['N', 'R', 'SR', 'SSR'])->comment('レアリティ');
    $table->enum('element', ['Fire', 'Water', 'Wind', 'Earth'])->comment('属性');
    $table->timestampTz('created_at');
    $table->timestampTz('updated_at');
});
```

**usr/log/sys（varchar使用）:**
```php
Schema::create('usr_user_profiles', function (Blueprint $table) {
    $table->string('id', 255)->primary();
    $table->string('user_id', 255)->unique();
    $table->string('status', 255)->default('Active')->comment('ステータス: Active, Inactive, Banned');
    $table->string('membership_type', 255)->default('Free')->comment('会員種別: Free, Premium, VIP');
    $table->timestampTz('created_at');
    $table->timestampTz('updated_at');
});
```

---

## 5. コメントの記述

すべてのカラムに適切なコメントを記述してください。

### ✅ 正しい例

```php
$table->string('id', 255)->primary()->comment('ID');
$table->string('mst_unit_id', 255)->comment('mst_units.id');
$table->integer('level')->default(1)->comment('レベル');
$table->enum('status', ['Active', 'Inactive'])->comment('ステータス');
```

### コメントのガイドライン

- **主キー**: `->comment('ID')` または `->comment('UUID')`
- **外部キー**: `->comment('テーブル名.カラム名')` （例: `mst_units.id`）
- **enum型**: 可能であれば値の意味も記載（例: `'ステータス: Active=有効, Inactive=無効'`）
- **boolean/tinyint**: 0/1の意味を記載（例: `'フラグ: 1=有効, 0=無効'`）
- **日時カラム**: 何の日時かを明記（例: `'配布開始日時'`, `'有効期限'`）

---

## 5. down()メソッドの実装

すべてのマイグレーションで、ロールバック用のdown()メソッドを適切に実装してください。

### テーブル作成の場合

```php
public function up(): void
{
    Schema::create('mst_units', function (Blueprint $table) {
        // テーブル定義
    });
}

public function down(): void
{
    Schema::dropIfExists('mst_units');
}
```

### カラム追加の場合

```php
public function up(): void
{
    Schema::table('mst_units', function (Blueprint $table) {
        $table->integer('max_level')->comment('最大レベル')->after('level');
    });
}

public function down(): void
{
    Schema::table('mst_units', function (Blueprint $table) {
        $table->dropColumn('max_level');
    });
}
```

### 複数カラム削除の場合

```php
public function down(): void
{
    Schema::table('mst_units', function (Blueprint $table) {
        $table->dropColumn(['max_level', 'min_level', 'rarity']);
    });
}
```

### カラム変更の場合

```php
public function up(): void
{
    Schema::table('mst_units', function (Blueprint $table) {
        $table->string('name', 500)->change(); // 255 → 500に変更
    });
}

public function down(): void
{
    Schema::table('mst_units', function (Blueprint $table) {
        $table->string('name', 255)->change(); // 元に戻す
    });
}
```

### インデックス追加の場合

```php
public function up(): void
{
    Schema::table('log_logins', function (Blueprint $table) {
        $table->index('usr_user_id');
        $table->index('created_at');
    });
}

public function down(): void
{
    Schema::table('log_logins', function (Blueprint $table) {
        $table->dropIndex(['usr_user_id']);
        $table->dropIndex(['created_at']);
    });
}
```

### 複数テーブル作成の場合

```php
public function up(): void
{
    Schema::create('mst_units', function (Blueprint $table) { /* ... */ });
    Schema::create('mst_unit_skills', function (Blueprint $table) { /* ... */ });
}

public function down(): void
{
    // ✅ 作成順の逆順で削除
    Schema::dropIfExists('mst_unit_skills');
    Schema::dropIfExists('mst_units');
}
```

---

## チェックリスト

マイグレーション作成時に必ず確認してください：

- [ ] timestampTz()を使用しているか（timestamp()は禁止）
- [ ] created_at/updated_atが最後の列になっているか
- [ ] カラム追加時にafter()を指定しているか
- [ ] **enum型はTiDB（usr/log/sys）で使用していないか** ★重要
- [ ] usr/log/sysでenum相当の値はvarchar(255)を使用しているか
- [ ] varchar使用時、コメントに取りうる値を明記しているか
- [ ] すべてのカラムにコメントを記述しているか
- [ ] down()メソッドを適切に実装しているか
- [ ] 外部キーのコメントは正確か（テーブル名.カラム名）
- [ ] 複数テーブル作成時、down()で逆順に削除しているか

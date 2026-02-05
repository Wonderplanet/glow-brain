# 変更パターン別実装ガイド

このドキュメントでは、glow-schemaの8つの変更パターンそれぞれに対する実装方法を説明します。

## 目次

1. [新規テーブル作成](#1-新規テーブル作成)
2. [カラム追加](#2-カラム追加)
3. [カラム削除](#3-カラム削除)
4. [カラム型・属性変更](#4-カラム型属性変更)
5. [テーブル削除](#5-テーブル削除)
6. [カラム名変更（rename）](#6-カラム名変更rename)
7. [テーブル構造変更（分離・統合）](#7-テーブル構造変更分離統合)
8. [複合的な変更](#8-複合的な変更)

---

## 1. 新規テーブル作成

### 概要

glow-schemaに新しいテーブル定義（YAMLファイル）が追加された場合の実装。

### glow-schemaでの変更例

```yaml
# resources/opr/tables/opr_gacha_histories.yml (新規ファイル)
table:
  name: opr_gacha_histories
  columns:
    - name: id
      type: varchar(255)
      primary: true
    - name: usr_user_id
      type: varchar(255)
    - name: opr_gacha_id
      type: varchar(255)
    - name: played_at
      type: datetime
```

### glow-serverでの実装

#### 1. マイグレーションファイル作成

```php
// api/database/migrations/mng/2025_XX_XX_XXXXXX_create_opr_gacha_histories_table.php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MNG_CONNECTION;

    public function up(): void
    {
        Schema::create('opr_gacha_histories', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('usr_user_id');
            $table->string('opr_gacha_id');
            $table->dateTime('played_at');

            // インデックス（必要に応じて）
            $table->index('usr_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opr_gacha_histories');
    }
};
```

#### 2. Entityクラス作成

```php
// api/app/Domain/Gacha/Entities/GachaHistory.php

declare(strict_types=1);

namespace App\Domain\Gacha\Entities;

use Carbon\CarbonImmutable;

class GachaHistory
{
    public function __construct(
        private string $id,
        private string $usrUserId,
        private string $oprGachaId,
        private CarbonImmutable $playedAt,
    ) {
    }

    // Getters
    public function getId(): string
    {
        return $this->id;
    }

    // ... その他のgetters
}
```

#### 3. Repositoryインターフェース作成

```php
// api/app/Domain/Gacha/Repositories/GachaHistoryRepository.php

namespace App\Domain\Gacha\Repositories;

use App\Domain\Gacha\Entities\GachaHistory;
use Illuminate\Support\Collection;

interface GachaHistoryRepository
{
    public function add(GachaHistory $gachaHistory): void;
    public function findByUserId(string $usrUserId): Collection;
}
```

#### 4. Repository実装クラス作成

```php
// api/app/Infrastructure/Database/Repositories/GachaHistoryRepositoryImpl.php

namespace App\Infrastructure\Database\Repositories;

use App\Domain\Gacha\Entities\GachaHistory;
use App\Domain\Gacha\Repositories\GachaHistoryRepository;

class GachaHistoryRepositoryImpl implements GachaHistoryRepository
{
    // 実装
}
```

### チェックリスト

- [ ] マイグレーションファイルが作成されている
- [ ] Entityクラスが作成されている
- [ ] Repositoryインターフェースが作成されている
- [ ] Repository実装クラスが作成されている
- [ ] ServiceProviderに登録されている
- [ ] 必要に応じてResourceクラスが作成されている

---

## 2. カラム追加

### 概要

既存テーブルに新しいカラムが追加された場合の実装。

### glow-schemaでの変更例

```yaml
# resources/mst/tables/mst_enemy_characters.yml
table:
  columns:
    # ... 既存カラム
+   - name: is_phantomized
+     type: tinyint
+     default: 0
+     comment: 'プレイアブルキャラの敵化専用表現用'
```

### glow-serverでの実装

#### 1. マイグレーションファイル作成

```php
// api/database/migrations/mst/2025_XX_XX_add_is_phantomized_to_mst_enemy_characters.php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    public function up(): void
    {
        Schema::table('mst_enemy_characters', function (Blueprint $table) {
            $table->tinyInteger('is_phantomized')
                ->default(0)
                ->comment('プレイアブルキャラの敵化専用表現用')
                ->after('asset_key');  // 追加位置を指定
        });
    }

    public function down(): void
    {
        Schema::table('mst_enemy_characters', function (Blueprint $table) {
            $table->dropColumn('is_phantomized');
        });
    }
};
```

#### 2. Entityクラス更新

```php
// api/app/Domain/Master/Entities/MstEnemyCharacter.php

class MstEnemyCharacter
{
    public function __construct(
        // ... 既存プロパティ
        private string $assetKey,
+       private int $isPhantomized,  // ← 追加
    ) {
    }

+   public function getIsPhantomized(): int
+   {
+       return $this->isPhantomized;
+   }
}
```

#### 3. Resourceクラス更新（APIレスポンスに含む場合）

```php
// api/app/Domain/Master/Resources/MstEnemyCharacterResource.php

public function toArray(): array
{
    return [
        // ... 既存フィールド
+       'isPhantomized' => $this->isPhantomized,
    ];
}
```

### 重要な注意点

#### ✅ 正しい: after()で追加位置を指定

```php
$table->tinyInteger('is_phantomized')
    ->default(0)
    ->after('asset_key');  // ← 追加位置を明示
```

#### ❌ 間違い: 追加位置を指定しない

```php
$table->tinyInteger('is_phantomized')
    ->default(0);  // ← テーブル末尾に追加されてしまう
```

**理由**: YAMLスキーマではカラムの順序が重要。クライアントやマスタデータCSVとの整合性を保つため、`after()`で位置を明示する。

### チェックリスト

- [ ] マイグレーションで`after()`を使用している
- [ ] `default`値が設定されている（NULL許可でない場合）
- [ ] Entityクラスにプロパティとgetterが追加されている
- [ ] Resourceクラスが更新されている（必要な場合）
- [ ] down()でカラム削除が実装されている

---

## 3. カラム削除

### 概要

既存テーブルから不要なカラムが削除された場合の実装。

### glow-schemaでの変更例

```yaml
# resources/mst/tables/mst_units.yml
table:
  columns:
-   - name: bounding_range_front
-     type: double(8,2)
-   - name: bounding_range_back
-     type: double(8,2)
```

### glow-serverでの実装

#### 1. マイグレーションファイル作成

```php
// api/database/migrations/mst/2025_XX_XX_drop_bounding_range_from_mst_units.php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    public function up(): void
    {
        Schema::table('mst_units', function (Blueprint $table) {
            $table->dropColumn('bounding_range_front');
            $table->dropColumn('bounding_range_back');
        });
    }

    public function down(): void
    {
        Schema::table('mst_units', function (Blueprint $table) {
            // ロールバック時に元に戻せるようにカラムを再作成
            $table->double('bounding_range_front', 8, 2)
                ->after('mst_unit_ability_id1');
            $table->double('bounding_range_back', 8, 2)
                ->after('bounding_range_front');
        });
    }
};
```

#### 2. Entityクラス更新

```php
// api/app/Domain/Master/Entities/MstUnit.php

class MstUnit
{
    public function __construct(
        // ... 既存プロパティ
        private string $mstUnitAbilityId1,
-       private float $boundingRangeFront,  // ← 削除
-       private float $boundingRangeBack,   // ← 削除
        private int $isEncyclopediaSpecialAttackPositionRight,
    ) {
    }

-   public function getBoundingRangeFront(): float  // ← 削除
-   {
-       return $this->boundingRangeFront;
-   }
-
-   public function getBoundingRangeBack(): float  // ← 削除
-   {
-       return $this->boundingRangeBack;
-   }
}
```

#### 3. Resourceクラス更新

```php
// api/app/Domain/Master/Resources/MstUnitResource.php

public function toArray(): array
{
    return [
        // ... 既存フィールド
-       'boundingRangeFront' => $this->boundingRangeFront,  // ← 削除
-       'boundingRangeBack' => $this->boundingRangeBack,    // ← 削除
    ];
}
```

### 重要な注意点

#### ✅ 正しい: down()でカラムを正確に再作成

```php
public function down(): void
{
    Schema::table('mst_units', function (Blueprint $table) {
        $table->double('bounding_range_front', 8, 2)
            ->after('mst_unit_ability_id1');  // 元の位置に復元
    });
}
```

#### ❌ 間違い: down()を実装しない

```php
public function down(): void
{
    // 何もしない - ロールバックできない!
}
```

**理由**: マイグレーションのロールバックが必要な場合に備えて、down()で正確に復元できるようにする。

### チェックリスト

- [ ] マイグレーションでカラムが削除されている
- [ ] down()でカラムが正確に再作成される
- [ ] Entityクラスからプロパティとgetterが削除されている
- [ ] Resourceクラスからフィールドが削除されている
- [ ] 削除カラムを使用しているコードがないか確認済み

---

## 4. カラム型・属性変更

### 概要

カラムのデータ型や属性（nullable、defaultなど）が変更された場合の実装。

### glow-schemaでの変更例

```yaml
# resources/mst/tables/mst_units.yml
table:
  columns:
    - name: attack_range_type
-     type: enum('Short','Middle','Long')
+     type: varchar(255)
```

### glow-serverでの実装

#### 1. マイグレーションファイル作成

```php
// api/database/migrations/mst/2025_XX_XX_change_attack_range_type_to_varchar_in_mst_units.php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    public function up(): void
    {
        Schema::table('mst_units', function (Blueprint $table) {
            $table->string('attack_range_type', 255)
                ->change();  // ← 既存カラムの型変更
        });
    }

    public function down(): void
    {
        // enum型に戻す
        DB::statement("ALTER TABLE mst_units MODIFY COLUMN attack_range_type ENUM('Short','Middle','Long') NOT NULL");
    }
};
```

#### 2. Entityクラス更新

```php
// api/app/Domain/Master/Entities/MstUnit.php

class MstUnit
{
    public function __construct(
        // ... 既存プロパティ
-       private AttackRangeType $attackRangeType,  // ← enum型
+       private string $attackRangeType,            // ← string型
    ) {
    }

-   public function getAttackRangeType(): AttackRangeType
+   public function getAttackRangeType(): string
    {
        return $this->attackRangeType;
    }
}
```

### 重要な注意点

#### ✅ 正しい: change()メソッドを使用

```php
$table->string('attack_range_type', 255)->change();
```

#### ❌ 間違い: 新しいカラムとして追加

```php
$table->string('attack_range_type_new', 255);  // 別カラムになる
```

### よくあるパターン

#### enum → varchar

```php
$table->string('column_name', 255)->change();
```

#### nullable追加

```php
$table->integer('column_name')->nullable()->change();
```

#### default値変更

```php
$table->integer('column_name')->default(1)->change();
```

### チェックリスト

- [ ] `change()`メソッドを使用している
- [ ] down()で元の型に戻せる
- [ ] Entityクラスの型が更新されている
- [ ] 型変更によるコンパイルエラーがない

---

## 5. テーブル削除

### 概要

不要になったテーブルが削除された場合の実装。

### glow-schemaでの変更例

```yaml
# resources/mst/tables/mst_stage_limit_statuses.yml が削除される
```

### glow-serverでの実装

#### 1. マイグレーションファイル作成

```php
// api/database/migrations/mst/2025_XX_XX_drop_mst_stage_limit_statuses.php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    public function up(): void
    {
        // テーブル削除
        Schema::dropIfExists('mst_stage_limit_statuses');

        // 関連する外部キー制約やカラムも削除
        Schema::table('mst_stages', function (Blueprint $table) {
            $table->dropColumn('mst_stage_limit_status_id');
        });
    }

    public function down(): void
    {
        // ロールバック時にテーブルを再作成
        Schema::create('mst_stage_limit_statuses', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->bigInteger('release_key')->default(1);
            $table->enum('only_rarity', ['N', 'R', 'SR', 'SSR', 'UR'])->nullable();
            $table->enum('over_rarity', ['N', 'R', 'SR', 'SSR', 'UR'])->nullable();
            $table->integer('over_summon_cost')->nullable();
            $table->integer('under_summon_cost')->nullable();
            $table->string('mst_series_ids');
        });

        Schema::table('mst_stages', function (Blueprint $table) {
            $table->string('mst_stage_limit_status_id')->default('');
        });
    }
};
```

#### 2. 関連ファイルの削除

```bash
# Entityクラスを削除
rm api/app/Domain/Master/Entities/MstStageLimitStatus.php

# Repositoryを削除（存在する場合）
rm api/app/Domain/Master/Repositories/MstStageLimitStatusRepository.php
rm api/app/Infrastructure/Database/Repositories/MstStageLimitStatusRepositoryImpl.php

# Resourceを削除（存在する場合）
rm api/app/Domain/Master/Resources/MstStageLimitStatusResource.php
```

#### 3. ServiceProviderの更新

```php
// api/app/Providers/RepositoryServiceProvider.php

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ... 他のバインド
-       $this->app->bind(
-           MstStageLimitStatusRepository::class,
-           MstStageLimitStatusRepositoryImpl::class
-       );
    }
}
```

### 重要な注意点

#### ✅ 正しい: dropIfExists()を使用

```php
Schema::dropIfExists('mst_stage_limit_statuses');  // テーブルが存在しなくてもエラーにならない
```

#### ❌ 間違い: drop()を使用

```php
Schema::drop('mst_stage_limit_statuses');  // テーブルが存在しないとエラーになる
```

### チェックリスト

- [ ] `dropIfExists()`を使用している
- [ ] 関連するカラムや外部キー制約も削除している
- [ ] down()でテーブルを正確に再作成できる
- [ ] Entity/Model/Repositoryファイルが削除されている
- [ ] ServiceProviderから登録が削除されている
- [ ] 削除テーブルを使用しているコードがないか確認済み

---

## 6. カラム名変更（rename）

### 概要

カラム名がリネームされた場合の実装。

### glow-schemaでの変更例

```yaml
# resources/mst/tables/mst_event_display_units_i18n.yml
table:
  columns:
-   - name: serif1
+   - name: speech_balloon_text1
-   - name: serif2
+   - name: speech_balloon_text2
```

### glow-serverでの実装

#### 1. マイグレーションファイル作成

```php
// api/database/migrations/mst/2025_XX_XX_rename_serif_to_speech_balloon_text_in_mst_event_display_units_i18n.php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    public function up(): void
    {
        Schema::table('mst_event_display_units_i18n', function (Blueprint $table) {
            $table->renameColumn('serif1', 'speech_balloon_text1');
            $table->renameColumn('serif2', 'speech_balloon_text2');
            $table->renameColumn('serif3', 'speech_balloon_text3');
        });
    }

    public function down(): void
    {
        Schema::table('mst_event_display_units_i18n', function (Blueprint $table) {
            $table->renameColumn('speech_balloon_text1', 'serif1');
            $table->renameColumn('speech_balloon_text2', 'serif2');
            $table->renameColumn('speech_balloon_text3', 'serif3');
        });
    }
};
```

#### 2. Entityクラス更新

```php
// api/app/Domain/Master/Entities/MstEventDisplayUnitI18n.php

class MstEventDisplayUnitI18n
{
    public function __construct(
        // ... 既存プロパティ
-       private string $serif1,
-       private string $serif2,
-       private string $serif3,
+       private string $speechBalloonText1,
+       private string $speechBalloonText2,
+       private string $speechBalloonText3,
    ) {
    }

-   public function getSerif1(): string
+   public function getSpeechBalloonText1(): string
    {
-       return $this->serif1;
+       return $this->speechBalloonText1;
    }

    // 他のgettersも同様に更新
}
```

#### 3. Resourceクラス更新

```php
// api/app/Domain/Master/Resources/MstEventDisplayUnitI18nResource.php

public function toArray(): array
{
    return [
        // ... 既存フィールド
-       'serif1' => $this->serif1,
-       'serif2' => $this->serif2,
-       'serif3' => $this->serif3,
+       'speechBalloonText1' => $this->speechBalloonText1,
+       'speechBalloonText2' => $this->speechBalloonText2,
+       'speechBalloonText3' => $this->speechBalloonText3,
    ];
}
```

### 重要な注意点

#### ✅ 正しい: renameColumn()を使用

```php
$table->renameColumn('old_name', 'new_name');
```

#### ❌ 間違い: カラム削除+追加で対応

```php
$table->dropColumn('old_name');
$table->string('new_name');  // データが失われる!
```

**理由**: `renameColumn()`を使えばデータを保持したままカラム名を変更できる。削除+追加ではデータが失われる。

### チェックリスト

- [ ] `renameColumn()`を使用している
- [ ] down()で元の名前に戻せる
- [ ] Entityクラスのプロパティ名が更新されている
- [ ] Entityクラスのgetter名が更新されている
- [ ] Resourceクラスのフィールド名が更新されている
- [ ] 旧カラム名を使用しているコードがないか確認済み

---

## 7. テーブル構造変更（分離・統合）

### 概要

テーブルの分離（1つのテーブルを複数に分割）や統合（複数テーブルを1つにまとめる）を行う場合の実装。

### glow-schemaでの変更例

```yaml
# 例: mst_client_pathsテーブルを削除し、
# データをopr_in_game_noticesに移動
```

### glow-serverでの実装

#### 1. マイグレーションファイル作成

```php
// api/database/migrations/mng/2025_XX_XX_merge_mst_client_paths_to_opr_in_game_notices.php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MNG_CONNECTION;

    public function up(): void
    {
        // 移動先テーブルにカラム追加
        Schema::table('opr_in_game_notices', function (Blueprint $table) {
            $table->string('destination_type')->default('');
            $table->string('destination_path')->default('');
            $table->string('destination_path_detail')->default('');
        });

        // 元のテーブルを削除
        Schema::dropIfExists('mst_client_paths');
    }

    public function down(): void
    {
        // 元のテーブルを再作成
        Schema::create('mst_client_paths', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('path_type');
            $table->string('path_detail');
        });

        // 追加したカラムを削除
        Schema::table('opr_in_game_notices', function (Blueprint $table) {
            $table->dropColumn(['destination_type', 'destination_path', 'destination_path_detail']);
        });
    }
};
```

#### 2. Entity/Repositoryの更新

統合先のEntityクラスにプロパティを追加し、統合元のEntity/Repositoryを削除。

### チェックリスト

- [ ] データ移行ロジックが必要か確認済み
- [ ] 移行元テーブルのデータが不要か確認済み
- [ ] Entityクラスが適切に更新/削除されている
- [ ] Repositoryが適切に更新/削除されている

---

## 8. 複合的な変更

### 概要

複数の変更パターンが組み合わさった場合の実装。

### 例: API削除 + テーブル削除 + 新規テーブル追加

glow-schema PR #461: GetStoreInfo削除とusrStoreInfo追加

#### 実装手順

1. 削除対応（テーブル削除パターン適用）
2. 新規作成対応（新規テーブル作成パターン適用）
3. API変更対応（Resourceファイル更新）

#### マイグレーションファイル

複数の変更を1つのマイグレーションファイルにまとめるか、複数ファイルに分割するかを判断：

**1つにまとめる場合:**
```php
public function up(): void
{
    // 1. 古いテーブル削除
    Schema::dropIfExists('old_table');

    // 2. 新しいテーブル作成
    Schema::create('new_table', function (Blueprint $table) {
        // ...
    });

    // 3. 既存テーブルのカラム追加
    Schema::table('existing_table', function (Blueprint $table) {
        $table->string('new_column');
    });
}
```

**分割する場合:**
- より明確で理解しやすい
- ロールバックが細かく制御できる
- 推奨: 関連性が低い変更は分割

### チェックリスト

- [ ] 各変更パターンが適切に実装されている
- [ ] マイグレーションの実行順序が適切
- [ ] down()で全ての変更が正確に戻せる
- [ ] 依存関係が考慮されている

---

## まとめ

各パターンの実装方法を理解したら、[examples/](../examples/) ディレクトリで実際のPR実装例を確認してください。

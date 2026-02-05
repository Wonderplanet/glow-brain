# 実装例: カラム追加・削除・変更

このドキュメントでは、カラムの追加、削除、名前変更の実際のPR実装例を紹介します。

## 目次

1. [PR #1380: カラム追加（is_phantomized）](#pr-1380-column-add)
2. [PR #875: カラム削除（bounding_range）](#pr-875-column-delete)
3. [PR #999: カラム名変更（serif → speechBalloonText)](#pr-999-column-rename)

---

## PR #1380: カラム追加（is_phantomized） {#pr-1380-column-add}

- **glow-server PR**: https://github.com/Wonderplanet/glow-server/pull/1380
- **実装内容**: mst_enemy_charactersテーブルにis_phantomizedカラムを追加

### マイグレーションファイル

**ファイル**: `api/database/migrations/mst/2025_07_03_023213_add_is_phantomized_to_mst_enemy_characters.php`

```php
<?php

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
                ->after('asset_key');  // ← 重要: 追加位置を指定
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

### 命名規則

```
パターン: add_{column_name}_to_{table_name}

例:
- add_is_phantomized_to_mst_enemy_characters
- add_animation_speed_to_mst_manga_animations
- add_boss_bgm_asset_key_to_mst_in_games
```

### 重要なポイント

#### ✅ after()で追加位置を指定

```php
$table->tinyInteger('is_phantomized')
    ->default(0)
    ->after('asset_key');  // ← YAMLスキーマの順序に合わせる
```

#### ✅ default値を設定（NOT NULL の場合）

```php
$table->tinyInteger('is_phantomized')
    ->default(0)  // ← 既存データのため必須
```

理由: 既存レコードがある場合、defaultを指定しないとマイグレーションエラーになる。

#### ✅ down()でカラム削除

```php
public function down(): void
{
    Schema::table('mst_enemy_characters', function (Blueprint $table) {
        $table->dropColumn('is_phantomized');
    });
}
```

### カラム追加のチェックリスト

- [ ] `after()`で追加位置を指定している
- [ ] `default`値が設定されている（NOT NULL の場合）
- [ ] `comment`が付いている（YAMLに記載がある場合）
- [ ] down()でカラム削除が実装されている
- [ ] Entityクラスにプロパティが追加されている
- [ ] Resourceクラスが更新されている（必要な場合）

---

## PR #875: カラム削除（bounding_range） {#pr-875-column-delete}

- **glow-server PR**: https://github.com/Wonderplanet/glow-server/pull/875
- **glow-schema PR**: https://github.com/Wonderplanet/glow-schema/pull/310
- **実装内容**: MstUnitとMstEnemyStageParameterからboundingRangeFront、boundingRangeBackカラムを削除

### マイグレーションファイル

**ファイル**: `api/database/migrations/mst/2025_02_04_060219_drop_bounding_range_columns.php`

```php
<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    public function up(): void
    {
        // MstEnemyStageParameterからboundingRangeFront, boundingRangeBack列を削除
        Schema::table('mst_enemy_stage_parameters', function (Blueprint $table) {
            $table->dropColumn('bounding_range_front');
            $table->dropColumn('bounding_range_back');
        });

        // MstUnitからboundingRangeFront,boundingRangeBack列を削除
        Schema::table('mst_units', function (Blueprint $table) {
            $table->dropColumn('bounding_range_front');
            $table->dropColumn('bounding_range_back');
        });
    }

    public function down(): void
    {
        // ロールバック時に元に戻せるようにカラムを再作成
        Schema::table('mst_enemy_stage_parameters', function (Blueprint $table) {
            $table->double('bounding_range_front', 8, 2)
                ->after('min_attack_power');
            $table->double('bounding_range_back', 8, 2)
                ->after('bounding_range_front');
        });

        Schema::table('mst_units', function (Blueprint $table) {
            $table->double('bounding_range_front', 8, 2)
                ->after('mst_unit_ability_id1');
            $table->double('bounding_range_back', 8, 2)
                ->after('bounding_range_front');
        });
    }
};
```

### 命名規則

```
パターン: drop_{column_name}_from_{table_name}

例:
- drop_bounding_range_from_mst_units
- drop_serif_from_mst_event_display_units_i18n

複数テーブルにまたがる場合:
- drop_bounding_range_columns（共通カラム名）
- remove_unused_columns（汎用的な説明）
```

### 重要なポイント

#### ✅ 複数カラムを一度に削除

```php
$table->dropColumn(['column1', 'column2']);  // 配列で指定可能
```

または

```php
$table->dropColumn('column1');
$table->dropColumn('column2');
```

#### ✅ down()で正確に復元

```php
public function down(): void
{
    Schema::table('mst_units', function (Blueprint $table) {
        // 元の型、精度、位置を正確に再現
        $table->double('bounding_range_front', 8, 2)
            ->after('mst_unit_ability_id1');  // 元の位置
    });
}
```

**重要**: ロールバック時のデータ損失を防ぐため、down()は正確に実装する。

### カラム削除のチェックリスト

- [ ] `dropColumn()`を使用している
- [ ] down()でカラムが正確に再作成される
- [ ] 元のカラムの型、精度、位置が正確
- [ ] Entityクラスからプロパティが削除されている
- [ ] Resourceクラスからフィールドが削除されている
- [ ] 削除カラムを使用しているコードがないか確認済み
- [ ] 関連するテストが更新されている

---

## PR #999: カラム名変更（serif → speechBalloonText） {#pr-999-column-rename}

- **glow-server PR**: https://github.com/Wonderplanet/glow-server/pull/999
- **glow-schema PR**: https://github.com/Wonderplanet/glow-schema/pull/350
- **実装内容**: mst_event_display_units_i18nテーブルのserif1/2/3をspeech_balloon_text1/2/3に変更

### マイグレーションファイル

**ファイル**: `api/database/migrations/mst/2025_03_14_061628_alter_mst_event_display_unit_i18n.php`

```php
<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    public function up(): void
    {
        // rename serif > speech_balloon_text
        Schema::table('mst_event_display_units_i18n', function (Blueprint $table) {
            $table->renameColumn('serif1', 'speech_balloon_text1');
            $table->renameColumn('serif2', 'speech_balloon_text2');
            $table->renameColumn('serif3', 'speech_balloon_text3');
        });
    }

    public function down(): void
    {
        // rename speech_balloon_text > serif
        Schema::table('mst_event_display_units_i18n', function (Blueprint $table) {
            $table->renameColumn('speech_balloon_text1', 'serif1');
            $table->renameColumn('speech_balloon_text2', 'serif2');
            $table->renameColumn('speech_balloon_text3', 'serif3');
        });
    }
};
```

### Entityクラス更新

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

### Resourceクラス更新

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

### 命名規則

```
パターン: rename_{old_name}_to_{new_name}_in_{table_name}

例:
- rename_serif_to_speech_balloon_text_in_mst_event_display_units_i18n
- rename_attack_range_type_in_mst_units

または:

alter_{table_name}  （複数の変更を含む場合）
```

### 重要なポイント

#### ✅ renameColumn()を使用

```php
$table->renameColumn('old_name', 'new_name');
```

#### ❌ 間違い: カラム削除+追加で対応

```php
// これはNG! データが失われる
$table->dropColumn('old_name');
$table->string('new_name');
```

**理由**: `renameColumn()`を使えばデータを保持したままカラム名を変更できる。削除+追加ではデータが失われる。

#### データベース命名規則の変換

PHPではキャメルケース、DBではスネークケースを使用：

```
PHP (Entity/Resource):  speechBalloonText1
DB (Migration):         speech_balloon_text1
```

### カラム名変更のチェックリスト

- [ ] `renameColumn()`を使用している
- [ ] down()で元の名前に戻せる
- [ ] Entityクラスのプロパティ名が更新されている
- [ ] Entityクラスのgetter名が更新されている
- [ ] Resourceクラスのフィールド名が更新されている
- [ ] 旧カラム名を使用しているコードがないか確認済み
- [ ] キャメルケース/スネークケースの変換が正しい

---

## まとめ

### カラム操作の基本原則

| 操作 | メソッド | データ保持 | 注意点 |
|------|---------|-----------|--------|
| **追加** | `$table->type()->after()` | N/A | `after()`で位置指定、`default`設定 |
| **削除** | `$table->dropColumn()` | 削除される | down()で正確に復元 |
| **名前変更** | `$table->renameColumn()` | **保持される** | 削除+追加は禁止 |
| **型変更** | `$table->type()->change()` | 保持される | enum→varcharなど |

### よくある間違い

#### ❌ 追加位置を指定しない

```php
$table->tinyInteger('new_column')->default(0);
// → テーブル末尾に追加されてしまう
```

#### ❌ renameの代わりに削除+追加

```php
$table->dropColumn('old_name');
$table->string('new_name');
// → データが失われる!
```

#### ❌ down()を実装しない

```php
public function down(): void
{
    // 何もしない - ロールバックできない!
}
```

### ベストプラクティス

1. **常にdown()を実装**: ロールバック可能にする
2. **after()で位置指定**: YAMLスキーマの順序に合わせる
3. **renameColumn()を使う**: データを保持する
4. **default値を設定**: 既存データへの影響を考慮
5. **comment追加**: YAMLに記載があれば反映

詳細な実装パターンは [patterns.md](../guides/patterns.md) を参照してください。

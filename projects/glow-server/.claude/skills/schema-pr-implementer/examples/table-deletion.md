# 実装例: テーブル削除

このドキュメントでは、テーブル削除の実際のPR実装例を紹介します。

## PR #833: MstStageLimitStatus テーブル削除 {#pr-833}

- **glow-server PR**: https://github.com/Wonderplanet/glow-server/pull/833
- **glow-schema PR**: https://github.com/Wonderplanet/glow-schema/pull/288
- **実装内容**: 不要になったMstStageLimitStatusテーブルの削除

### glow-schema PR #288の変更内容

```
削除対象:
- resources/mst/tables/mst_stage_limit_statuses.yml

理由:
- 特別ルール関連機能でMstStageLimitStatusが不要になった
- 関連するクライアント実装も削除済み
```

### マイグレーションファイル

**ファイル**: `api/database/migrations/mst/2025_01_10_050858_drop_mst_stage_limit_statuses.php`

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

        // 参照カラムも復元
        Schema::table('mst_stages', function (Blueprint $table) {
            $table->string('mst_stage_limit_status_id')->default('');
        });
    }
};
```

### 命名規則

```
パターン: drop_{table_name}

例:
- drop_mst_stage_limit_statuses
- drop_mst_client_paths
- drop_old_user_table
```

### 関連ファイルの削除

テーブル削除時は、関連するファイルも削除する必要があります：

```bash
# 1. Entityクラスを削除
rm api/app/Domain/Master/Entities/MstStageLimitStatus.php

# 2. Repositoryインターフェースを削除（存在する場合）
rm api/app/Domain/Master/Repositories/MstStageLimitStatusRepository.php

# 3. Repository実装クラスを削除（存在する場合）
rm api/app/Infrastructure/Database/Repositories/MstStageLimitStatusRepositoryImpl.php

# 4. Resourceクラスを削除（存在する場合）
rm api/app/Domain/Master/Resources/MstStageLimitStatusResource.php

# 5. Factoryクラスを削除（存在する場合）
rm api/database/factories/MstStageLimitStatusFactory.php
```

### ServiceProviderの更新

Repository登録を削除：

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

### 重要なポイント

#### ✅ dropIfExists()を使用

```php
Schema::dropIfExists('mst_stage_limit_statuses');
```

**理由**: テーブルが存在しない場合でもエラーにならない。安全な実装。

#### ❌ drop()を使用

```php
Schema::drop('mst_stage_limit_statuses');  // テーブルが存在しないとエラー
```

#### ✅ 関連カラムも削除

```php
// 他のテーブルからの参照カラムも削除
Schema::table('mst_stages', function (Blueprint $table) {
    $table->dropColumn('mst_stage_limit_status_id');
});
```

#### ✅ down()で正確に復元

```php
public function down(): void
{
    // テーブル構造を正確に再現
    Schema::create('mst_stage_limit_statuses', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->bigInteger('release_key')->default(1);
        $table->enum('only_rarity', ['N', 'R', 'SR', 'SSR', 'UR'])->nullable();
        // ... 全てのカラムを正確に復元
    });
}
```

### テーブル削除のチェックリスト

#### マイグレーション
- [ ] `dropIfExists()`を使用している
- [ ] 関連するカラムや外部キー制約も削除している
- [ ] down()でテーブルが正確に再作成できる
- [ ] down()で関連カラムも復元している

#### コード
- [ ] Entity/Modelファイルが削除されている
- [ ] Repositoryファイルが削除されている（存在する場合）
- [ ] Resourceファイルが削除されている（存在する場合）
- [ ] Factoryファイルが削除されている（存在する場合）
- [ ] ServiceProviderから登録が削除されている

#### 確認
- [ ] 削除テーブルを使用しているコードがないか確認済み
- [ ] 関連するテストが削除/更新されている
- [ ] マイグレーションが正常に実行できる
- [ ] ロールバックが正常に実行できる

---

## PR #357: テーブル統合（mst_client_paths削除） {#pr-357}

- **glow-server PR**: https://github.com/Wonderplanet/glow-server/pull/357
- **glow-schema PR**: https://github.com/Wonderplanet/glow-schema/pull/114
- **実装内容**: mst_client_pathsテーブルを削除し、遷移先情報をopr_in_game_noticesに移動

### テーブル統合のパターン

このPRは「テーブル削除」と「カラム追加」の組み合わせです。

### マイグレーションファイル

```php
<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MNG_CONNECTION;

    public function up(): void
    {
        // 1. 移動先テーブルにカラム追加
        Schema::table('opr_in_game_notices', function (Blueprint $table) {
            $table->string('destination_type')->default('');
            $table->string('destination_path')->default('');
            $table->string('destination_path_detail')->default('');
        });

        // 2. 元のテーブルを削除
        Schema::dropIfExists('mst_client_paths');
    }

    public function down(): void
    {
        // 1. 元のテーブルを再作成
        Schema::create('mst_client_paths', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('path_type');
            $table->string('path_detail');
        });

        // 2. 追加したカラムを削除
        Schema::table('opr_in_game_notices', function (Blueprint $table) {
            $table->dropColumn(['destination_type', 'destination_path', 'destination_path_detail']);
        });
    }
};
```

### データ移行が必要な場合

テーブル削除前に既存データを移行する必要がある場合：

```php
public function up(): void
{
    // 1. 移動先テーブルにカラム追加
    Schema::table('opr_in_game_notices', function (Blueprint $table) {
        $table->string('destination_type')->default('');
        $table->string('destination_path')->default('');
    });

    // 2. データ移行（必要な場合）
    DB::table('opr_in_game_notices')
        ->join('mst_client_paths', ...)
        ->update([...]);

    // 3. 元のテーブルを削除
    Schema::dropIfExists('mst_client_paths');
}
```

**注意**: データ移行ロジックが複雑な場合は、別のマイグレーションファイルに分割することを推奨。

### テーブル統合のチェックリスト

- [ ] 統合先テーブルにカラムが追加されている
- [ ] データ移行ロジックが実装されている（必要な場合）
- [ ] 元のテーブルが削除されている
- [ ] down()で元の状態に戻せる
- [ ] Entity/Repositoryが適切に更新されている
- [ ] 統合元のEntity/Repositoryが削除されている

---

## よくある間違い

### ❌ down()を実装しない

```php
public function down(): void
{
    // 何もしない - ロールバックできない!
}
```

**理由**: 本番環境でロールバックが必要になった場合に対応できない。

### ❌ 関連カラムを削除し忘れ

```php
public function up(): void
{
    Schema::dropIfExists('mst_stage_limit_statuses');
    // mst_stages.mst_stage_limit_status_id が残ってしまう!
}
```

**理由**: 参照整合性が崩れ、将来的にエラーの原因になる。

### ❌ ServiceProviderの更新を忘れる

Repository登録が残ったままになり、実行時エラーが発生する可能性がある。

---

## まとめ

テーブル削除では以下を意識してください：

1. **dropIfExists()を使用**: 安全な削除
2. **関連カラムも削除**: 参照整合性を保つ
3. **関連ファイルを削除**: Entity、Repository、Resource等
4. **ServiceProviderを更新**: 登録を削除
5. **down()を正確に実装**: ロールバック可能にする
6. **データ移行を考慮**: 必要な場合は移行ロジックを実装

詳細な実装パターンは [patterns.md](../guides/patterns.md#5-テーブル削除) を参照してください。

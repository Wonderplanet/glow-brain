# 実装例: 複合的な変更

このドキュメントでは、複数の変更パターンが組み合わさった実際のPR実装例を紹介します。

## PR #1569: API削除 + テーブル追加 {#pr-1569}

- **glow-server PR**: https://github.com/Wonderplanet/glow-server/pull/1569
- **glow-schema PR**: https://github.com/Wonderplanet/glow-schema/pull/461
- **実装内容**: shop/get_store_info API削除、shop/purchaseレスポンスにusrStoreInfo追加

### 変更の背景

```
問題:
パフォーマンス的に購入時毎回get_store_info APIを呼び出すのは非効率。
購入しなかった時でも通信してしまう。

解決策:
- shop/get_store_info API削除
- shop/purchase, shop/purchase_pass のレスポンスに usrStoreInfo追加
- 購入時にのみ必要な情報を返す
```

### 変更の種類

1. **API削除**: shop/get_store_info
2. **レスポンス追加**: usrStoreInfo
3. **ロジック変更**: 購入処理api実行時にusrStoreInfo取得

### 実装内容

#### 1. Controllerの変更

**削除: GetStoreInfoController**

```bash
# ファイル削除
rm api/app/Http/Controllers/Shop/GetStoreInfoController.php
```

**更新: PurchaseController、PurchasePassController**

```php
// api/app/Http/Controllers/Shop/PurchaseController.php

public function __invoke(PurchaseRequest $request): JsonResponse
{
    // ... 購入処理

    // レスポンスにusrStoreInfoを追加
    $usrStoreInfo = $this->shopService->getUserStoreInfo($usrUserId);

    return $this->successResponse([
        'rewards' => $rewards->toArray(),
        'usrStoreInfo' => $usrStoreInfo->toArray(),  // ← 追加
    ]);
}
```

#### 2. Resourceの追加

```php
// api/app/Domain/Shop/Resources/UsrStoreInfoResource.php

namespace App\Domain\Shop\Resources;

class UsrStoreInfoResource
{
    public function __construct(
        private int $totalBilling,
        private int $monthlyBilling,
    ) {
    }

    public function toArray(): array
    {
        return [
            'totalBilling' => $this->totalBilling,
            'monthlyBilling' => $this->monthlyBilling,
        ];
    }
}
```

#### 3. Routesの更新

```php
// api/routes/api.php

// 削除
- Route::post('/shop/get_store_info', [GetStoreInfoController::class, '__invoke']);

// 既存のルートはそのまま（レスポンスだけ変更）
Route::post('/shop/purchase', [PurchaseController::class, '__invoke']);
Route::post('/shop/purchase_pass', [PurchasePassController::class, '__invoke']);
```

### 複合的な変更のポイント

#### ✅ 段階的な変更

```
1. 新しいResource作成
2. 既存APIのレスポンス更新
3. 古いAPIの削除
4. Routesの更新
```

#### ✅ 後方互換性の考慮

このケースでは後方互換性を保たない変更（Breaking Change）なので：

- クライアント実装と同時リリース
- リリースノートに記載
- マイグレーションガイド提供

### チェックリスト

- [ ] 削除対象のController/Route/Resourceが削除されている
- [ ] 新しいResource/Entityが作成されている
- [ ] 既存APIのレスポンスが更新されている
- [ ] Routesファイルが更新されている
- [ ] テストが更新されている
- [ ] Breaking Changeのドキュメント化

---

## PR #766: テーブル分離と再設計 {#pr-766}

- **glow-server PR**: https://github.com/Wonderplanet/glow-server/pull/766
- **実装内容**: イベクエ特別ルール設定をテーブル分離して、全インゲームコンテンツで使えるように対応

### 変更の背景

```
問題:
イベントクエスト専用の特別ルール設定が、他のインゲームコンテンツで使えない。

解決策:
- 特別ルール設定を独立したテーブルに分離
- 全インゲームコンテンツから参照できるように汎用化
```

### 変更の種類

1. **テーブル分離**: イベント専用テーブルから共通テーブルへ
2. **カラム追加**: 汎用的な参照カラム
3. **リレーション変更**: 1対1から1対多へ
4. **ロジック変更**: 取得・設定方法の変更

### マイグレーションファイル

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
        // 1. 新しい共通テーブルを作成
        Schema::create('mst_special_rules', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('rule_type');
            $table->json('rule_config');
            $table->integer('priority')->default(0);
        });

        // 2. イベントテーブルから特別ルール関連カラムを削除
        Schema::table('mst_event_quests', function (Blueprint $table) {
            $table->dropColumn(['special_rule_type', 'special_rule_config']);
        });

        // 3. イベントテーブルに共通テーブルへの参照を追加
        Schema::table('mst_event_quests', function (Blueprint $table) {
            $table->string('mst_special_rule_id')->nullable();
        });

        // 4. 他のインゲームテーブルにも参照を追加
        Schema::table('mst_stages', function (Blueprint $table) {
            $table->string('mst_special_rule_id')->nullable();
        });

        Schema::table('mst_advent_battles', function (Blueprint $table) {
            $table->string('mst_special_rule_id')->nullable();
        });
    }

    public function down(): void
    {
        // 逆順で復元
        Schema::table('mst_advent_battles', function (Blueprint $table) {
            $table->dropColumn('mst_special_rule_id');
        });

        Schema::table('mst_stages', function (Blueprint $table) {
            $table->dropColumn('mst_special_rule_id');
        });

        Schema::table('mst_event_quests', function (Blueprint $table) {
            $table->dropColumn('mst_special_rule_id');
            $table->string('special_rule_type');
            $table->json('special_rule_config');
        });

        Schema::dropIfExists('mst_special_rules');
    }
};
```

### Entity/Repositoryの変更

#### 新規Entity作成

```php
// api/app/Domain/Master/Entities/MstSpecialRule.php

class MstSpecialRule
{
    public function __construct(
        private string $id,
        private string $ruleType,
        private array $ruleConfig,
        private int $priority,
    ) {
    }

    // Getters...
}
```

#### 既存Entity更新

```php
// api/app/Domain/Master/Entities/MstEventQuest.php

class MstEventQuest
{
    public function __construct(
        // ... 既存プロパティ
-       private string $specialRuleType,
-       private array $specialRuleConfig,
+       private ?string $mstSpecialRuleId,
    ) {
    }
}
```

### 複合的な変更のポイント

#### ✅ マイグレーションの実行順序

```php
public function up(): void
{
    // 1. 新規テーブル作成（最初に）
    Schema::create('mst_special_rules', ...);

    // 2. 既存テーブルのカラム削除
    Schema::table('mst_event_quests', function (Blueprint $table) {
        $table->dropColumn(...);
    });

    // 3. 参照カラム追加（最後に）
    Schema::table('mst_event_quests', function (Blueprint $table) {
        $table->string('mst_special_rule_id')->nullable();
    });
}
```

**重要**: 依存関係を考慮した実行順序にする。

#### ✅ データ移行の考慮

既存データがある場合、移行ロジックが必要：

```php
public function up(): void
{
    // 1. 新規テーブル作成
    Schema::create('mst_special_rules', ...);

    // 2. データ移行
    $eventQuests = DB::table('mst_event_quests')
        ->whereNotNull('special_rule_type')
        ->get();

    foreach ($eventQuests as $quest) {
        $specialRuleId = Str::uuid();

        // 新テーブルにデータ挿入
        DB::table('mst_special_rules')->insert([
            'id' => $specialRuleId,
            'rule_type' => $quest->special_rule_type,
            'rule_config' => $quest->special_rule_config,
        ]);

        // 参照ID更新
        DB::table('mst_event_quests')
            ->where('id', $quest->id)
            ->update(['mst_special_rule_id' => $specialRuleId]);
    }

    // 3. 古いカラム削除
    Schema::table('mst_event_quests', function (Blueprint $table) {
        $table->dropColumn(['special_rule_type', 'special_rule_config']);
    });
}
```

### チェックリスト

- [ ] マイグレーションの実行順序が正しい
- [ ] データ移行ロジックが実装されている（必要な場合）
- [ ] 新規Entity/Repositoryが作成されている
- [ ] 既存Entity/Repositoryが更新されている
- [ ] 全ての参照テーブルが更新されている
- [ ] down()で元の状態に戻せる
- [ ] テストが更新されている

---

## 複合的な変更の実装戦略

### 1. 変更を分解する

複雑な変更は、小さな変更パターンに分解して理解する：

```
例: テーブル分離 + カラム追加 + リレーション変更

分解:
1. 新規テーブル作成パターン
2. カラム削除パターン
3. カラム追加パターン
4. データ移行パターン
```

### 2. マイグレーションファイルの分割を検討

関連性が低い変更は、複数のマイグレーションファイルに分割：

```php
// マイグレーション1: 新規テーブル作成
2025_XX_XX_000001_create_mst_special_rules_table.php

// マイグレーション2: 既存テーブル更新
2025_XX_XX_000002_update_event_quests_for_special_rules.php

// マイグレーション3: データ移行
2025_XX_XX_000003_migrate_special_rule_data.php
```

**利点**:
- 各マイグレーションの責務が明確
- ロールバックが細かく制御できる
- エラー時のデバッグが容易

### 3. 依存関係を明確にする

```
依存関係の例:

新規テーブル作成
  ↓
データ移行（新テーブルへのINSERT）
  ↓
参照カラム追加（新テーブルへのFK）
  ↓
古いカラム削除
```

down()では逆順で実行する。

---

## まとめ

複合的な変更では以下を意識してください：

1. **変更を分解**: 小さなパターンに分割して理解
2. **実行順序**: 依存関係を考慮した順序
3. **データ移行**: 既存データの扱いを明確に
4. **ファイル分割**: 関連性が低い変更は分割
5. **ロールバック**: down()で正確に復元
6. **テスト**: 各ステップをテストで確認

詳細な実装パターンは [patterns.md](../guides/patterns.md#8-複合的な変更) を参照してください。

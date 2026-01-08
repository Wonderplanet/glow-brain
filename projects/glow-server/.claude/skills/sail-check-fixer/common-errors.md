# Common Errors: よくあるエラーと修正例

## 目次

1. [複数ツールに共通するエラー](#複数ツールに共通するエラー)
2. [PHPCS/PHPCBF頻出エラー](#phpcspphpcbf頻出エラー)
3. [PHPStan頻出エラー](#phpstan頻出エラー)
4. [Deptrac頻出エラー](#deptrac頻出エラー)
5. [Test頻出エラー](#test頻出エラー)
6. [複雑な修正が必要なケース](#複雑な修正が必要なケース)

## 複数ツールに共通するエラー

### エラー1: 型アノテーション不足

**影響**: phpcs, phpstan

**エラーメッセージ**:
- phpcs: `Missing parameter type` / `Missing return type`
- phpstan: `Method has no return type specified.`

**修正前**:
```php
public function getUser($userId)
{
    return UsrUser::find($userId);
}
```

**修正後**:
```php
public function getUser(string $userId): ?UsrUser
{
    return UsrUser::find($userId);
}
```

### エラー2: declare(strict_types=1)の不足

**影響**: phpcs

**エラーメッセージ**:
```
Missing declare(strict_types=1) declaration
```

**修正前**:
```php
<?php

namespace App\Domain\Example;

class ExampleService
{
```

**修正後**:
```php
<?php

declare(strict_types=1);

namespace App\Domain\Example;

class ExampleService
{
```

**注意**: phpcbfで自動修正される。

### エラー3: nullチェック不足

**影響**: phpstan, test

**エラーメッセージ**:
- phpstan: `Cannot call method on App\Model\UsrUser|null.`
- test: `Call to a member function on null`

**修正前**:
```php
public function getUserName(string $userId): string
{
    $user = UsrUser::find($userId);
    return $user->name;  // エラー: $userがnullの可能性
}
```

**修正後（パターン1: nullチェック）**:
```php
public function getUserName(string $userId): string
{
    $user = UsrUser::find($userId);
    if ($user === null) {
        throw new \RuntimeException('User not found');
    }
    return $user->name;
}
```

**修正後（パターン2: firstOrFail使用）**:
```php
public function getUserName(string $userId): string
{
    $user = UsrUser::where('id', $userId)->firstOrFail();
    return $user->name;
}
```

**修正後（パターン3: null許容型）**:
```php
public function getUserName(string $userId): ?string
{
    $user = UsrUser::find($userId);
    return $user?->name;
}
```

## PHPCS/PHPCBF頻出エラー

### エラー1: 配列の最後のカンマ不足

**エラーメッセージ**:
```
ERROR | Missing trailing comma in multi-line array
```

**修正前**:
```php
$array = [
    'key1' => 'value1',
    'key2' => 'value2'  // カンマがない
];
```

**修正後**:
```php
$array = [
    'key1' => 'value1',
    'key2' => 'value2',  // カンマを追加
];
```

**注意**: phpcbfで自動修正される。

### エラー2: 未使用のuse文

**エラーメッセージ**:
```
ERROR | Unused use statement
```

**修正前**:
```php
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Example\Models\UsrExample;  // 使用していない

class ExampleService
{
    public function test(CurrentUser $user)
    {
        // UsrExampleを使用していない
    }
}
```

**修正後**:
```php
use App\Domain\Common\Entities\CurrentUser;

class ExampleService
{
    public function test(CurrentUser $user)
    {
        // ...
    }
}
```

**注意**: phpcbfで自動修正される。

### エラー3: 緩い比較の使用

**エラーメッセージ**:
```
ERROR | Use strict comparison (===) instead of (==)
```

**修正前**:
```php
if ($value == null) {
    // ...
}
```

**修正後**:
```php
if ($value === null) {
    // ...
}
```

**注意**: phpcbfで自動修正される。

### エラー4: empty()の使用

**エラーメッセージ**:
```
ERROR | Do not use empty(), use explicit comparison
```

**修正前**:
```php
if (empty($array)) {
    // ...
}
```

**修正後**:
```php
if ($array === []) {
    // ...
}
```

## PHPStan頻出エラー

### エラー1: 配列型の指定不足

**エラーメッセージ**:
```
Method getUsers() return type has no value type specified in iterable type array.
```

**修正前**:
```php
/**
 * @return array
 */
public function getUsers(): array
{
    return UsrUser::all()->toArray();
}
```

**修正後**:
```php
/**
 * @return array<string, mixed>
 */
public function getUsers(): array
{
    return UsrUser::all()->toArray();
}
```

または、より厳密に:
```php
/**
 * @return array<int, UsrUser>
 */
public function getUsers(): array
{
    return UsrUser::all()->all();
}
```

### エラー2: リクエストの入力値の型

**エラーメッセージ**:
```
Parameter #1 $userId of method process() expects string, mixed given.
```

**修正前**:
```php
public function handle(Request $request): void
{
    $userId = $request->input('user_id');
    $this->process($userId);  // エラー: mixedをstringに渡している
}

private function process(string $userId): void
{
    // ...
}
```

**修正後（パターン1: assert使用）**:
```php
public function handle(Request $request): void
{
    $userId = $request->input('user_id');
    assert(is_string($userId));
    $this->process($userId);
}
```

**修正後（パターン2: 型アノテーション）**:
```php
public function handle(Request $request): void
{
    /** @var string $userId */
    $userId = $request->input('user_id');
    $this->process($userId);
}
```

### エラー3: コレクションのfirst()

**エラーメッセージ**:
```
Cannot call method toArray() on UsrItem|null.
```

**修正前**:
```php
$item = UsrItem::where('usr_user_id', $userId)->first();
return $item->toArray();  // エラー: $itemがnullの可能性
```

**修正後（パターン1: nullチェック）**:
```php
$item = UsrItem::where('usr_user_id', $userId)->first();
if ($item === null) {
    return [];
}
return $item->toArray();
```

**修正後（パターン2: firstOrFail）**:
```php
$item = UsrItem::where('usr_user_id', $userId)->firstOrFail();
return $item->toArray();
```

### エラー4: 配列キーの存在チェック不足

**エラーメッセージ**:
```
Offset 'key' does not exist on array.
```

**修正前**:
```php
$data = ['key1' => 'value1'];
$value = $data['key2'];  // エラー: key2は存在しない
```

**修正後**:
```php
$data = ['key1' => 'value1'];
$value = $data['key2'] ?? 'default';
```

### エラー5: プロパティの動的アクセス

**エラーメッセージ**:
```
Access to an undefined property UsrUser::$items.
```

**修正前**:
```php
class UsrUser extends Model
{
    public function items(): HasMany
    {
        return $this->hasMany(UsrItem::class);
    }
}

// 使用箇所
$user->items;  // エラー: プロパティとして認識されない
```

**修正後**:
```php
/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, UsrItem> $items
 */
class UsrUser extends Model
{
    public function items(): HasMany
    {
        return $this->hasMany(UsrItem::class);
    }
}

// 使用箇所
$user->items;  // 成功
```

## Deptrac頻出エラー

### エラー1: ControllerがServiceを直接呼び出し

**エラーメッセージ**:
```
Controller must not depend on Service
app/Http/Controllers/ShopController.php:15
  -> app/Domain/Shop/Services/ShopService.php:10
```

**修正前**:
```php
class ShopController extends Controller
{
    public function __construct(
        private readonly ShopService $shopService,  // ❌ Serviceを直接呼び出し
    ) {}

    public function purchase(Request $request): JsonResponse
    {
        $this->shopService->purchase(
            $request->input('user_id'),
            $request->input('item_id'),
        );
        return response()->json(['success' => true]);
    }
}
```

**修正後**:
```php
// 1. UseCaseを作成
// api/app/Domain/Shop/UseCases/ShopPurchaseUseCase.php
class ShopPurchaseUseCase
{
    public function __construct(
        private readonly ShopService $shopService,
    ) {}

    public function exec(string $userId, string $itemId): void
    {
        $this->shopService->purchase($userId, $itemId);
    }
}

// 2. ControllerからUseCaseを呼び出し
class ShopController extends Controller
{
    public function __construct(
        private readonly ShopPurchaseUseCase $useCase,  // ✅ UseCaseを経由
    ) {}

    public function purchase(Request $request): JsonResponse
    {
        $this->useCase->exec(
            $request->input('user_id'),
            $request->input('item_id'),
        );
        return response()->json(['success' => true]);
    }
}
```

### エラー2: UseCaseが他ドメインのServiceを直接呼び出し

**エラーメッセージ**:
```
UseCase must not depend on Service from other domain
app/Domain/Shop/UseCases/PurchaseUseCase.php:45
  -> app/Domain/Item/Services/ItemService.php:10
```

**修正前**:
```php
class ShopPurchaseUseCase
{
    public function __construct(
        private readonly ShopService $shopService,
        private readonly ItemService $itemService,  // ❌ 他ドメインのService
    ) {}

    public function exec(string $userId, string $itemId): void
    {
        $items = $this->itemService->getItems($userId);  // ❌ 直接呼び出し
        $this->shopService->purchase($userId, $itemId);
    }
}
```

**修正後**:
```php
// 1. Delegatorを作成
// api/app/Domain/Item/Delegators/ItemDelegator.php
class ItemDelegator
{
    public function __construct(
        private readonly ItemService $itemService,
    ) {}

    /**
     * @return array<UsrItemEntity>
     */
    public function getItems(string $userId): array
    {
        return $this->itemService->getItems($userId);
    }
}

// 2. UseCaseからDelegatorを使用
class ShopPurchaseUseCase
{
    public function __construct(
        private readonly ShopService $shopService,
        private readonly ItemDelegator $itemDelegator,  // ✅ Delegator経由
    ) {}

    public function exec(string $userId, string $itemId): void
    {
        $items = $this->itemDelegator->getItems($userId);  // ✅ Delegator経由
        $this->shopService->purchase($userId, $itemId);
    }
}
```

### エラー3: DelegatorがDomainEntityを返却

**エラーメッセージ**:
```
Delegator must not return DomainEntity
app/Domain/Item/Delegators/ItemDelegator.php:30
  -> app/Domain/Item/Entities/ItemDomainEntity.php:15
```

**修正前**:
```php
class ItemDelegator
{
    /**
     * @return array<ItemDomainEntity>  // ❌ DomainEntityを返却
     */
    public function getItems(string $userId): array
    {
        return $this->itemService->getDetailedItems($userId);
    }
}
```

**修正後**:
```php
class ItemDelegator
{
    /**
     * @return array<UsrItemEntity>  // ✅ UsrModelEntity（ResourceEntity）を返却
     */
    public function getItems(string $userId): array
    {
        $domainEntities = $this->itemService->getDetailedItems($userId);

        // DomainEntityをUsrModelEntityに変換
        return array_map(
            fn(ItemDomainEntity $entity) => new UsrItemEntity(
                usrUserId: $entity->getUserId(),
                mstItemId: $entity->getItemId(),
                amount: $entity->getAmount(),
            ),
            $domainEntities,
        );
    }
}
```

## Test頻出エラー

### エラー1: アサーションエラー（期待値不一致）

**エラーメッセージ**:
```
Failed asserting that 10 matches expected 5.
```

**修正前**:
```php
public function test_example()
{
    $user = $this->createUsrUser();
    $this->createDiamond($user->getId(), 10, 0, 0);  // 10ダイヤモンド

    $result = $this->service->getDiamondAmount($user->getId());
    $this->assertEquals(5, $result);  // エラー: 10だが5を期待
}
```

**修正後**:
```php
public function test_example()
{
    $user = $this->createUsrUser();
    $this->createDiamond($user->getId(), 10, 0, 0);

    $result = $this->service->getDiamondAmount($user->getId());
    $this->assertEquals(10, $result);  // 正しい期待値
}
```

### エラー2: saveAll()の実行漏れ

**エラーメッセージ**:
```
Failed asserting that 0 matches expected 1.
```

**修正前**:
```php
public function test_example()
{
    $user = $this->createUsrUser();
    $this->itemService->addItem($user->getId(), 'item_1', 10);
    // saveAll()を実行していない

    $items = UsrItem::where('usr_user_id', $user->getId())->get();
    $this->assertCount(1, $items);  // エラー: データが保存されていない
}
```

**修正後**:
```php
public function test_example()
{
    $user = $this->createUsrUser();
    $this->itemService->addItem($user->getId(), 'item_1', 10);
    $this->saveAll();  // ✅ saveAll()を実行

    $items = UsrItem::where('usr_user_id', $user->getId())->get();
    $this->assertCount(1, $items);  // 成功
}
```

**注意**: UseCase, Controller テストでは`saveAll()`は実行不要。

### エラー3: RefreshDatabaseの重複インポート

**エラーメッセージ**:
```
Trait 'RefreshDatabase' conflicts
```

**修正前**:
```php
use Illuminate\Foundation\Testing\RefreshDatabase;  // ❌ 重複インポート

class ExampleTest extends TestCase
{
    use RefreshDatabase;  // ❌ 親クラスで既に使用
}
```

**修正後**:
```php
class ExampleTest extends TestCase
{
    // RefreshDatabaseは親クラス(TestCase)で使用済み
}
```

### エラー4: fixTime()の使用漏れ

**エラーメッセージ**:
```
Failed asserting that two DateTime objects are equal.
```

**修正前**:
```php
public function test_example()
{
    $user = $this->createUsrUser();
    $this->service->updateLastLogin($user->getId());

    $user->refresh();
    $this->assertEquals('2024-01-01 00:00:00', $user->last_login_at);  // エラー
}
```

**修正後**:
```php
public function test_example()
{
    $now = $this->fixTime('2024-01-01 00:00:00');  // ✅ 時間を固定

    $user = $this->createUsrUser();
    $this->service->updateLastLogin($user->getId());

    $user->refresh();
    $this->assertEquals($now, $user->last_login_at);  // 成功
}
```

### エラー5: データベース制約違反

**エラーメッセージ**:
```
SQLSTATE[23000]: Integrity constraint violation
```

**修正前**:
```php
public function test_example()
{
    UsrItem::factory()->create([
        'usr_user_id' => 'user_123',  // エラー: ユーザーが存在しない
        'mst_item_id' => 'item_1',
    ]);
}
```

**修正後**:
```php
public function test_example()
{
    // 先にユーザーを作成
    $user = $this->createUsrUser(['id' => 'user_123']);

    // 次にアイテムを作成
    UsrItem::factory()->create([
        'usr_user_id' => $user->getId(),
        'mst_item_id' => 'item_1',
    ]);
}
```

## 複雑な修正が必要なケース

### ケース1: 複数エラーが連鎖している

**状況**: phpcs, phpstan, deptracで同時にエラー

**例**:
```php
class ShopController extends Controller
{
    public function __construct(
        private readonly ShopService $shopService,  // deptrac違反
    ) {}

    public function purchase(Request $request)  // phpcs: 型不足
    {
        $userId = $request->input('user_id');
        $this->shopService->purchase($userId, 'item_1');  // phpstan: 型エラー
        return response()->json(['success' => true]);
    }
}
```

**修正手順**:

1. **deptrac違反を修正**: UseCaseを作成
2. **phpcs違反を修正**: 型アノテーション追加
3. **phpstan違反を修正**: 型キャストやassert追加

**修正後**:
```php
// 1. UseCase作成
class ShopPurchaseUseCase
{
    public function __construct(
        private readonly ShopService $shopService,
    ) {}

    public function exec(string $userId, string $itemId): void
    {
        $this->shopService->purchase($userId, $itemId);
    }
}

// 2. Controller修正
class ShopController extends Controller
{
    public function __construct(
        private readonly ShopPurchaseUseCase $useCase,  // ✅ deptrac解決
    ) {}

    public function purchase(Request $request): JsonResponse  // ✅ phpcs解決
    {
        /** @var string $userId */
        $userId = $request->input('user_id');  // ✅ phpstan解決
        assert(is_string($userId));

        $this->useCase->exec($userId, 'item_1');
        return response()->json(['success' => true]);
    }
}
```

### ケース2: レガシーコードの大規模修正

**状況**: 古いコードでエラーが大量発生

**戦略**:
1. phpcbfで自動修正できる項目を一括修正
2. phpcsの型アノテーション不足を機械的に対応
3. phpstanのnullチェックを追加
4. deptracの依存関係違反は段階的に修正
5. テストは最後に修正（上記の修正でテストが通る場合もある）

### ケース3: 既存のアーキテクチャに大きな変更が必要

**状況**: Delegatorが存在せず、大規模なリファクタリングが必要

**戦略**:
1. まず新規のDelegatorを作成
2. 新しいコードから順次Delegatorを使用
3. 既存のコードは計画的に移行
4. 各ステップでテストを実行して、デグレがないことを確認

## チェックリスト

- [ ] エラーメッセージから影響するツールを特定
- [ ] 修正の優先順位を決定（自動修正 → 手動修正 → テスト）
- [ ] 各ガイドの該当セクションを参照
- [ ] 修正後、関連するツールを再実行して確認
- [ ] 連鎖的なエラーが解消されたか確認
- [ ] コミットメッセージに修正内容を記載

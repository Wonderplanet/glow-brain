# UsrModelキャッシュ実装例

UsrModelCacheRepositoryを使ったユーザーデータのキャッシュ実装例を紹介します。

## 実装例1: UsrItemRepository（複数レコード）

ユーザーが複数のアイテムを持つケースの実装例です。

### ファイル: UsrItemRepository.php

```php
<?php

declare(strict_types=1);

namespace App\Domain\Item\Repositories;

use App\Domain\Item\Models\UsrItem;
use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRepository;
use App\Infrastructure\UsrModelManager;
use Illuminate\Support\Collection;

class UsrItemRepository extends UsrModelMultiCacheRepository
{
    protected string $modelClass = UsrItem::class;

    public function __construct(
        protected UsrModelManager $usrModelManager,
    ) {
        parent::__construct($usrModelManager);
    }

    /**
     * 全アイテムを取得
     */
    public function getAllByUserId(string $usrUserId): Collection
    {
        // キャッシュから取得を試みる
        if ($this->isAllFetched()) {
            return collect($this->getCache());
        }

        // DBから取得
        $models = UsrItem::query()
            ->where('usr_user_id', $usrUserId)
            ->get();

        // キャッシュに保存
        $this->syncModels($models);
        $this->markAllFetched($usrUserId);

        return $models;
    }

    /**
     * IDで1件取得
     */
    public function getById(string $usrUserId, string $usrItemId): ?UsrItem
    {
        // キャッシュから取得を試みる
        $cache = $this->getCache();
        if (isset($cache[$usrItemId])) {
            return $cache[$usrItemId];
        }

        // DBから取得
        $model = UsrItem::query()
            ->where('usr_user_id', $usrUserId)
            ->where('usr_item_id', $usrItemId)
            ->first();

        if ($model === null) {
            return null;
        }

        // キャッシュに保存
        $this->syncModels(collect([$model]));

        return $model;
    }

    /**
     * mst_item_idで複数取得
     */
    public function getByMstItemIds(string $usrUserId, array $mstItemIds): Collection
    {
        // 全データ取得済みならキャッシュから取得
        if ($this->isAllFetched()) {
            $cachedItems = $this->getCacheWhereIn('mst_item_id', $mstItemIds);
            return collect($cachedItems);
        }

        // DBから取得
        $models = UsrItem::query()
            ->where('usr_user_id', $usrUserId)
            ->whereIn('mst_item_id', $mstItemIds)
            ->get();

        // キャッシュに保存
        $this->syncModels($models);

        return $models;
    }

    /**
     * 保存
     */
    public function save(UsrItem $model): void
    {
        $model->save();

        // キャッシュに反映
        $this->syncModels(collect([$model]));
    }

    /**
     * 複数保存
     */
    public function saveBatch(Collection $models): void
    {
        foreach ($models as $model) {
            $model->save();
        }

        // キャッシュに反映
        $this->syncModels($models);
    }

    /**
     * 変更があったアイテムを取得
     */
    public function getChangedItems(): Collection
    {
        return $this->getChangedModels();
    }
}
```

### 使用例: ItemService

```php
<?php

declare(strict_types=1);

namespace App\Domain\Item\Services;

use App\Domain\Item\Models\UsrItem;
use App\Domain\Item\Repositories\UsrItemRepository;
use Illuminate\Support\Collection;

class ItemService
{
    public function __construct(
        private UsrItemRepository $usrItemRepository,
    ) {
    }

    /**
     * アイテムを追加
     */
    public function addItem(string $usrUserId, string $mstItemId, int $amount): UsrItem
    {
        // 既存アイテムを取得
        $existingItems = $this->usrItemRepository->getByMstItemIds($usrUserId, [$mstItemId]);
        $existingItem = $existingItems->first();

        if ($existingItem !== null) {
            // 既存アイテムに加算
            $existingItem->setAmount($existingItem->getAmount() + $amount);
            $this->usrItemRepository->save($existingItem);
            return $existingItem;
        }

        // 新規アイテム作成
        $newItem = UsrItem::factory()->make([
            'usr_user_id' => $usrUserId,
            'mst_item_id' => $mstItemId,
            'amount' => $amount,
        ]);
        $this->usrItemRepository->save($newItem);

        return $newItem;
    }

    /**
     * アイテムを消費
     */
    public function consumeItem(string $usrUserId, string $mstItemId, int $amount): void
    {
        $items = $this->usrItemRepository->getByMstItemIds($usrUserId, [$mstItemId]);
        $item = $items->first();

        if ($item === null || $item->getAmount() < $amount) {
            throw new GameException(ErrorCode::INSUFFICIENT_ITEM);
        }

        $item->setAmount($item->getAmount() - $amount);
        $this->usrItemRepository->save($item);
    }

    /**
     * 複数アイテムをバッチ追加
     */
    public function addItemsBatch(string $usrUserId, array $itemData): Collection
    {
        // 全アイテム取得
        $allItems = $this->usrItemRepository->getAllByUserId($usrUserId);

        // mst_item_idをキーにしたマップを作成
        $itemMap = $allItems->keyBy('mst_item_id');

        $updatedItems = collect();

        foreach ($itemData as $data) {
            $mstItemId = $data['mst_item_id'];
            $amount = $data['amount'];

            if ($itemMap->has($mstItemId)) {
                // 既存アイテムに加算
                $item = $itemMap->get($mstItemId);
                $item->setAmount($item->getAmount() + $amount);
            } else {
                // 新規アイテム作成
                $item = UsrItem::factory()->make([
                    'usr_user_id' => $usrUserId,
                    'mst_item_id' => $mstItemId,
                    'amount' => $amount,
                ]);
            }

            $updatedItems->push($item);
        }

        // バッチ保存
        $this->usrItemRepository->saveBatch($updatedItems);

        return $updatedItems;
    }
}
```

### 使用例: UseCase（変更検知）

```php
<?php

declare(strict_types=1);

namespace App\Domain\Item\UseCases;

use App\Domain\Item\Repositories\UsrItemRepository;
use App\Domain\Item\Services\ItemService;
use App\Http\Responses\ResultData;

class ItemAddUseCase
{
    public function __construct(
        private ItemService $itemService,
        private UsrItemRepository $usrItemRepository,
    ) {
    }

    public function execute(string $usrUserId, string $mstItemId, int $amount): ResultData
    {
        // アイテム追加
        $this->itemService->addItem($usrUserId, $mstItemId, $amount);

        // 変更があったアイテムのみ取得してレスポンスに含める
        $changedItems = $this->usrItemRepository->getChangedItems();

        return new ResultData([
            'items' => $changedItems,
        ]);
    }
}
```

## 実装例2: UsrUserRepository（単一レコード）

ユーザーが1レコードのみ持つケースの実装例です。

### ファイル: UsrUserRepository.php

```php
<?php

declare(strict_types=1);

namespace App\Domain\User\Repositories;

use App\Domain\Resource\Usr\Repositories\UsrModelSingleCacheRepository;
use App\Domain\User\Models\UsrUser;
use App\Infrastructure\UsrModelManager;

class UsrUserRepository extends UsrModelSingleCacheRepository
{
    protected string $modelClass = UsrUser::class;

    public function __construct(
        protected UsrModelManager $usrModelManager,
    ) {
        parent::__construct($usrModelManager);
    }

    /**
     * ユーザーIDで取得
     */
    public function getByUserId(string $usrUserId): ?UsrUser
    {
        // キャッシュから取得を試みる
        $cache = $this->getCache();
        if (!empty($cache)) {
            return current($cache);
        }

        // DBから取得
        $model = UsrUser::query()
            ->where('usr_user_id', $usrUserId)
            ->first();

        if ($model === null) {
            return null;
        }

        // キャッシュに保存
        $this->syncModels(collect([$model]));

        return $model;
    }

    /**
     * 保存
     */
    public function save(UsrUser $model): void
    {
        $model->save();

        // キャッシュに反映
        $this->syncModels(collect([$model]));
    }

    /**
     * 変更があったユーザーを取得
     */
    public function getChangedUser(): ?UsrUser
    {
        $changedModels = $this->getChangedModels();
        return $changedModels->first();
    }
}
```

### 使用例: UserService

```php
<?php

declare(strict_types=1);

namespace App\Domain\User\Services;

use App\Domain\User\Models\UsrUser;
use App\Domain\User\Repositories\UsrUserRepository;

class UserService
{
    public function __construct(
        private UsrUserRepository $usrUserRepository,
    ) {
    }

    /**
     * ユーザーレベルアップ
     */
    public function levelUp(string $usrUserId): UsrUser
    {
        $user = $this->usrUserRepository->getByUserId($usrUserId);

        if ($user === null) {
            throw new GameException(ErrorCode::USER_NOT_FOUND);
        }

        // レベルアップ
        $user->setLevel($user->getLevel() + 1);
        $this->usrUserRepository->save($user);

        return $user;
    }

    /**
     * 経験値加算
     */
    public function addExp(string $usrUserId, int $exp): void
    {
        $user = $this->usrUserRepository->getByUserId($usrUserId);

        if ($user === null) {
            throw new GameException(ErrorCode::USER_NOT_FOUND);
        }

        $user->setExp($user->getExp() + $exp);
        $this->usrUserRepository->save($user);
    }
}
```

## 実装例3: カスタムモデルキー（複合キー）

UsrConditionPackのように複合キーが必要なケースの実装例です。

### ファイル: UsrConditionPack.php

```php
<?php

declare(strict_types=1);

namespace App\Domain\Shop\Models;

use App\Domain\Resource\Usr\Models\UsrModel;

class UsrConditionPack extends UsrModel
{
    protected $table = 'usr_condition_pack';

    /**
     * モデルキーをカスタマイズ（複合キー）
     */
    public function makeModelKey(): string
    {
        // usr_user_id + condition_type_id の組み合わせをキーにする
        return "{$this->usr_user_id}_{$this->condition_type_id}";
    }

    // getters/setters...
}
```

### ファイル: UsrConditionPackRepository.php

```php
<?php

declare(strict_types=1);

namespace App\Domain\Shop\Repositories;

use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRepository;
use App\Domain\Shop\Models\UsrConditionPack;
use App\Infrastructure\UsrModelManager;
use Illuminate\Support\Collection;

class UsrConditionPackRepository extends UsrModelMultiCacheRepository
{
    protected string $modelClass = UsrConditionPack::class;

    public function __construct(
        protected UsrModelManager $usrModelManager,
    ) {
        parent::__construct($usrModelManager);
    }

    /**
     * 全パックを取得
     */
    public function getAllByUserId(string $usrUserId): Collection
    {
        if ($this->isAllFetched()) {
            return collect($this->getCache());
        }

        $models = UsrConditionPack::query()
            ->where('usr_user_id', $usrUserId)
            ->get();

        $this->syncModels($models);
        $this->markAllFetched($usrUserId);

        return $models;
    }

    /**
     * condition_type_idで取得
     */
    public function getByConditionTypeId(string $usrUserId, string $conditionTypeId): ?UsrConditionPack
    {
        // キャッシュから取得を試みる
        $modelKey = "{$usrUserId}_{$conditionTypeId}";
        $cache = $this->getCache();
        if (isset($cache[$modelKey])) {
            return $cache[$modelKey];
        }

        // DBから取得
        $model = UsrConditionPack::query()
            ->where('usr_user_id', $usrUserId)
            ->where('condition_type_id', $conditionTypeId)
            ->first();

        if ($model === null) {
            return null;
        }

        // キャッシュに保存
        $this->syncModels(collect([$model]));

        return $model;
    }

    public function save(UsrConditionPack $model): void
    {
        $model->save();
        $this->syncModels(collect([$model]));
    }
}
```

## テスト実装例

### UsrItemRepositoryTest.php

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Item\Repositories;

use App\Domain\Item\Models\UsrItem;
use App\Domain\Item\Repositories\UsrItemRepository;
use Tests\TestCase;

class UsrItemRepositoryTest extends TestCase
{
    private UsrItemRepository $repository;
    private string $usrUserId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->app->make(UsrItemRepository::class);
        $this->usrUserId = 'test_user_' . uniqid();
    }

    public function test_getAllByUserId_キャッシュがない場合はDBから取得する(): void
    {
        // Arrange
        UsrItem::factory()->count(5)->create(['usr_user_id' => $this->usrUserId]);

        // Act
        $result = $this->repository->getAllByUserId($this->usrUserId);

        // Assert
        $this->assertCount(5, $result);
    }

    public function test_getAllByUserId_キャッシュがある場合はキャッシュから取得する(): void
    {
        // Arrange
        UsrItem::factory()->count(5)->create(['usr_user_id' => $this->usrUserId]);

        // 1回目はDBから取得（キャッシュに保存される）
        $this->repository->getAllByUserId($this->usrUserId);

        // DBのデータを削除
        UsrItem::query()->where('usr_user_id', $this->usrUserId)->delete();

        // Act（2回目はキャッシュから取得）
        $result = $this->repository->getAllByUserId($this->usrUserId);

        // Assert（キャッシュから取得できている）
        $this->assertCount(5, $result);
    }

    public function test_save_キャッシュに反映される(): void
    {
        // Arrange
        $item = UsrItem::factory()->make(['usr_user_id' => $this->usrUserId]);

        // Act
        $this->repository->save($item);

        // Assert（キャッシュから取得できる）
        $cachedItem = $this->repository->getById($this->usrUserId, $item->getUsrItemId());
        $this->assertNotNull($cachedItem);
        $this->assertEquals($item->getUsrItemId(), $cachedItem->getUsrItemId());
    }

    public function test_getChangedItems_変更があったアイテムのみ取得できる(): void
    {
        // Arrange
        UsrItem::factory()->count(5)->create(['usr_user_id' => $this->usrUserId]);

        // 全アイテム取得（キャッシュに保存）
        $allItems = $this->repository->getAllByUserId($this->usrUserId);

        // 1つだけ更新
        $itemToUpdate = $allItems->first();
        $itemToUpdate->setAmount(999);
        $this->repository->save($itemToUpdate);

        // Act（変更があったアイテムのみ取得）
        $changedItems = $this->repository->getChangedItems();

        // Assert（1つだけ取得できる）
        $this->assertCount(1, $changedItems);
        $this->assertEquals(999, $changedItems->first()->getAmount());
    }
}
```

## まとめ

UsrModelキャッシュパターンの主な特徴:

1. **リクエストスコープ**: リクエスト中のみキャッシュが有効
2. **自動同期**: syncModelsで自動的にキャッシュ更新
3. **変更検知**: getChangedModelsでレスポンス最適化
4. **型安全性**: UsrModelInterfaceで型が保証される
5. **全取得フラグ**: isAllFetched/markAllFetchedでキャッシュヒット最適化

このパターンを使うことで、リクエスト中のDB負荷を大幅に削減できます。

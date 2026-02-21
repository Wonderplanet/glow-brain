<?php

namespace Tests\Feature\Domain\Item;

use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Item\Repositories\UsrItemRepository;
use Tests\Feature\Domain\Common\Repositories\TestUsrItem;
use Tests\TestCase;

class UsrItemCacheRepositoryTest extends TestCase
{
    private UsrItemRepository $usrItemRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->usrItemRepository = $this->app->make(UsrItemRepository::class);
    }

    private function toUsrItemModel(UsrItem $usrItem): TestUsrItem
    {
        return new TestUsrItem($usrItem->toArray());
    }

    public function test_saveModels_upsertできていることを確認()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        // updateするために、事前にレコードを作成
        $updateModels = UsrItem::factory()->count(2)->create([
            'usr_user_id' => $usrUser->getId(),
            'amount' => 10,
        ])->map(fn ($usrItem) => $this->toUsrItemModel($usrItem))
        // 更新対象とするために、モデルの値を変更する
        ->each(function ($model) {
            $model->setItemAmount(50);
        });
        // insertするために、DB保存せずモデルを用意
        $insertModels = UsrItem::factory()->count(2)->make([
            'usr_user_id' => $usrUser->getId(),
            'amount' => 20,
        ])->map(fn ($usrItem) => $this->toUsrItemModel($usrItem));

        // Exercise
        $models = $updateModels->merge($insertModels);
        $this->execPrivateMethod($this->usrItemRepository, 'saveModels', [$models]);

        // Verify
        $usrItems = UsrItem::where('usr_user_id', $usrUser->getId())->get();
        $this->assertEquals(4, $usrItems->count());

        $models = $models->mapWithKeys(function ($model) {
            return [$model->getId() => $model->getAmount()];
        });
        $usrItems = $usrItems->mapWithKeys(function ($usrItem) {
            return [$usrItem->getId() => $usrItem->getAmount()];
        });
        $this->assertEquals($models, $usrItems);
    }

    public function test_create_モデルを作成してキャッシュに追加できていることを確認()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $mstItemId = fake()->uuid();
        $amount = 10;

        // Exercise
        $model = $this->usrItemRepository->create($usrUser->getId(), $mstItemId, $amount);

        // Verify
        $this->assertCount(
            0,
            UsrItem::where('usr_user_id', $usrUser->getId())->get()
            );

        $cache = array_values($this->getUsrModelManagerPrivateVariable('models')[UsrItemRepository::class]);
        $this->assertCount(1, $cache);
        $this->assertEquals($model->getId(), $cache[0]->getId());
    }

    public function test_getList_キャッシュになければDBから取得しキャッシュに追加できていることを確認()
    {
        // Setup
        $usrUser = $this->createUsrUser();

        $usrItems = UsrItem::factory()->count(2)->create([
            'usr_user_id' => $usrUser->getId(),
        ]);

        // Exercise
        $result = $this->usrItemRepository->getList($usrUser->getId());

        // Verify

        // DBから取得できていることを確認
        $this->assertCount(2, $result);
        $this->assertEquals(
            $usrItems->map(function ($usrItem) { return $usrItem->getId(); })->sort()->values(),
            $result->map(function ($usrItem) { return $usrItem->getId(); })->sort()->values(),
        );

        // キャッシュに追加されていることを確認
        $cache = collect($this->getUsrModelManagerPrivateVariable('models')[UsrItemRepository::class]);
        $this->assertCount(2, $cache);
        $this->assertEquals(
            $result->map(function ($model) { return $model->getId(); })->sort()->values(),
            $cache->map(function ($model) { return $model->getId(); })->sort()->values(),
        );
    }

    public function test_getListByMstItemIds_キャッシュになければDBから取得しキャッシュに追加できていることを確認()
    {
        // Setup
        $usrUser = $this->createUsrUser();

        $usrItems = UsrItem::factory()->count(2)->create([
            'usr_user_id' => $usrUser->getId(),
        ]);
        $mstItemIds = $usrItems->map->getMstItemId();

        // Exercise
        $result = $this->usrItemRepository->getListByMstItemIds($usrUser->getId(), $mstItemIds);

        // Verify

        // DBから取得できていることを確認
        $this->assertCount(2, $result);
        $this->assertEquals(
            $usrItems->map(function ($usrItem) { return $usrItem->getId(); })->sort()->values(),
            $result->map(function ($usrItem) { return $usrItem->getId(); })->sort()->values(),
        );

        // キャッシュに追加されていることを確認
        $cache = collect($this->getUsrModelManagerPrivateVariable('models')[UsrItemRepository::class]);
        $this->assertCount(2, $cache);
        $this->assertEquals(
            $result->map(function ($model) { return $model->getId(); })->sort()->values(),
            $cache->map(function ($model) { return $model->getId(); })->sort()->values(),
        );
    }

    public function test_getByMstItemId_キャッシュになければDBから取得しキャッシュに追加できていることを確認()
    {
        // Setup
        $usrUser = $this->createUsrUser();

        $usrItems = UsrItem::factory()->count(2)->create([
            'usr_user_id' => $usrUser->getId(),
        ]);
        $mstItemId = $usrItems->first()->getMstItemId();

        // Exercise
        $result = $this->usrItemRepository->getByMstItemId($usrUser->getId(), $mstItemId);

        // Verify

        // DBから取得できていることを確認
        $this->assertNotNull($result);
        $this->assertEquals($mstItemId, $result->getMstItemId());

        // キャッシュに追加されていることを確認
        $cache = array_values($this->getUsrModelManagerPrivateVariable('models')[UsrItemRepository::class]);
        $this->assertCount(1, $cache);
        $this->assertEquals($result->getId(), $cache[0]->getId());
    }
}

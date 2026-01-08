<?php

namespace Tests\Feature\Domain\Common;

use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Item\Repositories\UsrItemRepository;
use App\Domain\Stage\Models\Eloquent\UsrStage;
use App\Domain\Stage\Models\UsrStageSession;
use App\Domain\Stage\Repositories\UsrStageRepository;
use App\Domain\Stage\Repositories\UsrStageSessionRepository;
use Tests\Feature\Domain\Common\Repositories\TestMultiModel;
use Tests\Feature\Domain\Common\Repositories\TestUsrItem;
use Tests\Feature\Domain\Common\Repositories\TestUsrModelMultiCacheRepository;
use Tests\Feature\Domain\Common\Repositories\TestUsrModelSingleCacheRepository;
use Tests\TestCase;

class UsrModelManagerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_syncModels_複数種類のモデルに対して追加または上書きができてることを確認()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        $testMultiModels1 = collect([1, 2, 3])->map(function ($i) use ($usrUserId) {
            return TestMultiModel::create('id'.(string)$i, $usrUserId, intValue: 0, isChanged: true);
        })->all();
        $testMultiModels2 = collect([2, 3, 4])->map(function ($i) use ($usrUserId) {
            return TestMultiModel::create('id'.(string)$i, $usrUserId, intValue: (int) $i + 10, isChanged: true);
        })->all();

        $testSingleModel = TestMultiModel::create('id1', $usrUserId, boolValue: false, isChanged: true);

        // Exercise
        $this->usrModelManager->syncModels(TestUsrModelMultiCacheRepository::class, $testMultiModels1);
        $this->usrModelManager->syncModels(TestUsrModelMultiCacheRepository::class, $testMultiModels2);
        $this->usrModelManager->syncModels(TestUsrModelSingleCacheRepository::class, [$testSingleModel]);

        // Verify
        // testMultiModels
        $models = collect($this->getUsrModelManagerPrivateVariable('models')[TestUsrModelMultiCacheRepository::class])
            ->keyBy(function (TestMultiModel $model) {
                return $model->getId();
            });
        $this->assertEquals(4, $models->count());
        $this->assertEquals(0, $models->get('id1')->getIntValue());
        $this->assertEquals(12, $models->get('id2')->getIntValue());
        $this->assertEquals(13, $models->get('id3')->getIntValue());
        $this->assertEquals(14, $models->get('id4')->getIntValue());

        $needSave = $this->getUsrModelManagerPrivateVariable('needSaves')[TestUsrModelMultiCacheRepository::class];
        $this->assertTrue($needSave);

        // testSingleModel
        $model = array_values($this->getUsrModelManagerPrivateVariable('models')[TestUsrModelSingleCacheRepository::class])[0];
        $this->assertNotNull($model);
        $this->assertEquals($usrUserId, $model->getUsrUserId());
        $this->assertEquals('id1', $model->getId());
        $this->assertEquals(false, $model->getBoolValue());

        $needSave = $this->getUsrModelManagerPrivateVariable('needSaves')[TestUsrModelSingleCacheRepository::class];
        $this->assertTrue($needSave);
    }

    public function test_syncModels_同一キーのモデルがある場合に、変更ありのモデルのみ上書きされることを確認()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        $testMultiModels = collect([1, 2, 3])->map(function ($i) use ($usrUserId) {
            return TestMultiModel::create('id'.(string)$i, $usrUserId, intValue: $i * 100, isChanged: false);
        })->keyBy(function (TestMultiModel $model) {
            return $model->getId();
        });

        $models = collect();
        $models->put(
            TestUsrModelMultiCacheRepository::class,
            $testMultiModels->mapWithKeys(function (TestMultiModel $model) {
                return [$model->makeModelKey() => clone $model];
            })->all()
        );
        $this->setUsrModelManagerPrivateVariable('models', $models->all());

        $testMultiModel1 = $testMultiModels->get('id1');
        $testMultiModel2 = $testMultiModels->get('id2');
        $testMultiModel3 = $testMultiModels->get('id3');

        // 上書きされる
        $testMultiModel2->int_value = 222;
        // $testMultiModel2->is_changed = true;

        // 上書きされない
        $testMultiModel3->int_value = 333;
        // $testMultiModel3->is_changed = false;
        $testMultiModel3->syncOriginal();

        // Exercise
        $this->usrModelManager->syncModels(TestUsrModelMultiCacheRepository::class, [$testMultiModel2, $testMultiModel3]);

        // Verify
        $models = collect($this->getUsrModelManagerPrivateVariable('models')[TestUsrModelMultiCacheRepository::class])
            ->keyBy(function (TestMultiModel $model) {
                return $model->getId();
            });
        $this->assertEquals(3, $models->count());

        $this->assertEquals(100, $models->get('id1')->getIntValue());
        $this->assertEquals(222, $models->get('id2')->getIntValue());
        $this->assertEquals(300, $models->get('id3')->getIntValue());
    }

    public function test_saveModels_変更がないモデルのみの場合はDB更新を実行しないことを確認()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        $testMultiModels = collect([1, 2, 3])->map(function ($i) use ($usrUserId) {
            $model = TestMultiModel::create(
                'id'.(string)$i, $usrUserId, intValue: $i * 100, stringValue: 'notSave',
            );
            $model->syncOriginal();
            return $model;
        })->keyBy(function (TestMultiModel $model) {
            return $model->getId();
        });

        $testSingleModel = TestMultiModel::create(
            'id1', $usrUserId, boolValue: false, stringValue: 'notSave',
        );
        $testSingleModel->syncOriginal();

        $models = collect();
        $models->put(TestUsrModelMultiCacheRepository::class, $testMultiModels->all());
        $models->put(TestUsrModelSingleCacheRepository::class, [$testSingleModel]);
        $this->setUsrModelManagerPrivateVariable('models', $models->all());

        // Exercise
        $this->execPrivateMethod($this->usrModelManager, 'saveModels', [TestUsrModelMultiCacheRepository::class]);
        $this->execPrivateMethod($this->usrModelManager, 'saveModels', [TestUsrModelSingleCacheRepository::class]);

        // Verify
        // testMultiModel
        $stringValues = collect($this->getUsrModelManagerPrivateVariable('models')[TestUsrModelMultiCacheRepository::class])
            ->map(function (TestMultiModel $model) {
                return $model->getStringValue();
            })->unique();
        $this->assertCount(1, $stringValues);
        $this->assertEquals('notSave', $stringValues->first());
        // testSingleModel
        $model = array_values($this->getUsrModelManagerPrivateVariable('models')[TestUsrModelSingleCacheRepository::class])[0];
        $this->assertNotNull($model);
        $this->assertEquals('notSave', $model->getStringValue());
    }

    public function test_saveModels_変更があるモデルのみDB更新を実行していることを確認()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        // testMultiModel
        $testMultiModels = collect([1, 2, 3])->map(function ($i) use ($usrUserId) {
            $model = TestMultiModel::create(
                'id'.(string)$i, $usrUserId, intValue: $i * 100, stringValue: 'notSave',
            );
            $model->syncOriginal();
            return $model;
        })->keyBy(function (TestMultiModel $model) {
            return $model->getId();
        });
        // id2,3のみ変更
        $testMultiModel2 = $testMultiModels->get('id2');
        $testMultiModel2->int_value = 222;
        $testMultiModel3 = $testMultiModels->get('id3');
        $testMultiModel3->int_value = 333;

        // testSingleModel
        $testSingleModel = TestMultiModel::create(
            'id1', $usrUserId, boolValue: false, stringValue: 'notSave',
        );

        $models = collect();
        $models->put(TestUsrModelMultiCacheRepository::class, $testMultiModels->all());
        $models->put(TestUsrModelSingleCacheRepository::class, [$testSingleModel]);
        $this->setUsrModelManagerPrivateVariable('models', $models->all());

        // Exercise
        $this->execPrivateMethod($this->usrModelManager, 'saveModels', [TestUsrModelMultiCacheRepository::class]);
        $this->execPrivateMethod($this->usrModelManager, 'saveModels', [TestUsrModelSingleCacheRepository::class]);

        // Verify

        // DB保存されていることを確認
        // testMultiModel
        $stringValues = collect($this->getUsrModelManagerPrivateVariable('models')[TestUsrModelMultiCacheRepository::class])
            ->mapWithKeys(function (TestMultiModel $model) {
                return [$model->getId() => $model->getStringValue()];
            });
        $this->assertCount(3, $stringValues);
        $this->assertEquals('notSave', $stringValues->get('id1'));
        $this->assertEquals('saveModel', $stringValues->get('id2'));
        $this->assertEquals('saveModel', $stringValues->get('id3'));
        // testSingleModel
        $model = array_values($this->getUsrModelManagerPrivateVariable('models')[TestUsrModelSingleCacheRepository::class])[0];
        $this->assertNotNull($model);
        $this->assertEquals('saveModel', $model->getStringValue());

        // 各モデル毎の変更ありステータスが解除されていることを確認
        // testMultiModel
        $isChangeds = collect($this->getUsrModelManagerPrivateVariable('models')[TestUsrModelMultiCacheRepository::class])
            ->map(function (TestMultiModel $model) {
                return $model->isChanged();
            })->unique();
        $this->assertCount(1, $isChangeds);
        $this->assertFalse($isChangeds->first());
        // testSingleModel
        $isChanged = collect($this->getUsrModelManagerPrivateVariable('models')[TestUsrModelSingleCacheRepository::class])
            ->first()->isChanged();
        $this->assertFalse($isChanged);

        // Repositoryごとの変更ありステータスが解除されていることを確認
        // testMultiModel
        $needSave = $this->getUsrModelManagerPrivateVariable('needSaves')[TestUsrModelMultiCacheRepository::class];
        $this->assertFalse($needSave);
        // testSingleModel
        $needSave = $this->getUsrModelManagerPrivateVariable('needSaves')[TestUsrModelSingleCacheRepository::class];
        $this->assertFalse($needSave);

    }

    private function toUsrItemModel(UsrItem $usrItem): TestUsrItem
    {
        return new TestUsrItem($usrItem->toArray());
    }

    public function test_saveModels_DB更新の際にcreated_atとupdated_atが更新されていることを確認()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        // DB保存して、更新なしステータスで、インスタンスを用意
        $usrItem1 = $this->toUsrItemModel(UsrItem::factory()
            ->create([
                'usr_user_id' => $usrUserId,
                'mst_item_id' => '1',
                'amount' => 100,
            ]));
        $usrItem1->syncOriginal();

        // DB保存なしで、更新ありステータスで、インスタンスを用意
        $usrItem2 = $this->toUsrItemModel(UsrItem::factory()
            ->make([
                'usr_user_id' => $usrUserId,
                'mst_item_id' => '2',
                'amount' => 200,
            ]));

        // DB保存して、更新ありステータスで、インスタンスを用意
        $usrItem3 = $this->toUsrItemModel(UsrItem::factory()
            ->create([
                'usr_user_id' => $usrUserId,
                'mst_item_id' => '3',
                'amount' => 300,
            ]));
        $usrItem3->setItemAmount(333);

        $models = collect();
        $models->put(UsrItemRepository::class, [$usrItem1, $usrItem2, $usrItem3]);
        $this->setUsrModelManagerPrivateVariable('models', $models->all());

        // Exercise
        $this->execPrivateMethod($this->usrModelManager, 'saveModels', [UsrItemRepository::class]);

        // Verify
        $usrItems = UsrItem::where('usr_user_id', $usrUserId)->get()->keyBy(function (UsrItem $model) {
            return $model->getMstItemId();
        });
        $this->assertCount(3, $usrItems);

        // usrItem1
        $usrItem = $usrItems->get('1');
        $this->assertNotNull($usrItem->created_at);
        $this->assertEquals(
            $usrItem->created_at->toDateTimeString(),
            $usrItem->updated_at->toDateTimeString()
        );

        // usrItem2
        $usrItem = $usrItems->get('2');
        $this->assertNotNull($usrItem->created_at);
        $this->assertEquals(
            $usrItem->created_at->toDateTimeString(),
            $usrItem->updated_at->toDateTimeString()
        );

        // usrItem3
        $usrItem = $usrItems->get('3');
        $this->assertNotNull($usrItem->created_at);
        $this->assertTrue($usrItem->created_at->lte($usrItem->updated_at));
    }

    public function test_saveAll_変更ありのモデルを含むテーブルのRepository全てでsaveModelsを実行できていることを確認()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        $usrItem = $this->toUsrItemModel(UsrItem::factory()
            ->make([
                'usr_user_id' => $usrUserId,
                'mst_item_id' => '1',
                'amount' => 100,
            ]));
        $this->usrModelManager->syncModels(UsrItemRepository::class, [$usrItem]);

        $usrStageSession = UsrStageSession::factory()
            ->make([
                'usr_user_id' => $usrUserId,
                'mst_stage_id' => '1',
                'is_valid' => 0,
            ]);
        $this->usrModelManager->syncModels(UsrStageSessionRepository::class, [$usrStageSession]);

        $usrStage = UsrStage::factory()
            ->make([
                'usr_user_id' => $usrUserId,
                'mst_stage_id' => '1',
            ]);
        $usrStage->syncOriginal();
        $this->usrModelManager->syncModels(UsrStageRepository::class, [$usrStage]);

        // Exercise
        $this->saveAll();

        // Verify
        // usrItems
        $usrItem = UsrItem::query()->where('usr_user_id', $usrUserId)->where('mst_item_id', '1')->first();
        $this->assertNotNull($usrItem);

        $cache = array_values($this->getUsrModelManagerPrivateVariable('models')[UsrItemRepository::class])[0];
        $this->assertNotNull($cache);
        $this->assertFalse($cache->isChanged());

        $needSave = $this->getUsrModelManagerPrivateVariable('needSaves')[UsrItemRepository::class];
        $this->assertNotNull($needSave);
        $this->assertFalse($needSave);

        // usrStageSession
        $usrStageSession = UsrStageSession::where('usr_user_id', $usrUserId)->first();
        $this->assertNotNull($usrStageSession);

        $cache = array_values($this->getUsrModelManagerPrivateVariable('models')[UsrStageSessionRepository::class])[0];
        $this->assertNotNull($cache);
        $this->assertFalse($cache->isChanged());

        $needSave = $this->getUsrModelManagerPrivateVariable('needSaves')[UsrStageSessionRepository::class];
        $this->assertNotNull($needSave);
        $this->assertFalse($needSave);

        // usrStage
        $usrStage = UsrStage::where('usr_user_id', $usrUserId)->first();
        $this->assertNull($usrStage);

        $cache = array_values($this->getUsrModelManagerPrivateVariable('models')[UsrStageRepository::class])[0];
        $this->assertNotNull($cache);
        $this->assertFalse($cache->isChanged());

        $needSave = $this->getUsrModelManagerPrivateVariable('needSaves')[UsrStageRepository::class] ?? null;
        $this->assertNull($needSave);
    }

    public function test_getChangedModels_DB更新前に変更済みのモデルを取得できることを確認()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        $models = UsrItem::factory()
            ->count(3)
            ->make([
                'usr_user_id' => $usrUserId,
                'amount' => 100,
            ])
            ->map(function (UsrItem $model) {
                return $this->toUsrItemModel($model);
            })
            ->each->syncOriginal();

        $models->first()->setItemAmount(999);

        $this->usrModelManager->syncModels(UsrItemRepository::class, $models->toArray());

        // Exercise
        $result = $this->usrModelManager->getChangedModels(UsrItemRepository::class);

        // Verify
        $this->assertCount(1, $result);
        $this->assertEquals(999, $result->first()->getAmount());
    }

    public function test_getChangedModels_DB更新後に変更済みのモデルを取得できることを確認()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        $models = UsrItem::factory()
            ->count(3)
            ->make([
                'usr_user_id' => $usrUserId,
                'amount' => 100,
            ])
            ->map(function (UsrItem $model) {
                return $this->toUsrItemModel($model);
            })
            ->each->syncOriginal();

        $models->first()->setItemAmount(999);

        $this->usrModelManager->syncModels(UsrItemRepository::class, $models->toArray());
        $this->saveAll();

        // Exercise
        $result = $this->usrModelManager->getChangedModels(UsrItemRepository::class);

        // Verify
        $this->assertCount(1, $result);
        $this->assertEquals(999, $result->first()->getAmount());
    }

    // public function test_syncDeleteModels_キャッシュから指定したモデルを削除できることを確認()
    // {
    //     // Setup
    //     $usrUserId = $this->createUsrUser()->getId();
    //     $models = UsrItem::factory()
    //         ->count(4)
    //         ->make([
    //             'usr_user_id' => $usrUserId,
    //         ])
    //         ->map(function (UsrItem $model) {
    //             return $this->toUsrItemModel($model);
    //         });
    //     $this->usrModelManager->syncModels(UsrItemRepository::class, $models->take(3));

    //     // Exercise
    //     $this->usrModelManager->syncDeleteModels(
    //         UsrItemRepository::class,
    //         $models->take(2),
    //     );

    //     // Verify
    //     $cache = $this->getUsrModelManagerPrivateVariable('models')[UsrItemRepository::class];
    //     $this->assertCount(1, $cache);
    //     $this->assertEquals(
    //         $models->slice(2, 1)->first()->getId(),
    //         $cache->first()->getId(),
    //     );
    // }
}

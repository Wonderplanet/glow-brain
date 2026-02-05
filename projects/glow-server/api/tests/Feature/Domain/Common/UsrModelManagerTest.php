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
            return TestMultiModel::create('id'.(string)$i, $usrUserId, intValue: 0);
        })->all();
        $testMultiModels2 = collect([2, 3, 4])->map(function ($i) use ($usrUserId) {
            return TestMultiModel::create('id'.(string)$i, $usrUserId, intValue: (int) $i + 10);
        })->all();

        $testSingleModel = TestMultiModel::create('id1', $usrUserId, boolValue: false);

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

    public function test_syncModels_同一キーのモデルがある場合に、isChangedに関わらず上書きされることを確認()
    {
        /**
         * コミットed10035fbの修正により、syncModelsはisChanged=falseでも上書きするように変更された。
         * これは、コインの上限キャップシナリオでデグレを防ぐため。
         */

        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        $testMultiModels = collect([1, 2, 3])->map(function ($i) use ($usrUserId) {
            return TestMultiModel::create('id'.(string)$i, $usrUserId, intValue: $i * 100);
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

        // 上書きされる（isChanged=true）
        $testMultiModel2->int_value = 222;

        // 上書きされる（isChanged=falseでも上書きされる）
        $testMultiModel3->int_value = 333;
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
        // isChanged=falseでも上書きされる
        $this->assertEquals(333, $models->get('id3')->getIntValue());
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

    public function test_addModelsIfAbsent_キャッシュにないモデルを追加できることを確認()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        $testMultiModels = collect([1, 2, 3])->map(function ($i) use ($usrUserId) {
            $model = TestMultiModel::create('id'.(string)$i, $usrUserId, intValue: $i * 100);
            $model->syncOriginal();
            return $model;
        })->all();

        // Exercise
        $this->usrModelManager->addModelsIfAbsent(TestUsrModelMultiCacheRepository::class, $testMultiModels);

        // Verify
        $models = collect($this->getUsrModelManagerPrivateVariable('models')[TestUsrModelMultiCacheRepository::class])
            ->keyBy(function (TestMultiModel $model) {
                return $model->getId();
            });
        $this->assertEquals(3, $models->count());
        $this->assertEquals(100, $models->get('id1')->getIntValue());
        $this->assertEquals(200, $models->get('id2')->getIntValue());
        $this->assertEquals(300, $models->get('id3')->getIntValue());

        // 変更なしのモデル（syncOriginal済み）を追加したため、needSaveはfalse（キーが存在しない）
        $needSaves = $this->getUsrModelManagerPrivateVariable('needSaves');
        $this->assertArrayNotHasKey(TestUsrModelMultiCacheRepository::class, $needSaves);

        // changedModelKeysも変更なし（syncOriginal済みでisChanged=false）
        $changedModelKeys = $this->getUsrModelManagerPrivateVariable('changedModelKeys');
        $this->assertArrayNotHasKey(TestUsrModelMultiCacheRepository::class, $changedModelKeys);

        // isAllFetchedsも変更なし（addModelsでは変更しない）
        $isAllFetcheds = $this->getUsrModelManagerPrivateVariable('isAllFetcheds');
        $this->assertArrayNotHasKey(TestUsrModelMultiCacheRepository::class, $isAllFetcheds);
    }

    public function test_addModelsIfAbsent_キャッシュにあるモデルは上書きしないことを確認()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        // キャッシュに既存のモデルを設定
        $testMultiModels = collect([1, 2, 3])->map(function ($i) use ($usrUserId) {
            return TestMultiModel::create('id'.(string)$i, $usrUserId, intValue: $i * 100);
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

        // 既存のモデルと同じIDで値を変更したモデルを用意
        $testMultiModel2 = $testMultiModels->get('id2');
        $testMultiModel2->int_value = 9999;
        // isChangedがtrueの場合でも
        $testMultiModel3 = $testMultiModels->get('id3');
        $testMultiModel3->int_value = 8888;

        // Exercise
        // addModelsは既存のキャッシュを上書きしない
        $this->usrModelManager->addModelsIfAbsent(TestUsrModelMultiCacheRepository::class, [$testMultiModel2, $testMultiModel3]);

        // Verify
        // キャッシュが上書きされていないことを確認
        $models = collect($this->getUsrModelManagerPrivateVariable('models')[TestUsrModelMultiCacheRepository::class])
            ->keyBy(function (TestMultiModel $model) {
                return $model->getId();
            });
        $this->assertEquals(3, $models->count());

        // id2, id3は上書きされず、元の値が保持されていることを確認
        $this->assertEquals(100, $models->get('id1')->getIntValue());
        $this->assertEquals(200, $models->get('id2')->getIntValue());
        $this->assertEquals(300, $models->get('id3')->getIntValue());

        // 既存のキャッシュを上書きしないため、changedModelKeysも変更なし
        $changedModelKeys = $this->getUsrModelManagerPrivateVariable('changedModelKeys');
        $this->assertArrayNotHasKey(TestUsrModelMultiCacheRepository::class, $changedModelKeys);

        // isAllFetchedsも変更なし
        $isAllFetcheds = $this->getUsrModelManagerPrivateVariable('isAllFetcheds');
        $this->assertArrayNotHasKey(TestUsrModelMultiCacheRepository::class, $isAllFetcheds);
    }

    public function test_addModelsIfAbsent_新規と既存が混在する場合でも上書きせず追加だけできる()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        // キャッシュに既存のモデルを設定（id1, id2）
        $existingModels = collect([1, 2])->map(function ($i) use ($usrUserId) {
            return TestMultiModel::create('id'.(string)$i, $usrUserId, intValue: $i * 100);
        })->keyBy(function (TestMultiModel $model) {
            return $model->getId();
        });

        $models = collect();
        $models->put(
            TestUsrModelMultiCacheRepository::class,
            $existingModels->mapWithKeys(function (TestMultiModel $model) {
                return [$model->makeModelKey() => clone $model];
            })->all()
        );
        $this->setUsrModelManagerPrivateVariable('models', $models->all());

        // 既存モデル（id2）と新規モデル（id3, id4）を含むデータを用意
        $testMultiModel2 = $existingModels->get('id2');
        $testMultiModel2->int_value = 9999; // 既存モデルの値を変更
        $testMultiModel3 = TestMultiModel::create('id3', $usrUserId, intValue: 300);
        $testMultiModel4 = TestMultiModel::create('id4', $usrUserId, intValue: 400);

        // Exercise
        $this->usrModelManager->addModelsIfAbsent(
            TestUsrModelMultiCacheRepository::class,
            [$testMultiModel2, $testMultiModel3, $testMultiModel4]
        );

        // Verify
        $models = collect($this->getUsrModelManagerPrivateVariable('models')[TestUsrModelMultiCacheRepository::class])
            ->keyBy(function (TestMultiModel $model) {
                return $model->getId();
            });

        // 合計4つのモデルが存在することを確認
        $this->assertEquals(4, $models->count());

        // 既存モデル（id1, id2）は上書きされていない
        $this->assertEquals(100, $models->get('id1')->getIntValue());
        $this->assertEquals(200, $models->get('id2')->getIntValue());

        // 新規モデル（id3, id4）は追加されている
        $this->assertEquals(300, $models->get('id3')->getIntValue());
        $this->assertEquals(400, $models->get('id4')->getIntValue());

        // 新規モデルが追加されたため、needSaveがtrueになっていることを確認
        $needSave = $this->getUsrModelManagerPrivateVariable('needSaves')[TestUsrModelMultiCacheRepository::class];
        $this->assertTrue($needSave);

        // 新規モデル（id3, id4）がchangedModelKeysに追加されていることを確認
        $changedModelKeys = $this->getUsrModelManagerPrivateVariable('changedModelKeys')[TestUsrModelMultiCacheRepository::class];
        $this->assertArrayHasKey($testMultiModel3->makeModelKey(), $changedModelKeys);
        $this->assertArrayHasKey($testMultiModel4->makeModelKey(), $changedModelKeys);
        $this->assertCount(2, $changedModelKeys);

        // isAllFetchedsは変更なし
        $isAllFetcheds = $this->getUsrModelManagerPrivateVariable('isAllFetcheds');
        $this->assertArrayNotHasKey(TestUsrModelMultiCacheRepository::class, $isAllFetcheds);
    }

    public function test_addModelsIfAbsentは追加のみでsyncModelsは上書きすることを確認する()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        // 1. addModelsのテスト
        // キャッシュに既存のモデルを設定
        $testModel1 = TestMultiModel::create('id1', $usrUserId, intValue: 100);
        $models = collect();
        $models->put(
            TestUsrModelMultiCacheRepository::class,
            [$testModel1->makeModelKey() => clone $testModel1]
        );
        $this->setUsrModelManagerPrivateVariable('models', $models->all());

        // 既存モデルと同じIDで値を変更したモデルを用意
        $testModel1Updated = clone $testModel1;
        $testModel1Updated->int_value = 999;

        // Exercise: addModels
        $this->usrModelManager->addModelsIfAbsent(TestUsrModelMultiCacheRepository::class, [$testModel1Updated]);

        // Verify: addModelsは上書きしない
        $cachedModels = $this->getUsrModelManagerPrivateVariable('models')[TestUsrModelMultiCacheRepository::class];
        $this->assertEquals(100, $cachedModels[$testModel1->makeModelKey()]->getIntValue());

        // 既存キャッシュを上書きしないため、changedModelKeysも変更なし
        $changedModelKeys = $this->getUsrModelManagerPrivateVariable('changedModelKeys');
        $this->assertArrayNotHasKey(TestUsrModelMultiCacheRepository::class, $changedModelKeys);

        // isAllFetchedsも変更なし
        $isAllFetcheds = $this->getUsrModelManagerPrivateVariable('isAllFetcheds');
        $this->assertArrayNotHasKey(TestUsrModelMultiCacheRepository::class, $isAllFetcheds);

        // 2. syncModelsのテスト
        // 同じ状態でsyncModelsを実行
        $testModel1Updated2 = clone $testModel1;
        $testModel1Updated2->int_value = 888;

        // Exercise: syncModels
        $this->usrModelManager->syncModels(TestUsrModelMultiCacheRepository::class, [$testModel1Updated2]);

        // Verify: syncModelsは上書きする
        $cachedModels = $this->getUsrModelManagerPrivateVariable('models')[TestUsrModelMultiCacheRepository::class];
        $this->assertEquals(888, $cachedModels[$testModel1->makeModelKey()]->getIntValue());

        // syncModelsで上書きしたため、changedModelKeysにモデルキーが追加される
        $changedModelKeys = $this->getUsrModelManagerPrivateVariable('changedModelKeys')[TestUsrModelMultiCacheRepository::class];
        $this->assertArrayHasKey($testModel1->makeModelKey(), $changedModelKeys);
        $this->assertCount(1, $changedModelKeys);

        // isAllFetchedsは依然として変更なし（syncModelsでは設定しない）
        $isAllFetcheds = $this->getUsrModelManagerPrivateVariable('isAllFetcheds');
        $this->assertArrayNotHasKey(TestUsrModelMultiCacheRepository::class, $isAllFetcheds);
    }

    public function test_syncModels_コイン上限キャップシナリオでデグレしないことを確認()
    {
        /**
         * シナリオ具体例:
         * コインをシステム上限分所持している状態で、交換所で、コインを消費して、エンブレムを獲得するケース
         * 1. コイン999,999を所持している状態で、UsrUserParameterモデルインスタンスをキャッシュに追加
         * 2. 交換所でコインを500消費して、コイン999,499の状態でキャッシュを上書き
         * 3. エンブレムを獲得したが、重複していてコイン1000に変換され、上限キャップにかかり、コイン999,999になる
         * 4. コイン999,999 の状態でキャッシュを上書き
         *    -> syncModelsはisChanged=falseでも上書きするため、正しくコイン999,999に更新される
         *    バグ修正前はここで上書きされず、コイン999,499のままになってしまっていた。
         */

        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        // 1. 初期状態: コイン999,999をキャッシュに追加
        $coinModel = TestMultiModel::create('coin', $usrUserId, intValue: 999999);
        $this->usrModelManager->syncModels(TestUsrModelMultiCacheRepository::class, [$coinModel]);

        // キャッシュの状態確認
        $cachedModels = $this->getUsrModelManagerPrivateVariable('models')[TestUsrModelMultiCacheRepository::class];
        $this->assertEquals(999999, $cachedModels[$coinModel->makeModelKey()]->getIntValue());

        // 2. コインを500消費: コイン999,499に更新
        $coinModel->int_value = 999499;
        $this->usrModelManager->syncModels(TestUsrModelMultiCacheRepository::class, [$coinModel]);

        // キャッシュの状態確認
        $cachedModels = $this->getUsrModelManagerPrivateVariable('models')[TestUsrModelMultiCacheRepository::class];
        $this->assertEquals(999499, $cachedModels[$coinModel->makeModelKey()]->getIntValue());

        // 3. エンブレム重複でコイン1000に変換され、上限キャップでコイン999,999に戻る
        $coinModel->int_value = 999999;
        // DBから取得した状態と同じになったため、isChangedはfalseになる
        $coinModel->syncOriginal();
        $this->assertFalse($coinModel->isChanged());

        // 4. syncModelsはisChanged=falseでもキャッシュを上書きする
        $this->usrModelManager->syncModels(TestUsrModelMultiCacheRepository::class, [$coinModel]);

        // Verify: キャッシュが正しく999,999に更新されていることを確認
        $cachedModels = $this->getUsrModelManagerPrivateVariable('models')[TestUsrModelMultiCacheRepository::class];
        $this->assertEquals(999999, $cachedModels[$coinModel->makeModelKey()]->getIntValue());
    }

    public function test_addModelsIfAbsent_コイン上限キャップシナリオでデグレしないことを確認()
    {
        /**
         * test_syncModels_コイン上限キャップシナリオでデグレしないことを確認のaddModels版
         */

        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        // 1. ビジネスロジックでコインを変更: 999,999 -> 999,499
        $coinModelInLogic = TestMultiModel::create('coin', $usrUserId, intValue: 999999);
        $coinModelInLogic->syncOriginal();
        $this->usrModelManager->syncModels(TestUsrModelMultiCacheRepository::class, [$coinModelInLogic]);

        // コインを500消費
        $coinModelInLogic->int_value = 999499;
        $this->usrModelManager->syncModels(TestUsrModelMultiCacheRepository::class, [$coinModelInLogic]);

        // キャッシュの状態確認: コイン999,499
        $cachedModels = $this->getUsrModelManagerPrivateVariable('models')[TestUsrModelMultiCacheRepository::class];
        $this->assertEquals(999499, $cachedModels[$coinModelInLogic->makeModelKey()]->getIntValue());

        // syncModelsで変更したため、changedModelKeysにモデルキーが追加される
        $changedModelKeys = $this->getUsrModelManagerPrivateVariable('changedModelKeys')[TestUsrModelMultiCacheRepository::class];
        $this->assertArrayHasKey($coinModelInLogic->makeModelKey(), $changedModelKeys);

        // 2. DBから古いデータ（コイン999,999）を取得してキャッシュに詰めようとする
        // （実際のケースでは、cachedGetManyやcachedGetAllがDBから取得したデータ）
        $coinModelFromDB = TestMultiModel::create('coin', $usrUserId, intValue: 999999);
        $coinModelFromDB->syncOriginal();

        // addModelsを使うことで、既存キャッシュを保護
        $this->usrModelManager->addModelsIfAbsent(TestUsrModelMultiCacheRepository::class, [$coinModelFromDB]);

        // Verify: キャッシュがDBの古いデータで上書きされず、999,499のまま保持されていることを確認
        $cachedModels = $this->getUsrModelManagerPrivateVariable('models')[TestUsrModelMultiCacheRepository::class];
        $this->assertEquals(999499, $cachedModels[$coinModelInLogic->makeModelKey()]->getIntValue());

        // addModelsは既存キャッシュを上書きしないため、changedModelKeysは変更なし（syncModels時のまま）
        $changedModelKeys = $this->getUsrModelManagerPrivateVariable('changedModelKeys')[TestUsrModelMultiCacheRepository::class];
        $this->assertArrayHasKey($coinModelInLogic->makeModelKey(), $changedModelKeys);
        $this->assertCount(1, $changedModelKeys);

        // isAllFetchedsは変更なし
        $isAllFetcheds = $this->getUsrModelManagerPrivateVariable('isAllFetcheds');
        $this->assertArrayNotHasKey(TestUsrModelMultiCacheRepository::class, $isAllFetcheds);
    }

    public function test_addModelsIfAbsent_空配列の場合は何もしないことを確認()
    {
        // Setup

        // Exercise
        $this->usrModelManager->addModelsIfAbsent(TestUsrModelMultiCacheRepository::class, []);

        // Verify
        // キャッシュが作成されていないことを確認
        $models = $this->getUsrModelManagerPrivateVariable('models');
        $this->assertArrayNotHasKey(TestUsrModelMultiCacheRepository::class, $models);

        // changedModelKeysも作成されていない
        $changedModelKeys = $this->getUsrModelManagerPrivateVariable('changedModelKeys');
        $this->assertArrayNotHasKey(TestUsrModelMultiCacheRepository::class, $changedModelKeys);

        // isAllFetchedsも作成されていない
        $isAllFetcheds = $this->getUsrModelManagerPrivateVariable('isAllFetcheds');
        $this->assertArrayNotHasKey(TestUsrModelMultiCacheRepository::class, $isAllFetcheds);
    }
}

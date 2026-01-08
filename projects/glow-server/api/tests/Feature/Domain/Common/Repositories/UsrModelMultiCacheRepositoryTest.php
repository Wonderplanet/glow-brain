<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Common\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use Tests\TestCase;

class UsrModelMultiCacheRepositoryTest extends TestCase
{
    private TestUsrModelMultiCacheRepository $testUsrModelMultiCacheRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testUsrModelMultiCacheRepository = $this->app->make(
            TestUsrModelMultiCacheRepository::class
        );
    }

    /**
     * @test
     */
    public function syncModels_指定したモデルのみをキャッシュへ追加できることを確認(): void
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $ids = collect(['1', '2', '3']);
        $models = $ids->map(function ($i) use ($usrUserId) {
            return TestMultiModel::create($i, $usrUserId);
        });

        // Exercise
        $this->testUsrModelMultiCacheRepository->syncModels($models);

        // Verify
        $cache = $this->getUsrModelManagerPrivateVariable('models')[TestUsrModelMultiCacheRepository::class];
        $this->assertEquals(
            $ids->values()->toArray(),
            array_values(array_map(function ($model) {
                return $model->getId();
            }, $cache))
        );
    }

    /**
     * @test
     */
    public function syncModel_モデルをキャッシュへ追加できることを確認(): void
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $id = '1';
        $test = TestMultiModel::create($id, $usrUserId);

        // Exercise
        $this->testUsrModelMultiCacheRepository->syncModel($test);

        // Verify
        $this->assertEquals(
            $test,
            array_values($this->getUsrModelManagerPrivateVariable('models')[TestUsrModelMultiCacheRepository::class])[0],
        );
    }

    /**
     * @test
     */
    public function getCache_キャッシュ済のモデルを取得できることを確認(): void
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $id = '1';
        $test = TestMultiModel::create($id, $usrUserId);
        $this->setUsrModelManagerPrivateVariable(
            'models',
            [TestUsrModelMultiCacheRepository::class => [$test->makeModelKey() => $test]],
        );

        // Exercise
        $models = $this->execPrivateMethod($this->testUsrModelMultiCacheRepository, 'getCache', [$usrUserId]);

        // Verify
        $this->assertEquals($test, array_values($models)[0]);
    }

    /**
     * @test
     */
    public function cachedGetOneWhere_キャッシュから取得していることを確認(): void
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $testFalse = TestMultiModel::create('1', $usrUserId, stringValue: 'cache', boolValue: false);
        $testTrue = TestMultiModel::create('2', $usrUserId, stringValue: 'cache', boolValue: true);
        $this->setUsrModelManagerPrivateVariable(
            'models',
            [
                TestUsrModelMultiCacheRepository::class => [
                    $testFalse->makeModelKey() => $testFalse,
                    $testTrue->makeModelKey() => $testTrue,
                ]
            ]
        );

        // Exercise
        $model = $this->execPrivateMethod($this->testUsrModelMultiCacheRepository, 'cachedGetOneWhere', [
            $usrUserId,
            'bool_value',
            true,
            function () use ($testTrue) {
                $testTrue->string_value = 'callBack';
                return $testTrue;
            },
        ]);

        // Verify
        $this->assertEquals('cache', $model->getStringValue());
        $this->assertEquals($testTrue->getId(), $model->getId());
    }

    /**
     * @test
     */
    public function cachedGetOneWhere_キャッシュにないのでcallBackから取得していることを確認(): void
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $testFalse = TestMultiModel::create('1', $usrUserId, stringValue: 'cache', boolValue: false);
        $testTrue = TestMultiModel::create('2', $usrUserId, stringValue: 'cache', boolValue: true);

        // Exercise
        $model = $this->execPrivateMethod($this->testUsrModelMultiCacheRepository, 'cachedGetOneWhere', [
            $usrUserId,
            'bool_value',
            true,
            function () use ($testTrue) {
                $testTrue->string_value = 'callBack';
                return $testTrue;
            },
        ]);

        // Verify
        $this->assertEquals('callBack', $model->getStringValue());
        $this->assertEquals($testTrue->getId(), $model->getId());
    }

    /**
     * @test
     */
    public function cachedGetOneWhere_取得データが2個以上の場合はエラー(): void
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $test1 = TestMultiModel::create('1', $usrUserId, stringValue: 'cache', boolValue: true);
        $test2 = TestMultiModel::create('2', $usrUserId, stringValue: 'cache', boolValue: true);

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::INVALID_PARAMETER);

        // Exercise
        $models = $this->execPrivateMethod($this->testUsrModelMultiCacheRepository, 'cachedGetOneWhere', [
            $usrUserId,
            'bool_value',
            true,
            function () use ($test1, $test2) {
                $test1->string_value = 'callBack';
                $test2->string_value = 'callBack';
                return collect([$test1, $test2]);
            },
        ]);

        // Verify
    }

    /**
     * @test
     */
    public function cachedGetAll_全てキャッシュから取得していることを確認(): void
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $testFalse = TestMultiModel::create('1', $usrUserId, stringValue: 'cache', boolValue: false);
        $testTrue = TestMultiModel::create('2', $usrUserId, stringValue: 'cache', boolValue: true);
        $this->setUsrModelManagerPrivateVariable(
            'models',
            [
                TestUsrModelMultiCacheRepository::class => [
                    $testFalse->makeModelKey() => clone $testFalse,
                    $testTrue->makeModelKey() => clone $testTrue,
                ]
            ]
        );
        $this->setUsrModelManagerPrivateVariable(
            'isAllFetcheds',
            [TestUsrModelMultiCacheRepository::class => true]
        );

        // Exercise
        $models = $this->execPrivateMethod($this->testUsrModelMultiCacheRepository, 'cachedGetAll', [
            $usrUserId,
        ]);

        // Verify
        $models = $models->keyBy(function ($model) {
            return $model->getId();
        });
        $this->assertEquals('cache', $models->get('1')->getStringValue());
        $this->assertEquals('cache', $models->get('2')->getStringValue());

        // キャッシュから取得しているので、changedModelKeysは変更なし
        $changedModelKeys = $this->getUsrModelManagerPrivateVariable('changedModelKeys');
        $this->assertArrayNotHasKey(TestUsrModelMultiCacheRepository::class, $changedModelKeys);

        // isAllFetchedsは既にtrueが設定されている（setUp時の設定がそのまま）
        $isAllFetcheds = $this->getUsrModelManagerPrivateVariable('isAllFetcheds');
        $this->assertArrayHasKey(TestUsrModelMultiCacheRepository::class, $isAllFetcheds);
        $this->assertTrue($isAllFetcheds[TestUsrModelMultiCacheRepository::class]);
    }

    /**
     * @test
     */
    public function cachedGetAll_全てキャッシュにないのでDBから取得していることを確認(): void
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        $test1 = TestMultiModel::create('1', $usrUserId, stringValue: 'db');
        $test1->syncOriginal(); // DBから取得した状態にする
        $test2 = TestMultiModel::create('2', $usrUserId, stringValue: 'db');
        $test2->syncOriginal(); // DBから取得した状態にする

        $testUsrModelMultiCacheRepository = $this->createPartialMock(
            TestUsrModelMultiCacheRepository::class,
            ['dbSelectAll']
        );
        $reflection = new \ReflectionClass(TestUsrModelMultiCacheRepository::class);
        $constructor = $reflection->getConstructor();
        $constructor->invoke($testUsrModelMultiCacheRepository, $this->usrModelManager);
        $testUsrModelMultiCacheRepository->expects($this->once())
            ->method('dbSelectAll')
            ->with($usrUserId)
            ->willReturn(collect([$test1, $test2]));

        // Exercise
        $models = $this->execPrivateMethod($testUsrModelMultiCacheRepository, 'cachedGetAll', [
            $usrUserId,
        ]);


        // Verify
        $this->assertCount(2, $models);

        // DBから全件取得したので、changedModelKeysは追加されない（syncOriginal済み）
        $changedModelKeys = $this->getUsrModelManagerPrivateVariable('changedModelKeys');
        $this->assertArrayNotHasKey($testUsrModelMultiCacheRepository::class, $changedModelKeys);

        // DBから全件取得したので、isAllFetcheds=trueが設定される
        $isAllFetcheds = $this->getUsrModelManagerPrivateVariable('isAllFetcheds');
        $this->assertArrayHasKey($testUsrModelMultiCacheRepository::class, $isAllFetcheds);
        $this->assertTrue($isAllFetcheds[$testUsrModelMultiCacheRepository::class]);
    }

    public function test_addModelsIfAbsent_指定したモデルのみをキャッシュへ追加できることを確認(): void
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $ids = collect(['1', '2', '3']);
        $models = $ids->map(function ($i) use ($usrUserId) {
            return TestMultiModel::create($i, $usrUserId);
        });

        // Exercise
        $this->execPrivateMethod($this->testUsrModelMultiCacheRepository, 'addModelsIfAbsent', [$models]);

        // Verify
        $cache = $this->getUsrModelManagerPrivateVariable('models')[TestUsrModelMultiCacheRepository::class];
        $this->assertEquals(
            $ids->values()->toArray(),
            array_values(array_map(function ($model) {
                return $model->getId();
            }, $cache))
        );
    }

    public function test_addModelsIfAbsent_キャッシュにあるモデルは上書きしないことを確認(): void
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        // キャッシュに既存データを設定（id1のintValueは100）
        $test1 = TestMultiModel::create('1', $usrUserId, intValue: 100);
        $this->setUsrModelManagerPrivateVariable(
            'models',
            [
                TestUsrModelMultiCacheRepository::class => [
                    $test1->makeModelKey() => $test1,
                ]
            ]
        );

        // 既存データと同じIDで値を変更したモデルを用意（id1のintValueを999に変更）
        $test1Updated = TestMultiModel::create('1', $usrUserId, intValue: 999);
        $test2 = TestMultiModel::create('2', $usrUserId, intValue: 200);

        // Exercise
        $this->execPrivateMethod($this->testUsrModelMultiCacheRepository, 'addModelsIfAbsent', [collect([$test1Updated, $test2])]);

        // Verify
        $cache = $this->getUsrModelManagerPrivateVariable('models')[TestUsrModelMultiCacheRepository::class];
        $this->assertCount(2, $cache);

        $models = collect($cache)->keyBy(function ($model) {
            return $model->getId();
        });

        // id1はキャッシュにあったため、上書きされず元の値が保持されている
        $this->assertEquals(100, $models->get('1')->getIntValue());

        // id2は新規追加されている
        $this->assertEquals(200, $models->get('2')->getIntValue());
    }

    public function test_addModelIfAbsent_単一モデルをキャッシュへ追加できることを確認(): void
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $test = TestMultiModel::create('1', $usrUserId);

        // Exercise
        $this->execPrivateMethod($this->testUsrModelMultiCacheRepository, 'addModelIfAbsent', [$test]);

        // Verify
        $cache = $this->getUsrModelManagerPrivateVariable('models')[TestUsrModelMultiCacheRepository::class];
        $this->assertCount(1, $cache);
        $this->assertEquals('1', array_values($cache)[0]->getId());
    }

    public function test_addModelsIfAbsent_空配列の場合は何もしないことを確認(): void
    {
        // Setup

        // Exercise
        $this->execPrivateMethod($this->testUsrModelMultiCacheRepository, 'addModelsIfAbsent', [collect([])]);

        // Verify
        $models = $this->getUsrModelManagerPrivateVariable('models');
        $this->assertArrayNotHasKey(TestUsrModelMultiCacheRepository::class, $models);
    }

    public function test_cachedGetMany_DBから取得したデータでキャッシュを上書きしないことを確認(): void
    {
        /**
         * cachedGetManyでDBから取得する際、addModelsIfAbsentを使用しているため、
         * 既にキャッシュにあるモデルは上書きされないことを確認
         */

        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        // キャッシュに既存データを設定（id1のintValueは100）
        $test1 = TestMultiModel::create('1', $usrUserId, stringValue: 'cache', intValue: 100);
        $test1->syncOriginal(); // DBから取得した状態にする
        $this->setUsrModelManagerPrivateVariable(
            'models',
            [
                TestUsrModelMultiCacheRepository::class => [
                    $test1->makeModelKey() => $test1,
                ]
            ]
        );

        // DBから取得するデータを準備（id1のintValueは999に変更されている想定）
        $test1FromDB = TestMultiModel::create('1', $usrUserId, stringValue: 'db', intValue: 999);
        $test1FromDB->syncOriginal(); // DBから取得した状態にする
        $test2FromDB = TestMultiModel::create('2', $usrUserId, stringValue: 'db', intValue: 200);
        $test2FromDB->syncOriginal(); // DBから取得した状態にする

        // Exercise
        $models = $this->execPrivateMethod($this->testUsrModelMultiCacheRepository, 'cachedGetMany', [
            $usrUserId,
            function ($cache) {
                return $cache->filter(function ($model) {
                    return in_array($model->getId(), ['1', '2']);
                });
            },
            null, // expectedCountをnullにすることで、DBから取得する
            function () use ($test1FromDB, $test2FromDB) {
                return collect([$test1FromDB, $test2FromDB]);
            },
        ]);

        // Verify
        $this->assertCount(2, $models);
        $models = $models->keyBy(function ($model) {
            return $model->getId();
        });

        // id1はキャッシュにあったため、DBから取得したデータで上書きされず、元の値が保持されている
        $this->assertEquals('cache', $models->get('1')->getStringValue());
        $this->assertEquals(100, $models->get('1')->getIntValue());

        // id2はキャッシュになかったため、DBから取得したデータが追加されている
        $this->assertEquals('db', $models->get('2')->getStringValue());
        $this->assertEquals(200, $models->get('2')->getIntValue());
    }

    public function test_cachedGetAll_一部キャッシュにないのでDBから取得した際にキャッシュを上書きしないことを確認(): void
    {
        /**
         * cachedGetAllでDBから取得する際、addModelsIfAbsentを使用しているため、
         * 既にキャッシュにあるモデルは上書きされないことを確認
         */

        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        // キャッシュに既存データを設定（id1とid2、id1のintValueは100、id2のintValueは200）
        $test1 = TestMultiModel::create('1', $usrUserId, stringValue: 'cache', intValue: 100);
        $test1->syncOriginal(); // DBから取得した状態にする
        $test2 = TestMultiModel::create('2', $usrUserId, stringValue: 'cache', intValue: 200);
        $test2->syncOriginal(); // DBから取得した状態にする

        // DBから取得するデータを準備（id1のintValueが999、id2のintValueが888に変更されている想定）
        $test1FromDB = TestMultiModel::create('1', $usrUserId, stringValue: 'db', intValue: 999);
        $test1FromDB->syncOriginal(); // DBから取得した状態にする
        $test2FromDB = TestMultiModel::create('2', $usrUserId, stringValue: 'db', intValue: 888);
        $test2FromDB->syncOriginal(); // DBから取得した状態にする
        $test3FromDB = TestMultiModel::create('3', $usrUserId, stringValue: 'db', intValue: 300);
        $test3FromDB->syncOriginal(); // DBから取得した状態にする

        // mock
        $mockRepository = \Mockery::mock(
            TestUsrModelMultiCacheRepository::class,
            [$this->usrModelManager],
        )->makePartial();
        $mockRepository->shouldAllowMockingProtectedMethods();
        $mockRepository->shouldReceive('dbSelectAll')
            ->with($usrUserId)
            ->andReturn(collect([$test1FromDB, $test2FromDB, $test3FromDB]));

        // キャッシュをセットしておく
        $this->setUsrModelManagerPrivateVariable(
            'models',
            [
                $mockRepository::class => [
                    $test1->makeModelKey() => $test1,
                    $test2->makeModelKey() => $test2,
                ]
            ]
        );

        // Exercise
        $models = $this->execPrivateMethod($mockRepository, 'cachedGetAll', [
            $usrUserId,
        ]);

        // Verify
        $this->assertCount(3, $models);
        $models = $models->keyBy(function ($model) {
            return $model->getId();
        });

        // id1とid2はキャッシュにあったため、DBから取得したデータで上書きされず、元の値が保持されている
        $this->assertEquals('cache', $models->get('1')->getStringValue(), 'id1 string');
        $this->assertEquals(100, $models->get('1')->getIntValue(), 'id1 int');
        $this->assertEquals('cache', $models->get('2')->getStringValue(), 'id2 string');
        $this->assertEquals(200, $models->get('2')->getIntValue(), 'id2 int');

        // id3はキャッシュになかったため、DBから取得したデータが追加されている
        $this->assertEquals('db', $models->get('3')->getStringValue(), 'id3 string');
        $this->assertEquals(300, $models->get('3')->getIntValue(), 'id3 int');

        // DBから全件取得したので、changedModelKeysは追加されない（syncOriginal済み）
        $changedModelKeys = $this->getUsrModelManagerPrivateVariable('changedModelKeys');
        $this->assertArrayNotHasKey($mockRepository::class, $changedModelKeys);

        // DBから全件取得したので、isAllFetcheds=trueが設定される
        $isAllFetcheds = $this->getUsrModelManagerPrivateVariable('isAllFetcheds');
        $this->assertArrayHasKey($mockRepository::class, $isAllFetcheds);
        $this->assertTrue($isAllFetcheds[$mockRepository::class]);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Common\Repositories;

use Tests\TestCase;

class UsrModelSingleCacheRepositoryTest extends TestCase
{
    private TestUsrModelSingleCacheRepository $testUsrModelSingleCacheRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testUsrModelSingleCacheRepository = $this->app->make(
            TestUsrModelSingleCacheRepository::class
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
        $this->testUsrModelSingleCacheRepository->syncModel($test);

        // Verify
        $this->assertEquals($test, array_values($this->getUsrModelManagerPrivateVariable('models')[TestUsrModelSingleCacheRepository::class])[0]);
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
        $this->setUsrModelManagerPrivateVariable('models', [TestUsrModelSingleCacheRepository::class => [$test]]);

        // Exercise
        $models = $this->execPrivateMethod($this->testUsrModelSingleCacheRepository, 'getCache', [$usrUserId]);

        // Verify
        $this->assertEquals($test, array_values($models)[0]);
    }

    public function test_addModelIfAbsent_モデルをキャッシュへ追加できることを確認(): void
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $id = '1';
        $test = TestMultiModel::create($id, $usrUserId);

        // Exercise
        $this->execPrivateMethod($this->testUsrModelSingleCacheRepository, 'addModelIfAbsent', [$test]);

        // Verify
        $this->assertEquals($test, array_values($this->getUsrModelManagerPrivateVariable('models')[TestUsrModelSingleCacheRepository::class])[0]);
    }

    public function test_addModelIfAbsent_キャッシュにあるモデルは上書きしないことを確認(): void
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        // キャッシュに既存データを設定（intValueは100）
        $test1 = TestMultiModel::create('1', $usrUserId, intValue: 100);
        $this->setUsrModelManagerPrivateVariable('models', [TestUsrModelSingleCacheRepository::class => [$test1]]);

        // 既存データと同じIDで値を変更したモデルを用意（intValueを999に変更）
        $test1Updated = TestMultiModel::create('1', $usrUserId, intValue: 999);

        // Exercise
        $this->execPrivateMethod($this->testUsrModelSingleCacheRepository, 'addModelIfAbsent', [$test1Updated]);

        // Verify
        $cache = array_values($this->getUsrModelManagerPrivateVariable('models')[TestUsrModelSingleCacheRepository::class])[0];
        // キャッシュにあったため、上書きされず元の値が保持されている
        $this->assertEquals(100, $cache->getIntValue());
    }

    public function test_cachedGetOne_キャッシュから取得していることを確認(): void
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $model = TestMultiModel::create('1', $usrUserId, stringValue: 'db');

        $testUsrModelSingleCacheRepository = $this->createPartialMock(
            TestUsrModelSingleCacheRepository::class,
            ['dbSelectOne']
        );
        $reflection = new \ReflectionClass(TestUsrModelSingleCacheRepository::class);
        $constructor = $reflection->getConstructor();
        $constructor->invoke($testUsrModelSingleCacheRepository, $this->usrModelManager);
        $testUsrModelSingleCacheRepository->expects($this->once())
            ->method('dbSelectOne')
            ->with($usrUserId)
            ->willReturn($model);

        $model->string_value = 'cache';
        $this->setUsrModelManagerPrivateVariable(
            'models',
            [TestUsrModelSingleCacheRepository::class => [$model]]
        );

        // Exercise
        $model = $this->execPrivateMethod($testUsrModelSingleCacheRepository, 'cachedGetOne', [
            $usrUserId,
        ]);

        // Verify
        $this->assertNotNull($model);
        $this->assertEquals('cache', $model->getStringValue());

        // キャッシュから取得しているので、changedModelKeysは変更なし
        $changedModelKeys = $this->getUsrModelManagerPrivateVariable('changedModelKeys');
        $this->assertArrayNotHasKey(TestUsrModelSingleCacheRepository::class, $changedModelKeys);
    }

    public function test_cachedGetOne_キャッシュにないのでDBから取得していることを確認(): void
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $model = TestMultiModel::create('1', $usrUserId, stringValue: 'db');
        $model->syncOriginal(); // DBから取得した状態にする

        $testUsrModelSingleCacheRepository = $this->createPartialMock(
            TestUsrModelSingleCacheRepository::class,
            ['dbSelectOne']
        );
        $reflection = new \ReflectionClass(TestUsrModelSingleCacheRepository::class);
        $constructor = $reflection->getConstructor();
        $constructor->invoke($testUsrModelSingleCacheRepository, $this->usrModelManager);
        $testUsrModelSingleCacheRepository->expects($this->once())
            ->method('dbSelectOne')
            ->with($usrUserId)
            ->willReturn($model);

        // Exercise
        $model = $this->execPrivateMethod($testUsrModelSingleCacheRepository, 'cachedGetOne', [
            $usrUserId,
        ]);

        // Verify
        $this->assertNotNull($model);
        $this->assertEquals('db', $model->getStringValue());

        // DBから取得したので、changedModelKeysは追加されない（syncOriginal済み）
        $changedModelKeys = $this->getUsrModelManagerPrivateVariable('changedModelKeys');
        $this->assertArrayNotHasKey($testUsrModelSingleCacheRepository::class, $changedModelKeys);

        // DBから取得したので、isAllFetcheds=trueが設定される（SingleCacheRepositoryでもmarkAllFetched()を呼ぶ）
        $isAllFetcheds = $this->getUsrModelManagerPrivateVariable('isAllFetcheds');
        $this->assertArrayHasKey($testUsrModelSingleCacheRepository::class, $isAllFetcheds);
        $this->assertTrue($isAllFetcheds[$testUsrModelSingleCacheRepository::class]);
    }

    public function test_cachedGetOne_DBから取得したデータでキャッシュを上書きしないことを確認(): void
    {
        /**
         * cachedGetOneでDBから取得する際、addModelIfAbsentを使用しているため、
         * 既にキャッシュにあるモデルは上書きされないことを確認
         */

        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        // キャッシュに既存データを設定（intValueは100）
        $modelInCache = TestMultiModel::create('1', $usrUserId, stringValue: 'cache', intValue: 100);
        $modelInCache->syncOriginal(); // DBから取得した状態にする

        // DBから取得するデータを準備（intValueが999に変更されている想定）
        $modelFromDB = TestMultiModel::create('1', $usrUserId, stringValue: 'db', intValue: 999);
        $modelFromDB->syncOriginal(); // DBから取得した状態にする

        // mock
        $mockRepository = \Mockery::mock(
            TestUsrModelSingleCacheRepository::class,
            [$this->usrModelManager],
        )->makePartial();
        $mockRepository->shouldAllowMockingProtectedMethods();
        $mockRepository->shouldReceive('dbSelectOne')
            ->with($usrUserId)
            ->andReturn($modelFromDB);

        // キャッシュをセットしておく
        $this->setUsrModelManagerPrivateVariable(
            'models',
            [$mockRepository::class => [$modelInCache->makeModelKey() => $modelInCache]]
        );

        // Exercise
        $model = $this->execPrivateMethod($mockRepository, 'cachedGetOne', [
            $usrUserId,
        ]);

        // Verify
        $this->assertNotNull($model);

        // キャッシュにあったため、DBから取得したデータで上書きされず、元の値が保持されている
        $this->assertEquals('cache', $model->getStringValue());
        $this->assertEquals(100, $model->getIntValue());

        // DBから取得したが上書きしないため、changedModelKeysは追加されない
        $changedModelKeys = $this->getUsrModelManagerPrivateVariable('changedModelKeys');
        $this->assertArrayNotHasKey($mockRepository::class, $changedModelKeys);
    }
}

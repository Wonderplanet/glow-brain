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
        $testFalse = TestMultiModel::create('1', $usrUserId, stringValue: 'cache', boolValue: false, isChanged: false);
        $testTrue = TestMultiModel::create('2', $usrUserId, stringValue: 'cache', boolValue: true, isChanged: false);
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
    }

    /**
     * @test
     */
    public function cachedGetAll_全てキャッシュにないのでDBから取得していることを確認(): void
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        $test1 = TestMultiModel::create('1', $usrUserId, stringValue: 'db');
        $test2 = TestMultiModel::create('2', $usrUserId, stringValue: 'db');

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
    }

    /**
     * @test
     */
    public function cachedGetAll_一部キャッシュにないのでDBから取得していることを確認(): void
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $test1 = TestMultiModel::create('1', $usrUserId, stringValue: 'cache');
        $test2 = TestMultiModel::create('2', $usrUserId, stringValue: 'cache');
        $test3 = TestMultiModel::create('3', $usrUserId, stringValue: 'db');

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
            ->willReturn(collect([$test1, $test2, $test3]));

        $this->setUsrModelManagerPrivateVariable(
            'models',
            [
                TestUsrModelMultiCacheRepository::class => [
                    $test1->makeModelKey() => $test1,
                    $test2->makeModelKey() => $test2,
                ]
            ]
        );

        // Exercise
        $models = $this->execPrivateMethod($testUsrModelMultiCacheRepository, 'cachedGetAll', [
            $usrUserId,
        ]);

        // Verify
        $this->assertCount(3, $models);
        $models = $models->keyBy(function ($model) {
            return $model->getId();
        });
        $this->assertEquals('cache', $models->get('1')->getStringValue());
        $this->assertEquals('cache', $models->get('2')->getStringValue());
        $this->assertEquals('db', $models->get('3')->getStringValue());
    }
}

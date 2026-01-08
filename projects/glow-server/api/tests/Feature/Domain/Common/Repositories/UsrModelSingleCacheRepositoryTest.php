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

    /**
     * @test
     */
    public function cachedGetOne_キャッシュから取得していることを確認(): void
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
    }

    /**
     * @test
     */
    public function cachedGetOne_キャッシュにないのでDBから取得していることを確認(): void
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

        // Exercise
        $model = $this->execPrivateMethod($testUsrModelSingleCacheRepository, 'cachedGetOne', [
            $usrUserId,
        ]);

        // Verify
        $this->assertNotNull($model);
        $this->assertEquals('db', $model->getStringValue());
    }
}

<?php

namespace MasterAssetReleaseAdmin\Unit\Services;

use Tests\Traits\ReflectionTrait;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\ClassSearchService;
use WonderPlanet\Tests\TestCase;

class ClassSearchServiceTest extends TestCase
{
    use ReflectionTrait;

    private ClassSearchService $service;


    public function setUp(): void
    {
        parent::setUp();
        $this->service = app()->make(ClassSearchService::class);
    }

    /**
     * @test
     */
    public function verifyMasterModelClassName_存在するクラスのチェック(): void
    {    
        $classNames = ['MstItem', 'OprCoinProduct'];

        foreach ($classNames as $className) {
            // Exercise
            $res = $this->service->verifyMasterModelClassName($className);

            $this->assertTrue($res);
        }
    }

    /**
     * @test
     */
    public function verifyMasterModelClassName_存在しないクラスのチェック(): void
    {    
        $className = 'MstHoge';

        // Exercise
        $res = $this->service->verifyMasterModelClassName($className);

        $this->assertFalse($res);
    }

    /**
     * @test
     */
    public function createMasterModelClass_存在するクラスのチェック(): void
    {    
        $classNames = ['MstItem', 'OprCoinProduct'];

        foreach ($classNames as $className) {
            // Exercise
            $instance = $this->service->createMasterModelClass($className);

            $this->assertEquals(new (config('wp_master_asset_release_admin.masterResourceModelsPath.'. strtolower(substr($className, 0, 3))) . $className), $instance);
        }
    }

    /**
     * @test
     */
    public function createMasterModelClass_存在しないクラスのチェック(): void
    {    
        $className = 'MstHoge';

        // Exercise
        $instance = $this->service->createMasterModelClass($className);

        $this->assertEquals(null, $instance);
    }
}

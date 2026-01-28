<?php

namespace MasterAssetReleaseAdmin\Unit\Operators;

use WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators\MasterDataDBOperator;
use WonderPlanet\Tests\TestCase;

class MasterDataDBOperatorTest extends TestCase
{
    private MasterDataDBOperator $masterDataDBOperator;

    public function setUp(): void
    {
        parent::setUp();

        $this->masterDataDBOperator = app(MasterDataDBOperator::class);
    }

    /**
     * @test
     */
    public function drop_DB削除チェック(): void
    {
        // Setup
        $releaseKey = 'unit';
        $serverDbHashMap = [
            'unit' => '_test',
        ];
        $masterDbName = $this->masterDataDBOperator
            ->getMasterDbName($releaseKey, $serverDbHashMap);
        $this->masterDataDBOperator
            ->create($masterDbName);

        // Exercise
        $this->masterDataDBOperator->drop($masterDbName);

        // Verify
        // DBが削除されているか
        $databases = $this->masterDataDBOperator->showDatabases();
        $this->assertNotContains($masterDbName, $databases);
    }

    /**
     * @test
     */
    public function getMasterDbName_取得データチェック(): void
    {
        // Setup
        $releaseKey = 'releaseKey1';
        $serverDbHashMap = [
            'releaseKey1' => 'dbHash1',
            'releaseKey2' => 'dbHash2',
        ];

        // Exercise
        $actual = $this->masterDataDBOperator->getMasterDbName($releaseKey, $serverDbHashMap);

        // Verify
        // `環境名`_mst_`version` の文字列になっているか
        $this->assertEquals('testing_mst_releaseKey1_dbHash1', $actual);
    }
}

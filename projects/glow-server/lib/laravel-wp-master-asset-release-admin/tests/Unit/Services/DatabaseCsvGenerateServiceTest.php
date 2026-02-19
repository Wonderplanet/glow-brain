<?php

namespace MasterAssetReleaseAdmin\Unit\Services;

use Tests\Traits\ReflectionTrait;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Entities\MngMasterReleaseKeyEntity;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterReleaseVersion;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators\CSVOperator;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\ClassSearchService;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\CsvConvertService;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\DatabaseCsvGenerateService;
use WonderPlanet\Tests\TestCase;

class DatabaseCsvGenerateServiceTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @test
     */
    public function getDatabaseCsvDataHash_ハッシュ値取得チェック(): void
    {
        // Setup
        $importId = '001';
        $gitRevision = 'revision_1';
        $mngMasterReleaseVersionId = 'version_1';
        $releaseKey = 202412010;
        $mngMasterRelease = MngMasterRelease::factory()
            ->create([
                'release_key' => $releaseKey,
                'enabled' => 1,
                'target_release_version_id' => 'version_1',
            ]);
        MngMasterReleaseVersion::factory()
            ->create([
                'id' => $mngMasterReleaseVersionId,
                'release_key' => $releaseKey,
            ]);
        // 実際にcsvファイルを読み込まないようにモックを設定
        $mngMasterReleaseKeyEntity = new MngMasterReleaseKeyEntity($releaseKey, collect([$mngMasterRelease]));
        $csvOperatorMock = \Mockery::mock(CSVOperator::class);
        $csvOperatorMock->shouldReceive('read')->andReturn(['test']);
        $databaseCsvGenerateServiceMock = \Mockery::mock(
            DatabaseCsvGenerateService::class,
            [
                app()->make(CsvConvertService::class),
                app()->make(ClassSearchService::class),
                $csvOperatorMock
            ]
        )->makePartial();
        $databaseCsvGenerateServiceMock
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('getDatabaseCsvFiles')
            ->with("/{$importId}/mst/*.{$releaseKey}_{$gitRevision}.csv")
            ->andReturn(['mst_file']);
        $databaseCsvGenerateServiceMock
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('getDatabaseCsvFiles')
            ->with("/{$importId}/opr/*.{$releaseKey}_{$gitRevision}.csv")
            ->andReturn(['opr_file']);

        // Exercise
        $actuals = $databaseCsvGenerateServiceMock
            ->getDatabaseCsvDataHash($importId, $mngMasterReleaseKeyEntity, $gitRevision);

        // Verify
        //  keyがリリースキー同一か
        $this->assertArrayHasKey($releaseKey, $actuals);
        //  値にハッシュ化した文字列となっているか
        $this->assertIsString($actuals[$releaseKey]);
    }
}

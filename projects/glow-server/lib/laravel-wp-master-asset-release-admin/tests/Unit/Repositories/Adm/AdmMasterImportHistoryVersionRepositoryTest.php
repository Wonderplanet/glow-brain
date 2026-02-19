<?php

declare(strict_types=1);

namespace MasterAssetReleaseAdmin\Unit\Repositories\Adm;

use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Adm\AdmMasterImportHistoryVersion;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Repositories\Adm\AdmMasterImportHistoryVersionRepository;
use WonderPlanet\Tests\TestCase;

class AdmMasterImportHistoryVersionRepositoryTest extends TestCase
{
    private AdmMasterImportHistoryVersionRepository $admMasterImportHistoryVersionRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->admMasterImportHistoryVersionRepository = app()->make(AdmMasterImportHistoryVersionRepository::class);
    }

    /**
     * @test
     */
    public function create_登録チェック(): void
    {
        // Setup
        $admMasterImportHistoryId = 'import_history_id_test';
        $mngMasterReleaseVersionId = 'release_version_id_test';

        // Exercise
        $this->admMasterImportHistoryVersionRepository
            ->create($admMasterImportHistoryId, $mngMasterReleaseVersionId);

        // Verify
        $results = AdmMasterImportHistoryVersion::all();
        $this->assertCount(1, $results);
        $actual = $results->first();

        $this->assertEquals($admMasterImportHistoryId, $actual->getAdmMasterImportHistoryId());
        $this->assertEquals($mngMasterReleaseVersionId, $actual->getMngMasterReleaseVersionId());
    }

    /**
     * @test
     */
    public function getById_取得チェック(): void
    {
        // Setup
        $admMasterImportHistoryVersion = AdmMasterImportHistoryVersion::factory()->create(['id' => 'id_1']);

        // Exercise
        $actual = $this->admMasterImportHistoryVersionRepository->getById('id_1');

        // Verify
        $this->assertEquals($admMasterImportHistoryVersion->getAdmMasterImportHistoryId(), $actual->getAdmMasterImportHistoryId());
        $this->assertEquals($admMasterImportHistoryVersion->getMngMasterReleaseVersionId(), $actual->getMngMasterReleaseVersionId());
    }
}

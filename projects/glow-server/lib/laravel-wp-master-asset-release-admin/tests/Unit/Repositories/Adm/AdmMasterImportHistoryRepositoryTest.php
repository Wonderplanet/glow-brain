<?php

declare(strict_types=1);

namespace MasterAssetReleaseAdmin\Unit\Repositories\Adm;

use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Adm\AdmMasterImportHistory;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Repositories\Adm\AdmMasterImportHistoryRepository;
use WonderPlanet\Tests\TestCase;

class AdmMasterImportHistoryRepositoryTest extends TestCase
{
    private AdmMasterImportHistoryRepository $admMasterImportHistoryRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->admMasterImportHistoryRepository = app()->make(AdmMasterImportHistoryRepository::class);
    }

    /**
     * @test
     */
    public function create_登録チェック(): void
    {
        // Setup
        $gitRevision = 'git_revision_test';
        $importAdmUserId = 'adm_user_1';
        $importSource = 'import_source_test';

        // Exercise
        $this->admMasterImportHistoryRepository
            ->create($gitRevision, $importAdmUserId, $importSource);

        // Verify
        $results = AdmMasterImportHistory::all();
        $this->assertCount(1, $results);
        $actual = $results->first();

        $this->assertEquals($gitRevision, $actual->getGitRevision());
        $this->assertEquals($importAdmUserId, $actual->getImportAdmUserId());
        $this->assertEquals($importSource, $actual->getImportSource());
    }

    /**
     * @test
     */
    public function getById_取得チェック(): void
    {
        // Setup
        $admMasterImportHistory = AdmMasterImportHistory::factory()->create(['id' => 'id_1']);

        // Exercise
        $actual = $this->admMasterImportHistoryRepository->getById('id_1');

        // Verify
        $this->assertEquals($admMasterImportHistory->getGitRevision(), $actual->getGitRevision());
        $this->assertEquals($admMasterImportHistory->getImportAdmUserId(), $actual->getImportAdmUserId());
        $this->assertEquals($admMasterImportHistory->getImportSource(), $actual->getImportSource());
    }
}

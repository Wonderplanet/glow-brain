<?php

declare(strict_types=1);

namespace MasterAssetReleaseAdmin\Unit\Repositories\Adm;

use Carbon\CarbonImmutable;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Adm\AdmMasterReleaseVersionStatus;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Repositories\Adm\AdmMasterReleaseVersionStatusRepository;
use WonderPlanet\Tests\TestCase;

class AdmMasterReleaseVersionStatusRepositoryTest extends TestCase
{
    private AdmMasterReleaseVersionStatusRepository $admMasterReleaseVersionStatusRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->admMasterReleaseVersionStatusRepository = app()->make(AdmMasterReleaseVersionStatusRepository::class);
    }

    /**
     * @test
     * @dataProvider admMasterReleaseVersionStatusData
     */
    public function create_登録チェック(
        ?string $ocarinaValidationVersion,
        ?CarbonImmutable $clientFileDeletedAt,
        ?CarbonImmutable $serverDbDeletedAt
    ): void {
        // Setup
        $mngMasterReleaseVersionId = 'release_version_id_test';
        $ocarinaValidatedStatus = 'release_version_id_test';

        // Exercise
        $this->admMasterReleaseVersionStatusRepository
            ->create(
                $mngMasterReleaseVersionId,
                $ocarinaValidatedStatus,
                $ocarinaValidationVersion,
                $clientFileDeletedAt,
                $serverDbDeletedAt
            );

        // Verify
        $results = AdmMasterReleaseVersionStatus::all();
        $this->assertCount(1, $results);
        $actual = $results->first();

        $this->assertEquals($mngMasterReleaseVersionId, $actual->getMngMasterReleaseVersionId());
        $this->assertEquals($ocarinaValidatedStatus, $actual->getOcarinaValidatedStatus());
        $this->assertEquals($ocarinaValidationVersion, $actual->getOcarinaValidationVersion());
        $this->assertEquals($clientFileDeletedAt, $actual->getClientFileDeletedAt());
        $this->assertEquals($serverDbDeletedAt, $actual->getServerDbDeletedAt());
    }

    /**
     * @test
     * @dataProvider admMasterReleaseVersionStatusData
     */
    public function getById_取得チェック(
        ?string $ocarinaValidationVersion,
        ?CarbonImmutable $clientFileDeletedAt,
        ?CarbonImmutable $serverDbDeletedAt
    ): void {
        // Setup
        $admMasterReleaseVersionStatus = AdmMasterReleaseVersionStatus::factory()
            ->create(
                [
                    'id' => 'id_1',
                    'ocarina_validation_version' => $ocarinaValidationVersion,
                    'client_file_deleted_at' => $clientFileDeletedAt,
                    'server_db_deleted_at' => $serverDbDeletedAt,
                ]
            );

        // Exercise
        $actual = $this->admMasterReleaseVersionStatusRepository->getById('id_1');

        // Verify
        $this->assertEquals($admMasterReleaseVersionStatus->getMngMasterReleaseVersionId(), $actual->getMngMasterReleaseVersionId());
        $this->assertEquals($admMasterReleaseVersionStatus->getOcarinaValidatedStatus(), $actual->getOcarinaValidatedStatus());
        $this->assertEquals($admMasterReleaseVersionStatus->getOcarinaValidationVersion(), $actual->getOcarinaValidationVersion());
        $this->assertEquals($admMasterReleaseVersionStatus->getClientFileDeletedAt(), $actual->getClientFileDeletedAt());
        $this->assertEquals($admMasterReleaseVersionStatus->getServerDbDeletedAt(), $actual->getServerDbDeletedAt());
    }

    /**
     * @return array
     */
    private function admMasterReleaseVersionStatusData(): array
    {
        return [
            'データがnull' => [null, null, null],
            'データがある' => [
                'validation_version_test',
                CarbonImmutable::make('2020-01-01 00:00:00'),
                CarbonImmutable::make('2020-12-01 00:00:00')
            ],
        ];
    }
}

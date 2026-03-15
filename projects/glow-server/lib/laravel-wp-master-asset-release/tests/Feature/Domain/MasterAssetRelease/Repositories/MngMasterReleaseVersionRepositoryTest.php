<?php

namespace Feature\Domain\MasterAssetRelease\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngMasterRelease;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngMasterReleaseVersion;
use WonderPlanet\Domain\MasterAssetRelease\Repositories\MngMasterReleaseVersionRepository;

class MngMasterReleaseVersionRepositoryTest extends TestCase
{
    use RefreshDatabase;
    private MngMasterReleaseVersionRepository $mngMasterReleaseVersionRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->mngMasterReleaseVersionRepository = app(MngMasterReleaseVersionRepository::class);
    }

    /**
     * @test
     */
    public function getApplyCollection_データ取得チェック(): void
    {
        $now = $this->fixTime();

        // Setup
        MngMasterRelease::factory()->createMany(
            [
                [
                    // 配信終了
                    'release_key' => 202408310,
                    'client_compatibility_version' => '0.0.9',
                    'enabled' => 1,
                    'target_release_version_id' => 'id-000',
                    'start_at' => $now->subDays(10),
                ],
                [
                    // 配信中(最古)
                    'release_key' => 202409010,
                    'client_compatibility_version' => '1.0.0',
                    'enabled' => 1,
                    'target_release_version_id' => 'id-100',
                    'start_at' => $now->subDays(5),
                ],
                [
                    // 配信中(最新)
                    'release_key' => 202409020,
                    'client_compatibility_version' => '1.1.0',
                    'enabled' => 1,
                    'target_release_version_id' => 'id-101',
                    'start_at' => $now->subDays(2),
                ],
                [
                    // 配信準備中
                    'release_key' => 202409030,
                    'client_compatibility_version' => '1.2.0',
                    'start_at' => $now->addDay(),
                ],
            ]
        );
        MngMasterReleaseVersion::factory()->createMany(
            [
                [
                    'id' => 'id-000',
                    'release_key' => 202408310,
                ],
                [
                    'id' => 'id-100',
                    'release_key' => 202409010,
                ],
                [
                    'id' => 'id-101',
                    'release_key' => 202409020,
                ],
            ]
        );

        // Exercise
        $actuals = $this->mngMasterReleaseVersionRepository
            ->getApplyCollection($now);

        // Verify
        $this->assertCount(2, $actuals);
        $actual1 = $actuals->first(fn ($versionMap) => $versionMap['entity']->getReleaseKey() === 202409010);
        $this->assertEquals(202409010, $actual1['entity']->getReleaseKey());
        $this->assertEquals('1.0.0', $actual1['client_compatibility_version']);
        $actual2 = $actuals->first(fn ($versionMap) => $versionMap['entity']->getReleaseKey() === 202409020);
        $this->assertEquals(202409020, $actual2['entity']->getReleaseKey());
        $this->assertEquals('1.1.0', $actual2['client_compatibility_version']);
    }
}

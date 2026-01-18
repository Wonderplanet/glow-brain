<?php

namespace Feature\Domain\MasterAssetRelease\Delegators;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use WonderPlanet\Domain\MasterAssetRelease\Delegators\MasterReleaseDelegator;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngMasterRelease;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngMasterReleaseVersion;

class MasterReleaseDelegatorTest extends TestCase
{
    use RefreshDatabase;
    private MasterReleaseDelegator $masterReleaseDelegator;


    // fixtures/defaultのmng_master_releasesのデータを投入させない
    protected bool $ignoreDefaultCsvImport = true;

    public function setUp(): void
    {
        parent::setUp();
        $this->masterReleaseDelegator = app(MasterReleaseDelegator::class);
    }

    /**
     * @test
     */
    public function getApplyMasterReleaseVersionEntityByClientVersion_データ取得チェック(): void
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
        $clientVersion = '1.0.0';

        // Exercise
        $actual = $this->masterReleaseDelegator->getApplyMasterReleaseVersionEntityByClientVersion(
            $clientVersion,
            $now
        );

        // Verify
        $this->assertEquals(202409010, $actual->getReleaseKey());
    }

    /**
     * @test
     */
    public function getApplyMasterReleaseVersionEntityAndClientCompatibilityVersionCollection_データ取得チェック(): void
    {
        $now = $this->fixTime();

        // Setup
        MngMasterRelease::factory()
            ->createMany([
                [
                    // 配信中(最古)
                    'release_key' => 202501010,
                    'client_compatibility_version' => '1.0.0',
                    'target_release_version_id' => '100',
                    'enabled' => 1,
                    'start_at' => $now->subDays(5),
                ],
                [
                    // 配信中(最新)
                    'release_key' => 202502010,
                    'client_compatibility_version' => '1.1.0',
                    'target_release_version_id' => '101',
                    'enabled' => 1,
                    'start_at' => $now->subDays(2),
                ],
                [
                    // 準備中
                    'release_key' => 202503010,
                    'client_compatibility_version' => '1.2.0',
                    'start_at' => $now->addDay(),
                ],
            ]);
        MngMasterReleaseVersion::factory()->createMany(
            [
                [
                    'id' => '100',
                    'release_key' => 202501010,
                ],
                [
                    'id' => '101',
                    'release_key' => 202502010,
                ],
            ]
        );

        // Exercise
        $actuals = $this->masterReleaseDelegator
            ->getApplyMasterReleaseVersionEntityAndClientCompatibilityVersionCollection($now);

        // Verify
        $this->assertCount(2, $actuals);
        $actual1 = $actuals->first(fn (array $map) => $map['client_compatibility_version'] === '1.0.0');
        $this->assertEquals(202501010, $actual1['entity']->getReleaseKey());
        $this->assertEquals('1.0.0', $actual1['client_compatibility_version']);
        $actual2 = $actuals->first(fn (array $map) => $map['client_compatibility_version'] === '1.1.0');
        $this->assertEquals(202502010, $actual2['entity']->getReleaseKey());
        $this->assertEquals('1.1.0', $actual2['client_compatibility_version']);
    }
}

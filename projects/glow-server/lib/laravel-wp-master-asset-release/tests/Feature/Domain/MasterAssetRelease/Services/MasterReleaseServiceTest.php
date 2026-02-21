<?php

namespace Feature\Domain\MasterAssetRelease\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use WonderPlanet\Domain\MasterAssetRelease\Exceptions\WpMasterReleaseApplyNotFoundException;
use WonderPlanet\Domain\MasterAssetRelease\Exceptions\WpMasterReleaseIncompatibleClientVersionException;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngMasterRelease;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngMasterReleaseVersion;
use WonderPlanet\Domain\MasterAssetRelease\Services\MasterReleaseService;

class MasterReleaseServiceTest extends TestCase
{
    use RefreshDatabase;
    private MasterReleaseService $masterReleaseService;


    // fixtures/defaultのmng_master_releasesのデータを投入させない
    protected bool $ignoreDefaultCsvImport = true;

    public function setUp(): void
    {
        parent::setUp();
        $this->masterReleaseService = app(MasterReleaseService::class);
    }

    /**
     * @test
     * @dataProvider getApplyMasterReleaseVersionEntityByClientVersionData
     */
    public function getApplyMasterReleaseVersionEntityByClientVersion_データ取得チェック(
        string $clientVersion,
        int $releaseKey,
    ): void {
        $now = $this->fixTime();

        // Setup
        $releases = MngMasterRelease::factory()
            ->createMany([
                [
                    // 配信中(最古)
                    'release_key' => 202501010,
                    'client_compatibility_version' => '1.0.0',
                    'target_release_version_id' => '100',
                    'enabled' => 1,
                    'start_at' => $now->subDays(10),
                ],
                [
                    // 配信中(最新)
                    'release_key' => 202502010,
                    'client_compatibility_version' => '1.1.0',
                    'target_release_version_id' => '101',
                    'enabled' => 1,
                    'start_at' => $now->subDays(5),
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
        $expected = $releases->first(fn ($row) => $row->release_key === $releaseKey);

        // Exercise
        $actual = $this->masterReleaseService->getApplyMasterReleaseVersionEntityByClientVersion($clientVersion, $now);

        // Verify
        $this->assertEquals($expected->release_key, $actual->getReleaseKey());
    }

    /**
     * @return array
     */
    private function getApplyMasterReleaseVersionEntityByClientVersionData(): array
    {
        return [
            'クライアントバージョンが1.0.0' => ['1.0.0', 202501010],
            'クライアントバージョンが1.0.5' => ['1.0.5', 202501010],
            'クライアントバージョンが1.1.0' => ['1.1.0', 202502010],
            'クライアントバージョンが1.2.0' => ['1.2.0', 202502010],
        ];
    }

    /**
     * @test
     */
    public function getApplyMasterReleaseVersionEntityByClientVersion_配信中のリリースバージョンデータが存在しない(): void
    {
        $this->expectException(WpMasterReleaseApplyNotFoundException::class);
        $this->expectExceptionMessage('Wp-Master-Release: Not Found Apply Release');
        $now = $this->fixTime();

        // Setup
        MngMasterRelease::factory()
            ->create([
                // 準備中
                'release_key' => 202503010,
                'client_compatibility_version' => '1.0.0',
                'start_at' => $now->addDay(),
            ]);
        $clientVersion = '1.0.0';

        // Exercise
        $this->masterReleaseService->getApplyMasterReleaseVersionEntityByClientVersion($clientVersion, $now);
    }

    /**
     * @test
     */
    public function getApplyMasterReleaseVersionEntityByClientVersion_互換性のあるリリース情報が存在しない(): void
    {
        $this->expectException(WpMasterReleaseIncompatibleClientVersionException::class);
        $this->expectExceptionMessage('Incompatible Client Version: 0.0.9');
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
                    'start_at' => $now->subDays(10),
                ],
                [
                    // 配信中(最新)
                    'release_key' => 202502010,
                    'client_compatibility_version' => '1.1.0',
                    'target_release_version_id' => '101',
                    'enabled' => 1,
                    'start_at' => $now->subDays(5),
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
        $clientVersion = '0.0.9';

        // Exercise
        $this->masterReleaseService->getApplyMasterReleaseVersionEntityByClientVersion($clientVersion, $now);
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
        $actuals = $this->masterReleaseService
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

    /**
     * @test
     */
    public function getApplyMasterReleaseVersionEntityAndClientCompatibilityVersionCollection_配信中のデータが存在しない(): void
    {
        $this->expectException(WpMasterReleaseApplyNotFoundException::class);
        $this->expectExceptionMessage('Wp-Master-Release: Not Found Apply Release');
        $now = $this->fixTime();

        // Setup
        MngMasterRelease::factory()
            ->createMany([
                [
                    // 準備中
                    'release_key' => 202501010,
                    'client_compatibility_version' => '1.0.0',
                    'start_at' => $now->addDay(),
                ],
            ]);

        // Exercise
        $this->masterReleaseService
            ->getApplyMasterReleaseVersionEntityAndClientCompatibilityVersionCollection($now);
    }
}

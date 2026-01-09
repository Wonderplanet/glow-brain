<?php

namespace Feature\Domain\MasterAssetRelease\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use WonderPlanet\Domain\Common\Constants\PlatformConstant;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngAssetRelease;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngAssetReleaseVersion;
use WonderPlanet\Domain\MasterAssetRelease\Repositories\MngAssetReleaseVersionRepository;

class MngAssetReleaseVersionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private MngAssetReleaseVersionRepository $mngAssetReleaseVersionRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->mngAssetReleaseVersionRepository = app(MngAssetReleaseVersionRepository::class);
    }

    /**
     * @test
     * @dataProvider getApplyCollectionData
     */
    public function getApplyCollection_データ取得チェック(
        int $platform,
        string $expectedVersionId1,
        string $expectedVersionId2
    ): void {
        $now = $this->fixTime();

        // Setup
        MngAssetRelease::factory()->createMany(
            [
                [
                    // ios 配信終了
                    'release_key' => 202408310,
                    'platform' => PlatformConstant::PLATFORM_IOS,
                    'client_compatibility_version' => '0.0.9',
                    'enabled' => 1,
                    'target_release_version_id' => 'ios-000',
                    'start_at' => $now->subDays(10),
                ],
                [
                    // android 配信終了
                    'release_key' => 202408310,
                    'platform' => PlatformConstant::PLATFORM_ANDROID,
                    'client_compatibility_version' => '0.0.9',
                    'enabled' => 1,
                    'target_release_version_id' => 'android-000',
                    'start_at' => $now->subDays(10),
                ],
                [
                    // ios 配信中(最古)
                    'release_key' => 202409010,
                    'platform' => PlatformConstant::PLATFORM_IOS,
                    'client_compatibility_version' => '1.0.0',
                    'enabled' => 1,
                    'target_release_version_id' => 'ios-100',
                    'start_at' => $now->subDays(5),
                ],
                [
                    // android 配信中(最古)
                    'release_key' => 202409010,
                    'platform' => PlatformConstant::PLATFORM_ANDROID,
                    'client_compatibility_version' => '1.0.0',
                    'enabled' => 1,
                    'target_release_version_id' => 'android-100',
                    'start_at' => $now->subDays(5),
                ],
                [
                    // ios 配信中(最新)
                    'release_key' => 202409020,
                    'platform' => PlatformConstant::PLATFORM_IOS,
                    'client_compatibility_version' => '1.1.0',
                    'enabled' => 1,
                    'target_release_version_id' => 'ios-101',
                    'start_at' => $now->subDays(2),
                ],
                [
                    // android 配信中(最新)
                    'release_key' => 202409020,
                    'platform' => PlatformConstant::PLATFORM_ANDROID,
                    'client_compatibility_version' => '1.1.0',
                    'enabled' => 1,
                    'target_release_version_id' => 'android-101',
                    'start_at' => $now->subDays(2),
                ],
                [
                    // ios 配信準備中
                    'release_key' => 202409030,
                    'platform' => PlatformConstant::PLATFORM_IOS,
                    'client_compatibility_version' => '1.2.0',
                    'target_release_version_id' => 'ios-102',
                    'start_at' => $now->addDay(),
                ],
                [
                    // android 配信準備中
                    'release_key' => 202409030,
                    'platform' => PlatformConstant::PLATFORM_ANDROID,
                    'client_compatibility_version' => '1.2.0',
                    'target_release_version_id' => 'android-102',
                    'start_at' => $now->addDay(),
                ],
            ]
        );
        MngAssetReleaseVersion::factory()->createMany(
            [
                [
                    'id' => 'ios-000',
                    'release_key' => 202408310,
                ],
                [
                    'id' => 'android-000',
                    'release_key' => 202408310,
                ],
                [
                    'id' => 'ios-100',
                    'release_key' => 202409010,
                ],
                [
                    'id' => 'android-100',
                    'release_key' => 202409010,
                ],
                [
                    'id' => 'ios-101',
                    'release_key' => 202409020,
                ],
                [
                    'id' => 'android-101',
                    'release_key' => 202409020,
                ],
                [
                    'id' => 'ios-102',
                    'release_key' => 202409030,
                ],
                [
                    'id' => 'android-102',
                    'release_key' => 202409030,
                ],
            ]
        );

        // Exercise
        $actuals = $this->mngAssetReleaseVersionRepository
            ->getApplyCollection($platform, $now);

        // Verify
        // 配信中のデータが取得できているか
        $this->assertCount(2, $actuals);
        $actual1 = $actuals
            ->first(fn ($versionMap) => $versionMap['entity']->getReleaseKey() === 202409010);
        $this->assertEquals($expectedVersionId1, $actual1['entity']->getId());
        $this->assertEquals('1.0.0', $actual1['client_compatibility_version']);
        $actual2 = $actuals
            ->first(fn ($versionMap) => $versionMap['entity']->getReleaseKey() === 202409020);
        $this->assertEquals($expectedVersionId2, $actual2['entity']->getId());
        $this->assertEquals('1.1.0', $actual2['client_compatibility_version']);
    }

    /**
     * @return array[]
     */
    private function getApplyCollectionData(): array
    {
        return [
            'ios' => [PlatformConstant::PLATFORM_IOS, 'ios-100', 'ios-101'],
            'android' => [PlatformConstant::PLATFORM_ANDROID, 'android-100', 'android-101'],
        ];
    }
}

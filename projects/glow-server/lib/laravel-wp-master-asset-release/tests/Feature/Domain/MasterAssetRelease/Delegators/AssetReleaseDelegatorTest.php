<?php

namespace Feature\Domain\MasterAssetRelease\Delegators;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use WonderPlanet\Domain\Common\Constants\PlatformConstant;
use WonderPlanet\Domain\MasterAssetRelease\Delegators\AssetReleaseDelegator;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngAssetRelease;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngAssetReleaseVersion;

class AssetReleaseDelegatorTest extends TestCase
{
    use RefreshDatabase;

    private AssetReleaseDelegator $assetReleaseDelegator;

    public function setUp(): void
    {
        parent::setUp();
        $this->assetReleaseDelegator = app(AssetReleaseDelegator::class);
    }
    
    /**
     * @test
     * @dataProvider getCurrentActiveAssetData
     */
    public function getCurrentActiveAsset_データ取得チェック(
        int $platform,
        string $clientVersion,
        string $expectedVersionId,
        int $expectedReleaseKey,
    ): void {
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
                ],
                [
                    // android 配信終了
                    'release_key' => 202408310,
                    'platform' => PlatformConstant::PLATFORM_ANDROID,
                    'client_compatibility_version' => '0.0.9',
                    'enabled' => 1,
                    'target_release_version_id' => 'android-000',
                ],
                [
                    // ios 配信中(最古)
                    'release_key' => 202409010,
                    'platform' => PlatformConstant::PLATFORM_IOS,
                    'client_compatibility_version' => '1.0.0',
                    'enabled' => 1,
                    'target_release_version_id' => 'ios-100',
                ],
                [
                    // android 配信中(最古)
                    'release_key' => 202409010,
                    'platform' => PlatformConstant::PLATFORM_ANDROID,
                    'client_compatibility_version' => '1.0.0',
                    'enabled' => 1,
                    'target_release_version_id' => 'android-100',
                ],
                [
                    // ios 配信中(最新)
                    'release_key' => 202409020,
                    'platform' => PlatformConstant::PLATFORM_IOS,
                    'client_compatibility_version' => '1.1.0',
                    'enabled' => 1,
                    'target_release_version_id' => 'ios-101',
                ],
                [
                    // android 配信中(最新)
                    'release_key' => 202409020,
                    'platform' => PlatformConstant::PLATFORM_ANDROID,
                    'client_compatibility_version' => '1.1.0',
                    'enabled' => 1,
                    'target_release_version_id' => 'android-101',
                ],
                [
                    // ios 配信準備中
                    'release_key' => 202409030,
                    'platform' => PlatformConstant::PLATFORM_IOS,
                    'client_compatibility_version' => '1.2.0',
                    'target_release_version_id' => 'ios-102',
                ],
                [
                    // android 配信準備中
                    'release_key' => 202409030,
                    'platform' => PlatformConstant::PLATFORM_ANDROID,
                    'client_compatibility_version' => '1.2.0',
                    'target_release_version_id' => 'android-102',
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
        $actual = $this->assetReleaseDelegator->getCurrentActiveAsset($platform, $clientVersion);

        // Verify
        // 指定バージョンとプラットフォームに沿ったリリース情報が取得できているか
        $this->assertEquals($expectedVersionId, $actual->getId());
        $this->assertEquals($expectedReleaseKey, $actual->getReleaseKey());
    }
    
    /**
     * @return array[]
     */
    private function getCurrentActiveAssetData(): array
    {
        return [
            'ios バージョン1.0.0' => [PlatformConstant::PLATFORM_IOS, '1.0.0', 'ios-100', 202409010],
            'android バージョン1.0.0' => [PlatformConstant::PLATFORM_ANDROID, '1.0.0', 'android-100', 202409010],
            'ios バージョン1.1.0' => [PlatformConstant::PLATFORM_IOS, '1.1.0', 'ios-101', 202409020],
            'android バージョン1.1.0' => [PlatformConstant::PLATFORM_ANDROID, '1.1.0', 'android-101', 202409020],
        ];
    }

    /**
     * @test
     * @dataProvider getCurrentActiveAssetNullData
     */
    public function getCurrentActiveAsset_対象データがなくてnullになる(int $platform): void
    {
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
                ],
                [
                    // android 配信終了
                    'release_key' => 202408310,
                    'platform' => PlatformConstant::PLATFORM_ANDROID,
                    'client_compatibility_version' => '0.0.9',
                    'enabled' => 1,
                    'target_release_version_id' => 'android-000',
                ],
                [
                    // ios 配信中(最古)
                    'release_key' => 202409010,
                    'platform' => PlatformConstant::PLATFORM_IOS,
                    'client_compatibility_version' => '1.0.0',
                    'enabled' => 1,
                    'target_release_version_id' => 'ios-100',
                ],
                [
                    // android 配信中(最古)
                    'release_key' => 202409010,
                    'platform' => PlatformConstant::PLATFORM_ANDROID,
                    'client_compatibility_version' => '1.0.0',
                    'enabled' => 1,
                    'target_release_version_id' => 'android-100',
                ],
                [
                    // ios 配信中(最新)
                    'release_key' => 202409020,
                    'platform' => PlatformConstant::PLATFORM_IOS,
                    'client_compatibility_version' => '1.1.0',
                    'enabled' => 1,
                    'target_release_version_id' => 'ios-101',
                ],
                [
                    // android 配信中(最新)
                    'release_key' => 202409020,
                    'platform' => PlatformConstant::PLATFORM_ANDROID,
                    'client_compatibility_version' => '1.1.0',
                    'enabled' => 1,
                    'target_release_version_id' => 'android-101',
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
            ]
        );
        $clientVersion = '0.0.9';

        // Exercise
        $actual = $this->assetReleaseDelegator->getCurrentActiveAsset($platform, $clientVersion);

        // Exercise
        $this->assertNull($actual);
    }
    
    /**
     * @return array[]
     */
    private function getCurrentActiveAssetNullData(): array
    {
        return [
            'ios' => [PlatformConstant::PLATFORM_IOS, 'iOS'],
            'android' => [PlatformConstant::PLATFORM_ANDROID, 'Android'],
        ];
    }
}

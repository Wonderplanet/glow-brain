<?php

namespace Feature\Domain\Game\Services;

use App\Domain\Game\Services\AssetDataManifestService;
use App\Domain\User\Constants\UserConstant;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Tests\TestCase;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngAssetRelease;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngAssetReleaseVersion;

class AssetDataManifestServiceTest extends TestCase
{
    use DatabaseTruncation;
    private AssetDataManifestService $assetDataManifestService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assetDataManifestService = $this->app->make(AssetDataManifestService::class);
    }

    /**
     * @test
     */
    public function getCurrentActiveManifest_現在iosで適用しているアセットパスを取得する()
    {
        // Setup
        $now = $this->fixTime();
        $clientVersion = '1.0.0';
        $platform = UserConstant::PLATFORM_IOS;
        $catalogHash = 'catalog_hash';
        $catalogFileName = 'catalog_file_name';
        $targetReleaseVersionId = '1';
        $gitRevision = '111';

        MngAssetRelease::factory()->createMany(
            [
                // 本テストケースで取得されるレコード
                [
                    'release_key' => 111,
                    'platform' => $platform,
                    'enabled' => true,
                    'target_release_version_id' => $targetReleaseVersionId,
                    'client_compatibility_version' => $clientVersion,
                    'start_at' => $now->subDay(),
                ],
                // 以下は取得されないはずのダミーレコード
                [
                    'release_key' => 112,
                    'platform' => $platform,
                    'enabled' => false,
                    'target_release_version_id' => '2',
                    'client_compatibility_version' => $clientVersion,
                    'start_at' => $now->subDays(2),
                ],
                [
                    'release_key' => 113,
                    'platform' => UserConstant::PLATFORM_ANDROID,
                    'enabled' => true,
                    'target_release_version_id' => '3',
                    'client_compatibility_version' => $clientVersion,
                    'start_at' => $now->addDay(),
                ],
                [
                    'release_key' => 114,
                    'platform' => UserConstant::PLATFORM_ANDROID,
                    'enabled' => false,
                    'target_release_version_id' => '4',
                    'client_compatibility_version' => $clientVersion,
                    'start_at' => $now->addDays(5),
                ],
            ]
        );

        MngAssetReleaseVersion::factory()->create([
            'id' => $targetReleaseVersionId,
            'release_key' => 111,
            'git_revision' => $gitRevision,
            'catalog_hash' => $catalogHash,
            'catalog_file_name' => $catalogFileName,
            'platform' => $platform,
            'build_client_version' => $clientVersion,
        ]);

        $expect = [
            'catalog_data_path' => "assetbundles/ios/{$catalogHash}/{$catalogFileName}",
            'asset_hash' => $catalogHash,
        ];

        // Exercise
        $result = $this->assetDataManifestService->getCurrentActiveManifest($platform, $clientVersion, $now);

        // Verify
        $this->assertEquals($expect, $result);
    }

    /**
     * @test
     */
    public function getCurrentActiveManifest_現在androidで適用しているアセットパスを取得する()
    {
        // Setup
        $now = $this->fixTime();
        $clientVersion = '1.0.0';
        $platform = UserConstant::PLATFORM_ANDROID;
        $catalogHash = 'catalog_hash';
        $catalogFileName = 'catalog_file_name';
        $targetReleaseVersionId = '1';
        $gitRevision = '111';

        MngAssetRelease::factory()->createMany(
            [
                // 本テストケースで取得されるレコード
                [
                    'release_key' => 111,
                    'platform' => $platform,
                    'enabled' => true,
                    'target_release_version_id' => $targetReleaseVersionId,
                    'client_compatibility_version' => $clientVersion,
                    'start_at' => $now->subDay(),
                ],
                // 以下は取得されないはずのダミーレコード
                [
                    'release_key' => 112,
                    'platform' => $platform,
                    'enabled' => false,
                    'target_release_version_id' => '2',
                    'client_compatibility_version' => $clientVersion,
                    'start_at' => $now->subDays(2),
                ],
                [
                    'release_key' => 113,
                    'platform' => UserConstant::PLATFORM_IOS,
                    'enabled' => false,
                    'target_release_version_id' => '3',
                    'client_compatibility_version' => $clientVersion,
                    'start_at' => $now->addDay(),
                ],
                [
                    'release_key' => 114,
                    'platform' => UserConstant::PLATFORM_IOS,
                    'enabled' => false,
                    'target_release_version_id' => '4',
                    'client_compatibility_version' => $clientVersion,
                    'start_at' => $now->addDays(5),
                ],
            ]
        );

        MngAssetReleaseVersion::factory()->create([
            'id' => $targetReleaseVersionId,
            'release_key' => 111,
            'git_revision' => $gitRevision,
            'catalog_hash' => $catalogHash,
            'catalog_file_name' => $catalogFileName,
            'platform' => $platform,
            'build_client_version' => $clientVersion,
        ]);

        $expect = [
            'catalog_data_path' => "assetbundles/android/{$catalogHash}/{$catalogFileName}",
            'asset_hash' => $catalogHash,
        ];

        // Exercise
        $result = $this->assetDataManifestService->getCurrentActiveManifest($platform, $clientVersion, $now);

        // Verify
        $this->assertEquals($expect, $result);
    }
}

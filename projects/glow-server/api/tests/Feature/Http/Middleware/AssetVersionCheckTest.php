<?php

namespace Feature\Http\Middleware;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Constants\System;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Models\MstApiAction;
use App\Domain\User\Constants\UserConstant;
use App\Http\Middleware\AssetVersionCheck;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use Tests\TestCase;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngAssetRelease;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngAssetReleaseVersion;

class AssetVersionCheckTest extends TestCase
{
    private AssetVersionCheck $assetVersionCheck;

    public function setUp(): void
    {
        parent::setUp();
        $this->assetVersionCheck = new AssetVersionCheck();
    }

    /**
     * Requestをモックするヘルパーメソッド
     *
     * @param string $path
     * @param string $assetHash
     * @param string $clientVersion
     * @param string $platform
     * @return MockInterface|Request
     */
    private function createMockedRequest(
        string $path,
        string $assetHash,
        string $clientVersion,
        string $platform
    ) {
        return $this->mock(Request::class, function (MockInterface $mock) use (
            $path, $assetHash, $clientVersion, $platform
        ) {
            $mock->shouldReceive('header')
                ->with(System::HEADER_ASSET_HASH)
                ->andReturn($assetHash);
            $mock->shouldReceive('header')
                ->with(System::CLIENT_VERSION)
                ->andReturn($clientVersion);
            $mock->shouldReceive('header')
                ->with(System::HEADER_PLATFORM)
                ->andReturn($platform);
            $mock->shouldReceive('path')
                ->andReturn($path);
        });
    }

    /**
     * @test
     */
    public function handle_有効なアセットバージョンの場合は通過する()
    {
        // SetUp
        $platform = UserConstant::PLATFORM_IOS;
        $clientVersion = '1.0.0';
        $gitRevision = 'gitRevision';
        $response = 'hoge';
        $path = '/api/fuga';
        $targetReleaseVersionId = '1';
        $catalogHash = 'catalogHash';

        MngAssetRelease::factory()->create(
            [
                'platform' => $platform,
                'enabled' => true,
                'target_release_version_id' => $targetReleaseVersionId,
                'client_compatibility_version' => $clientVersion,
            ],
        );

        MngAssetReleaseVersion::factory()->create([
            'id' => $targetReleaseVersionId,
            'catalog_hash' => $catalogHash,
            'platform' => $platform,
            'build_client_version' => $clientVersion,
            'git_revision' => $gitRevision
        ]);

        $mockedRequest = $this->createMockedRequest($path, $catalogHash, $clientVersion, $platform);
        $next = fn() => $response;

        // Exercise
        $result = $this->assetVersionCheck->handle($mockedRequest, $next);

        // Verify
        $this->assertEquals($response, $result);
    }

    /**
     * @test
     */
    public function handle_無効なアセットバージョンの場合は、REQUIRE_RESOURCE_UPDATEの例外が発生する()
    {
        // SetUp
        $platform = UserConstant::PLATFORM_IOS;
        $clientVersion = '1.0.0';
        $requestAssetHash = 'hash2';
        $response = 'hoge';
        $path = '/api/fuga';
        $targetReleaseVersionId = '1';
        $catalogHash = 'catalogHash';

        MngAssetRelease::factory()->create(
            [
                'platform' => $platform,
                'enabled' => true,
                'target_release_version_id' => $targetReleaseVersionId,
                'client_compatibility_version' => $clientVersion,
            ],
        );

        MngAssetReleaseVersion::factory()->create([
            'id' => $targetReleaseVersionId,
            'platform' => $platform,
            'build_client_version' => $clientVersion,
            'catalog_hash' => $catalogHash
        ]);

        $mockedRequest = $this->createMockedRequest($path, $requestAssetHash, $clientVersion, $platform);
        $next = fn() => $response;

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::REQUIRE_RESOURCE_UPDATE);

        // Exercise
        $this->assetVersionCheck->handle($mockedRequest, $next);

        // Verify
    }

    /**
     * @test
     */
    public function handle_アセットバージョンのヘッダーが存在しない場合は通過する()
    {
        // SetUp
        $response = 'hoge';

        /** @var Request */
        $mockedRequest = $this->mock(Request::class, function (MockInterface $mock) {
            $mock->shouldReceive('header');
        });
        $next = fn() => $response;

        // Exercise
        $result = $this->assetVersionCheck->handle($mockedRequest, $next);

        // Verify
        $this->assertEquals($response, $result);
    }

    /**
     * @test
     */
    public function handle_更新チェックをskipするパスの場合は通過する()
    {
        // SetUp
        $platform = UserConstant::PLATFORM_IOS;
        $version = '1.0.0';
        $requestAssetHash = 'hash1';
        $response = 'hoge';
        $path = array_key_first(System::ASSET_CHECK_THROUGH_API);

        $mockedRequest = $this->createMockedRequest($path, $requestAssetHash, $version, $platform);
        $next = fn() => $response;

        // Exercise
        $result = $this->assetVersionCheck->handle($mockedRequest, $next);

        // Verify
        $this->assertEquals($response, $result);
    }

    public function test_handle_異なる時間でも同じキャッシュを使ってフィルタリングしたデータを使ってチェックできている()
    {
        // SetUp
        $platform = UserConstant::PLATFORM_IOS;
        $currentClientVersion = '1.0.0';
        $futureClientVersion = '2.0.0';
        $catalogHash1 = 'catalogHash1';
        $catalogHash2 = 'catalogHash2';
        $path = '/api/fuga';
        $response = 'hoge';

        // 現在有効なデータを作成（現在のclientVersionに対応）
        MngAssetRelease::factory()->create([
            'release_key' => '202301',
            'platform' => $platform,
            'enabled' => true,
            'target_release_version_id' => 'version_001',
            'client_compatibility_version' => $currentClientVersion,
            'start_at' => '2023-01-10 00:00:00',
        ]);

        MngAssetReleaseVersion::factory()->create([
            'id' => 'version_001',
            'release_key' => '202301',
            'platform' => $platform,
            'build_client_version' => $currentClientVersion,
            'catalog_hash' => $catalogHash1,
            'git_revision' => 'gitRevision1',
        ]);

        // 未来のデータを作成（未来のclientVersionに対応）
        MngAssetRelease::factory()->create([
            'release_key' => '202302',
            'platform' => $platform,
            'enabled' => true,
            'target_release_version_id' => 'version_002',
            'client_compatibility_version' => $futureClientVersion,
            'start_at' => '2023-01-20 00:00:00',
        ]);

        MngAssetReleaseVersion::factory()->create([
            'id' => 'version_002',
            'release_key' => '202302',
            'platform' => $platform,
            'build_client_version' => $futureClientVersion,
            'catalog_hash' => $catalogHash2,
            'git_revision' => 'gitRevision2',
        ]);

        $next = fn() => $response;

        // Exercise 1: 現在時点 - 現在clientVersionで通ることを確認
        $this->fixTime('2023-01-15 12:00:00');
        $mockedRequest1 = $this->createMockedRequest($path, $catalogHash1, $currentClientVersion, $platform);

        $result1 = $this->assetVersionCheck->handle($mockedRequest1, $next);

        // Verify 1
        $this->assertEquals($response, $result1);

        // Exercise 2: 未来時点 - 未来clientVersionで通ることを確認
        $this->fixTime('2023-01-25 12:00:00');
        $mockedRequest2 = $this->createMockedRequest($path, $catalogHash2, $futureClientVersion, $platform);

        $result2 = $this->assetVersionCheck->handle($mockedRequest2, $next);

        // Verify 2
        $this->assertEquals($response, $result2);

        // Exercise 3: 現在時点 - 未来clientVersionのハッシュでは通らないことを確認
        $this->fixTime('2023-01-15 12:00:00');
        $mockedRequest3 = $this->createMockedRequest($path, $catalogHash2, $futureClientVersion, $platform);

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::REQUIRE_RESOURCE_UPDATE);

        $this->assetVersionCheck->handle($mockedRequest3, $next);
    }
}

<?php

namespace Feature\Domain\Common\MiddleWare;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Common\Middleware\InitializeMstDatabaseConnection;
use WonderPlanet\Domain\MasterAssetRelease\Constants\ErrorCode;
use WonderPlanet\Domain\MasterAssetRelease\Exceptions\WpMasterReleaseApplyNotFoundException;
use WonderPlanet\Domain\MasterAssetRelease\Exceptions\WpMasterReleaseIncompatibleClientVersionException;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngMasterRelease;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngMasterReleaseVersion;

class InitializeMstDatabaseConnectionTest extends TestCase
{
    use RefreshDatabase;

    private InitializeMstDatabaseConnection $initializeMstDatabaseConnection;

    // fixtures/defaultのmng_master_releasesのデータを投入させない
    protected bool $ignoreDefaultCsvImport = true;

    protected $backupConfigKeys = [
        'database.connections.mst.database',
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->initializeMstDatabaseConnection = new InitializeMstDatabaseConnection();

        // テストのために書き換え
        Config::set('database.connections.mst.database', 'testing_mst_202301_serverDbHash');
    }

    #[Test]
    public function handle_有効なマスターバージョンが存在する場合、接続先が変更される()
    {
        // SetUp
        $response = 'hoge';
        $mngMasterReleaseVersionId = 'version-1';
        $clientVersion = '1.0.0';
        MngMasterRelease::factory()
            ->create([
                'release_key' => '202301',
                'client_compatibility_version' => '1.0.0',
                'enabled' => 1,
                'target_release_version_id' => $mngMasterReleaseVersionId,
            ]);
        $masterReleaseVersion = MngMasterReleaseVersion::factory()
            ->create([
                'id' => $mngMasterReleaseVersionId,
                'release_key' => '202301',
                'git_revision' => 'test1',
                'server_db_hash' => 'serverDbHash',
                'client_mst_data_hash' => 'mst1',
                'client_opr_data_hash' => 'opr1',
            ]);

        /** @var \Illuminate\Http\Request */
        $mockedRequest = $this->mock(Request::class);
        $mockedRequest
            ->shouldReceive('header')
            ->with(config('wp_master_asset_release.header_client_version'))
            ->andReturn($clientVersion);
        $next = fn() => $response;

        // Exercise
        $result = $this->initializeMstDatabaseConnection->handle($mockedRequest, $next);

        // Verify
        $entity = $masterReleaseVersion->toEntity();
        $this->assertEquals($response, $result);
        $this->assertEquals($entity->getDbName(), config('database.connections.mst.database'));
    }

    #[Test]
    public function handle_クライアントバージョンと互換性のあるマスターリリース情報がない場合はエラーになる(): void
    {
        $this->expectException(WpMasterReleaseIncompatibleClientVersionException::class);
        $this->expectExceptionMessage('Wp-Master-Release: Incompatible Client Version: 0.0.9');
        $this->expectExceptionCode(ErrorCode::INCOMPATIBLE_MASTER_DATA_FROM_CLIENT_VERSION);
        
        // Setup
        $response = 'hoge';
        $mngMasterReleaseVersionId = 'version-1';
        $clientVersion = '0.0.9';
        MngMasterRelease::factory()
            ->create([
                'release_key' => '202301',
                'client_compatibility_version' => '1.0.0',
                'enabled' => 1,
                'target_release_version_id' => $mngMasterReleaseVersionId,
            ]);
        MngMasterReleaseVersion::factory()
            ->create([
                'id' => $mngMasterReleaseVersionId,
                'release_key' => '202301',
                'git_revision' => 'test1',
                'server_db_hash' => 'serverDbHash',
                'client_mst_data_hash' => 'mst1',
                'client_opr_data_hash' => 'opr1',
            ]);
        
        // Exercise
        /** @var \Illuminate\Http\Request */
        $mockedRequest = $this->mock(Request::class);
        $mockedRequest
            ->shouldReceive('header')
            ->with(config('wp_master_asset_release.header_client_version'))
            ->andReturn($clientVersion);
        $next = fn() => $response;
        
        // Exercise
        $this->initializeMstDatabaseConnection->handle($mockedRequest, $next);
    }

    #[Test]
    public function handle_配信中のマスターリリース情報がない場合はエラーになる(): void
    {
        $this->expectException(WpMasterReleaseApplyNotFoundException::class);
        $this->expectExceptionMessage('Wp-Master-Release: Not Found Apply Release');
        $this->expectExceptionCode(ErrorCode::NOT_FOUND_APPLY_MASTER_RELEASE);
        
        // Setup
        $response = 'hoge';
        $mngMasterReleaseVersionId = 'version-1';
        $clientVersion = '1.0.0';
        MngMasterRelease::factory()
            ->create([
                'release_key' => '202301',
                'client_compatibility_version' => '1.0.0',
                'enabled' => 0,
                'target_release_version_id' => $mngMasterReleaseVersionId,
            ]);
        MngMasterReleaseVersion::factory()
            ->create([
                'id' => $mngMasterReleaseVersionId,
                'release_key' => '202301',
                'git_revision' => 'test1',
                'server_db_hash' => 'serverDbHash',
                'client_mst_data_hash' => 'mst1',
                'client_opr_data_hash' => 'opr1',
            ]);
        
        // Exercise
        /** @var \Illuminate\Http\Request */
        $mockedRequest = $this->mock(Request::class);
        $mockedRequest
            ->shouldReceive('header')
            ->with(config('wp_master_asset_release.header_client_version'))
            ->andReturn($clientVersion);
        $next = fn() => $response;
        
        // Exercise
        $this->initializeMstDatabaseConnection->handle($mockedRequest, $next);
    }
}

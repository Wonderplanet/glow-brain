<?php

declare(strict_types=1);

namespace MasterAssetReleaseAdmin\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Http\Middleware\AdminInitializeMstDatabaseConnection;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterReleaseVersion;
use WonderPlanet\Tests\TestCase;

class AdminInitializeMstDatabaseConnectionTest extends TestCase
{
    private AdminInitializeMstDatabaseConnection $adminInitializeMstDatabaseConnection;
    private string $configDatabaseConnectionsMstDatabase = '';
    
    // デフォルトのmng_master_releasesのデータを投入させない
    protected bool $ignoreDefaultCsvImport = true;

    public function setUp(): void
    {
        parent::setUp();
        $this->adminInitializeMstDatabaseConnection = new AdminInitializeMstDatabaseConnection();

        // テスト用に書き換えるためバックアップ保存
        $this->configDatabaseConnectionsMstDatabase = Config::get('database.connections.mst.database');
        Config::set('database.connections.mst.database', 'testing_mst_202301_serverDbHash');
    }

    public function tearDown(): void
    {
        Config::set('database.connections.mst.database', $this->configDatabaseConnectionsMstDatabase);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function handle_有効なマスターバージョンが存在する場合は通す(): void
    {
        // SetUp
        $response = 'hoge';

        $mngMasterReleaseVersionId = 'version-1';
        MngMasterRelease::factory()
            ->create([
                'release_key' => '202301',
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

        /** @var \Illuminate\Http\Request */
        $mockedRequest = $this->mock(Request::class);
        $mockedRequest
            ->shouldReceive('path')
            ->andReturn('billing');
        $next = fn() => $response;

        // Exercise
        $result = $this->adminInitializeMstDatabaseConnection->handle($mockedRequest, $next);

        // Verify
        $this->assertEquals($response, $result);
    }

    /**
     * @test
     * @dataProvider handleData
     */
    public function handle_有効なマスターバージョンが存在しないが、許可されたURLならエラーにせず通す(string $path): void
    {
        // SetUp
        $response = 'hoge';

        MngMasterRelease::factory()
            ->create([
                'release_key' => '202301',
            ]);

        /** @var \Illuminate\Http\Request */
        $mockedRequest = $this->mock(Request::class);
        $mockedRequest
            ->shouldReceive('path')
            ->andReturn($path);
        $next = fn() => $response;

        // Exercise
        $result = $this->adminInitializeMstDatabaseConnection->handle($mockedRequest, $next);

        // Verify
        $this->assertEquals($response, $result);
    }

    /**
     * @return array[]
     */
    private function handleData(): array
    {
        return [
            'admin' => ['admin'],
            'livewire/update' => ['livewire/update'],
            'login' => ['login'],
            'logout' => ['logout'],
            'mng-asset-releases' => ['mng-asset-releases'],
            'mng-master-releases' => ['mng-master-releases'],
            'mng-master-release-versions' => ['mng-master-release-versions'],
            'mng-master-and-asset-release' => ['mng-master-and-asset-release'],
        ];
    }

    /**
     * @test
     */
    public function handle_有効なマスターバージョンが存在せず許可されないURLの場合、エラーになる(): void
    {
        $this->expectExceptionMessage('not found released master data');

        // SetUp
        $response = 'hoge';

        MngMasterRelease::factory()
            ->create([
                'release_key' => '202301',
            ]);

        /** @var \Illuminate\Http\Request */
        $mockedRequest = $this->mock(Request::class);
        $mockedRequest
            ->shouldReceive('path')
            ->andReturn('billing');
        $next = fn() => $response;

        // Exercise
        $this->adminInitializeMstDatabaseConnection->handle($mockedRequest, $next);
    }
}

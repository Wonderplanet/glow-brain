<?php

namespace Feature\Domain\MasterAssetRelease\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngMasterReleaseVersion;

class MngMasterReleaseVersionTest extends TestCase
{
    use RefreshDatabase;

    private string $configDatabaseConnectionsMstDatabase = '';

    public function setUp(): void
    {
        parent::setUp();
        
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
    public function getDbName_チェック(): void
    {
        // Setup
        $mngMasterReleaseVersion = MngMasterReleaseVersion::factory()
            ->create([
                'id' => 'version-1',
                'release_key' => 202411050,
                'server_db_hash' => 'hash_1',
            ]);
        $entity = $mngMasterReleaseVersion->toEntity();

        // Exercise
        $actual = $entity->getDbName();

        // Verify
        $this->assertEquals('testing_mst_202411050_hash_1', $actual);
    }
}

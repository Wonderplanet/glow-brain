<?php

namespace MasterAssetReleaseAdmin\Unit\Services;

use WonderPlanet\Domain\Admin\Operators\S3Operator;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Entities\MngMasterReleaseKeyEntity;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Adm\AdmMasterImportHistory;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Adm\AdmMasterImportHistoryVersion;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterReleaseVersion;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators\MasterDataDBOperator;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\DatabaseImportService;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\GitCommitService;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\MasterDataImportService;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\MngMasterReleaseService;
use WonderPlanet\Tests\TestCase;

class MasterDataImportServiceTest extends TestCase
{
    // fixtures/defaultのmng_master_releasesのデータを投入させない
    protected bool $ignoreDefaultCsvImport = true;

    /**
     * @test
     */
    public function executeImport_実行チェック(): void
    {
        // Setup
        $importId = 202412010100000;
        $releaseKey = 202412010;
        $mngMasterRelease = MngMasterRelease::factory()
            ->create([
                'release_key' => $releaseKey
            ]);
        $mngMasterReleaseKeyEntity = new MngMasterReleaseKeyEntity(0, collect([$mngMasterRelease]));
        $masterDataHashMap[$releaseKey] = 'test_client_mst_data_hash';
        $masterDataI18nHashMap[$releaseKey] = [
            'ja' => 'test_client_mst_data_i18n_ja_hash',
            'en' => 'test_client_mst_data_i18n_en_hash',
            'zh-Hant' => 'test_client_mst_data_i18n_zh_hash',
        ];
        $operationDataHashMap[$releaseKey] = 'test_client_opr_data_hash';
        $operationDataI18nHashMap[$releaseKey] = [
            'ja' => 'test_client_opr_data_i18n_ja_hash',
            'en' => 'test_client_opr_data_i18n_en_hash',
            'zh-Hant' => 'test_client_opr_data_i18n_zh_hash',
        ];
        $serverDbHashMap[$releaseKey] = 'test_server_db_hash';
        $dataHashMap[$releaseKey] = 'test_data_hash';
        $gitRevision = 'test_git_revision';
        $masterSchemaVersions = collect(['testing_mst_202412010_test_server_db_hash' => 'test_master_schema_version']);
        $importAdmUserId = 'adm_1';

        // git操作させないためにモックを作成
        $gitCommitServiceMock = \Mockery::mock(GitCommitService::class);
        $gitCommitServiceMock->shouldReceive('commitSpreadSheetCsv')
            ->with('commit by admin')
            ->andReturn(true);
        $gitCommitServiceMock->shouldReceive('getCurrentHash')
            ->withNoArgs()
            ->andReturn($gitRevision);
        $gitCommitServiceMock->shouldReceive('pushSpreadSheetCsv')
            ->withNoArgs()
            ->andReturn();

        // DB操作をさせないためにモックを作成
        $databaseImportServiceMock = \Mockery::mock(DatabaseImportService::class);
        $databaseImportServiceMock->shouldReceive('import')
            ->with($importId, $mngMasterReleaseKeyEntity, $serverDbHashMap)
            ->andReturn($masterSchemaVersions);

        // s3の操作をさせないためにモックを作成
        $serializedFileDirPath = config('wp_master_asset_release_admin.serializedFileDir') . "/{$importId}/{$releaseKey}/";
        $s3OperatorMock = \Mockery::mock(S3Operator::class);
        $s3OperatorMock->shouldReceive('uploadDirectory')
            ->with($serializedFileDirPath, S3Operator::CONFIG_NAME_S3, "{$releaseKey}/")
            ->andReturn();
        $masterDataMysqlDump = config('wp_master_asset_release_admin.masterDataMysqlDump') . "/{$importId}/{$releaseKey}/";
        $s3OperatorMock->shouldReceive('uploadDirectory')
            ->with($masterDataMysqlDump, config('wp_master_asset_release_admin.master_data_mysqldump_bucket'), "{$releaseKey}/")
            ->andReturn();

        // Exercise
        $masterDataImportService = new MasterDataImportService(
            $gitCommitServiceMock,
            $databaseImportServiceMock,
            app()->make(MngMasterReleaseService::class),
            $s3OperatorMock,
            app()->make(MasterDataDBOperator::class)
        );
        $masterDataImportService->executeImport(
            $importId,
            $mngMasterReleaseKeyEntity,
            $masterDataHashMap,
            $masterDataI18nHashMap,
            $operationDataHashMap,
            $operationDataI18nHashMap,
            $serverDbHashMap,
            $dataHashMap,
            $importAdmUserId
        );

        //Verify
        // 想定した値が登録されているか
        $mngMasterRelease = MngMasterRelease::all()->first();
        $this->assertEquals(202412010, $mngMasterRelease->release_key);
        $this->assertEquals(0, $mngMasterRelease->enabled);
        $this->assertNotNull($mngMasterRelease->target_release_version_id);

        $mngMasterReleaseVersion = MngMasterReleaseVersion::all()->first();
        $this->assertEquals(202412010, $mngMasterReleaseVersion->release_key);
        $this->assertEquals('test_git_revision', $mngMasterReleaseVersion->git_revision);
        $this->assertEquals('test_master_schema_version', $mngMasterReleaseVersion->master_schema_version);
        $this->assertEquals('test_data_hash', $mngMasterReleaseVersion->data_hash);
        $this->assertEquals('test_server_db_hash', $mngMasterReleaseVersion->server_db_hash);
        $this->assertEquals('test_client_mst_data_hash', $mngMasterReleaseVersion->client_mst_data_hash);
        $this->assertEquals('test_client_mst_data_i18n_ja_hash', $mngMasterReleaseVersion->client_mst_data_i18n_ja_hash);
        $this->assertEquals('test_client_mst_data_i18n_en_hash', $mngMasterReleaseVersion->client_mst_data_i18n_en_hash);
        $this->assertEquals('test_client_mst_data_i18n_zh_hash', $mngMasterReleaseVersion->client_mst_data_i18n_zh_hash);
        $this->assertEquals('test_client_opr_data_hash', $mngMasterReleaseVersion->client_opr_data_hash);
        $this->assertEquals('test_client_opr_data_i18n_ja_hash', $mngMasterReleaseVersion->client_opr_data_i18n_ja_hash);
        $this->assertEquals('test_client_opr_data_i18n_en_hash', $mngMasterReleaseVersion->client_opr_data_i18n_en_hash);
        $this->assertEquals('test_client_opr_data_i18n_zh_hash', $mngMasterReleaseVersion->client_opr_data_i18n_zh_hash);

        $admMasterImportHistory = AdmMasterImportHistory::all()->first();
        $this->assertEquals('test_git_revision', $admMasterImportHistory->git_revision);
        $this->assertEquals($importAdmUserId, $admMasterImportHistory->import_adm_user_id);
        $this->assertEquals(AdmMasterImportHistory::IMPORT_SOURCE_SPREAD_SHEET, $admMasterImportHistory->import_source);

        $admMasterImportHistoryVersion = AdmMasterImportHistoryVersion::all()->first();
        $this->assertEquals($admMasterImportHistory->id, $admMasterImportHistoryVersion->adm_master_import_history_id);
        $this->assertEquals($mngMasterReleaseVersion->id, $admMasterImportHistoryVersion->mng_master_release_version_id);
    }

    /**
     * @test
     */
    public function executeImportFromEnvironment_実行チェック(): void
    {
        // Setup
        $releaseKey = 202412010;
        MngMasterRelease::factory()
            ->create([
                'release_key' => $releaseKey
            ]);
        $importId = 202412010100000;
        $fromEnvironment = 'test_dev';

        $fromEnvironmentMasterReleaseVersionMap = [
            $releaseKey => [
                'release_key' => $releaseKey,
                'git_revision' => 'test_git_revision',
                'master_schema_version' => 'test_master_schema_version',
                'data_hash' => 'test_data_hash',
                'server_db_hash' => 'test_server_db_hash',
                'client_mst_data_hash' => 'test_client_mst_data_hash',
                'client_mst_data_i18n_ja_hash' => 'test_client_mst_data_i18n_ja_hash',
                'client_mst_data_i18n_en_hash' => 'test_client_mst_data_i18n_en_hash',
                'client_mst_data_i18n_zh_hash' => 'test_client_mst_data_i18n_zh_hash',
                'client_opr_data_hash' => 'test_client_opr_data_hash',
                'client_opr_data_i18n_ja_hash' => 'test_client_opr_data_i18n_ja_hash',
                'client_opr_data_i18n_en_hash' => 'test_client_opr_data_i18n_en_hash',
                'client_opr_data_i18n_zh_hash' => 'test_client_opr_data_i18n_zh_hash',
            ],
        ];
        $importAdmUserId = 'adm_1';
        $serverDbHashMap[$releaseKey] = 'test_server_db_hash';
        // s3の操作をさせないためにモックを作成
        $s3OperatorMock = \Mockery::mock(S3Operator::class);
        $s3FilePath = '202412010/test_dev_mst_202412010_test_server_db_hash.sql';
        $downloadFilePath = 'download_masterdata_mysqldump/202412010100000/202412010/test_dev_mst_202412010_test_server_db_hash.sql';
        // mysqldumpファイルダウンロードのモック
        $s3OperatorMock->shouldReceive('downloadMasterMySqlDump')
            ->with('s3_master_dump_test_dev', $s3FilePath, $downloadFilePath);
        // マスターデータのdataファイル、jsonファイルコピーのモック
        $configInput = 's3_client_master_data_test_dev';
        $pathList = [
            '202412010/masterdata/masterdata_test_client_mst_data_hash',
            '202412010/masteri18ndata/mst_I18n_ja_test_client_mst_data_i18n_ja_hash',
            '202412010/masteri18ndata/mst_I18n_en_test_client_mst_data_i18n_en_hash',
            '202412010/masteri18ndata/mst_I18n_zh-Hant_test_client_mst_data_i18n_zh_hash',
            '202412010/operationdata/operationdata_test_client_opr_data_hash',
            '202412010/operationi18ndata/opr_I18n_ja_test_client_opr_data_i18n_ja_hash',
            '202412010/operationi18ndata/opr_I18n_en_test_client_opr_data_i18n_en_hash',
            '202412010/operationi18ndata/opr_I18n_zh-Hant_test_client_opr_data_i18n_zh_hash',
        ];
        // ファイル名でループしてモックを生成
        foreach ($pathList as $path) {
            $file = $path . '.data';
            $s3OperatorMock->shouldReceive('copyMasterDataFile')
                ->once()
                ->with($configInput, S3Operator::CONFIG_NAME_S3, $file);
        }

        // DB作成操作をさせないためにモックを作成
        $databaseImportServiceMock = \Mockery::mock(DatabaseImportService::class);
        $databaseImportServiceMock->shouldReceive('importFromEnvironment')
            ->with($releaseKey, $serverDbHashMap, $downloadFilePath);

        // Exercise
        $masterDataImportService = new MasterDataImportService(
            app()->make(GitCommitService::class),
            $databaseImportServiceMock,
            app()->make(MngMasterReleaseService::class),
            $s3OperatorMock,
            app()->make(MasterDataDBOperator::class)
        );
        $masterDataImportService->executeImportFromEnvironment($importId, $fromEnvironment, $fromEnvironmentMasterReleaseVersionMap, $importAdmUserId);

        // Verify
        // 想定した値が登録されているか
        $mngMasterRelease = MngMasterRelease::all()->first();
        $this->assertEquals(202412010, $mngMasterRelease->release_key);
        $this->assertEquals(0, $mngMasterRelease->enabled);
        $this->assertNotNull($mngMasterRelease->target_release_version_id);

        $mngMasterReleaseVersion = MngMasterReleaseVersion::all()->first();
        $this->assertEquals(202412010, $mngMasterReleaseVersion->release_key);
        $this->assertEquals('test_git_revision', $mngMasterReleaseVersion->git_revision);
        $this->assertEquals('test_master_schema_version', $mngMasterReleaseVersion->master_schema_version);
        $this->assertEquals('test_data_hash', $mngMasterReleaseVersion->data_hash);
        $this->assertEquals('test_server_db_hash', $mngMasterReleaseVersion->server_db_hash);
        $this->assertEquals('test_client_mst_data_hash', $mngMasterReleaseVersion->client_mst_data_hash);
        $this->assertEquals('test_client_mst_data_i18n_ja_hash', $mngMasterReleaseVersion->client_mst_data_i18n_ja_hash);
        $this->assertEquals('test_client_mst_data_i18n_en_hash', $mngMasterReleaseVersion->client_mst_data_i18n_en_hash);
        $this->assertEquals('test_client_mst_data_i18n_zh_hash', $mngMasterReleaseVersion->client_mst_data_i18n_zh_hash);
        $this->assertEquals('test_client_opr_data_hash', $mngMasterReleaseVersion->client_opr_data_hash);
        $this->assertEquals('test_client_opr_data_i18n_ja_hash', $mngMasterReleaseVersion->client_opr_data_i18n_ja_hash);
        $this->assertEquals('test_client_opr_data_i18n_en_hash', $mngMasterReleaseVersion->client_opr_data_i18n_en_hash);
        $this->assertEquals('test_client_opr_data_i18n_zh_hash', $mngMasterReleaseVersion->client_opr_data_i18n_zh_hash);

        $admMasterImportHistory = AdmMasterImportHistory::all()->first();
        $this->assertEquals('test_git_revision', $admMasterImportHistory->git_revision);
        $this->assertEquals($importAdmUserId, $admMasterImportHistory->import_adm_user_id);
        $this->assertEquals(AdmMasterImportHistory::IMPORT_SOURCE_FROM_ENVIRONMENT, $admMasterImportHistory->import_source);

        $admMasterImportHistoryVersion = AdmMasterImportHistoryVersion::all()->first();
        $this->assertEquals($admMasterImportHistory->id, $admMasterImportHistoryVersion->adm_master_import_history_id);
        $this->assertEquals($mngMasterReleaseVersion->id, $admMasterImportHistoryVersion->mng_master_release_version_id);
    }
}

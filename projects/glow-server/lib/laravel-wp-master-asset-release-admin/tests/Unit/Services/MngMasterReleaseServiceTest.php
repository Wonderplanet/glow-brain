<?php

declare(strict_types=1);

namespace MasterAssetReleaseAdmin\Unit\Services;

use Tests\Traits\ReflectionTrait;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Adm\AdmMasterImportHistory;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Adm\AdmMasterImportHistoryVersion;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterReleaseVersion;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\MngMasterReleaseService;
use WonderPlanet\Tests\TestCase;

class MngMasterReleaseServiceTest extends TestCase
{
    use ReflectionTrait;

    private MngMasterReleaseService $mngMasterReleaseService;

    // fixtures/defaultのmng_master_releasesのデータを投入させない
    protected bool $ignoreDefaultCsvImport = true;

    public function setUp(): void
    {
        parent::setUp();

        $this->mngMasterReleaseService = app(MngMasterReleaseService::class);
    }

    /**
     * @test
     */
    public function getLatestReleasedMngMasterRelease_データ取得チェック(): void
    {
        // Setup
        $mngMasterReleases = [
            [
                // 配信終了
                'release_key' => 2024090101,
                'enabled' => 1,
                'target_release_version_id' => '100',
            ],
            [
                // 配信中
                'release_key' => 2024090102,
                'enabled' => 1,
                'target_release_version_id' => '101',
            ],
            [
                // 配信準備中
                'release_key' => 2024090103,
                'enabled' => 0,
                'target_release_version_id' => null,
            ],
        ];
        foreach ($mngMasterReleases as $data) {
            MngMasterRelease::factory()
                ->create($data);
        }

        // Exercise
        $actual = $this->mngMasterReleaseService->getLatestReleasedMngMasterRelease();

        // Verify
        $this->assertEquals(2024090102, $actual->release_key);
        $this->assertTrue((bool) $actual->enabled);
        $this->assertEquals('101', $actual->target_release_version_id);
    }

    /**
     * @test
     */
    public function getMngMasterReleasesByApply_データ取得チェック(): void
    {
        // Setup
        $mngMasterReleases = [
            [
                // 配信終了
                'release_key' => 2024090101,
                'enabled' => 1,
                'target_release_version_id' => '100',
            ],
            [
                // 配信中
                'release_key' => 2024090102,
                'enabled' => 1,
                'target_release_version_id' => '101',
            ],
            [
                // 配信中(最新)
                'release_key' => 2024090103,
                'enabled' => 1,
                'target_release_version_id' => '102',
            ],
            [
                // 配信準備中
                'release_key' => 2024090104,
                'enabled' => 0,
                'target_release_version_id' => null,
            ],
        ];
        MngMasterRelease::factory()
            ->createMany($mngMasterReleases);

        // Exercise
        $actuals = $this->mngMasterReleaseService->getMngMasterReleasesByApply();

        // Verify
        // 配信中データが2件存在するか
        $this->assertCount(2, $actuals);
        // 配信中データがそれぞれ正しいか
        $actual1 = $actuals->first(fn (MngMasterRelease $mngMasterRelease) => $mngMasterRelease->release_key === 2024090102);
        $this->assertEquals(2024090102, $actual1->release_key);
        $this->assertTrue((bool) $actual1->enabled);
        $this->assertEquals('101', $actual1->target_release_version_id);
        $actual2 = $actuals->first(fn (MngMasterRelease $mngMasterRelease) => $mngMasterRelease->release_key === 2024090103);
        $this->assertEquals(2024090103, $actual2->release_key);
        $this->assertTrue((bool) $actual2->enabled);
        $this->assertEquals('102', $actual2->target_release_version_id);
    }
    
    /**
     * @test
     */
    public function getOldestApplyMngMasterRelease_データ取得チェック(): void
    {
        // Setup
        $mngMasterReleases = [
            [
                // 配信終了
                'release_key' => 2024090101,
                'enabled' => 1,
                'target_release_version_id' => '100',
            ],
            [
                // 配信中(最古)
                'release_key' => 2024090102,
                'enabled' => 1,
                'target_release_version_id' => '101',
            ],
            [
                // 配信中(最新)
                'release_key' => 2024090103,
                'enabled' => 1,
                'target_release_version_id' => '102',
            ],
            [
                // 配信準備中
                'release_key' => 2024090104,
                'enabled' => 0,
                'target_release_version_id' => null,
            ],
        ];
        MngMasterRelease::factory()
            ->createMany($mngMasterReleases);
        
        // Exercise
        $actual = $this->mngMasterReleaseService->getOldestApplyMngMasterRelease();
        
        // Verify
        // 配信中データがそれぞれ正しいか
        $this->assertEquals(2024090102, $actual->release_key);
        $this->assertTrue((bool) $actual->enabled);
        $this->assertEquals('101', $actual->target_release_version_id);

    }

    /**
     * @test
     */
    public function getMngMasterReleasesByApplyOrPending_データ取得チェック(): void
    {
        // Setup
        $mngMasterReleases = [
            [
                // 配信終了
                'release_key' => 2024090101,
                'enabled' => 1,
                'target_release_version_id' => '100',
            ],
            [
                // 配信中(最古)
                'release_key' => 2024090102,
                'enabled' => 1,
                'target_release_version_id' => '101',
            ],
            [
                // 配信中(最新)
                'release_key' => 2024090103,
                'enabled' => 1,
                'target_release_version_id' => '102',
            ],
            [
                // 配信準備中
                'release_key' => 2024090104,
                'enabled' => 0,
                'target_release_version_id' => null,
            ],
        ];
        MngMasterRelease::factory()
            ->createMany($mngMasterReleases);

        // Exercise
        $actuals = $this->mngMasterReleaseService->getMngMasterReleasesByApplyOrPending();

        // Verify
        // 配信中2件+配信準備中1件が取得できているか
        $this->assertCount(3, $actuals);
        // 配信中(最古)データ取得チェック
        $actual1 = $actuals->first(fn ($row) => $row->release_key === 2024090102);
        $this->assertEquals(2024090102, $actual1->release_key);
        $this->assertTrue((bool) $actual1->enabled);
        $this->assertEquals('101', $actual1->target_release_version_id);
        // 配信中(最新)データ取得チェック
        $actual2 = $actuals->first(fn ($row) => $row->release_key === 2024090103);
        $this->assertEquals(2024090103, $actual2->release_key);
        $this->assertTrue((bool) $actual2->enabled);
        $this->assertEquals('102', $actual2->target_release_version_id);
        // 配信準備中データ取得チェック
        $actual3 = $actuals->first(fn ($row) => $row->release_key === 2024090104);
        $this->assertEquals(2024090104, $actual3->release_key);
        $this->assertFalse((bool) $actual3->enabled);
        $this->assertNull($actual3->target_release_version_id);
    }

    /**
     * @test
     */
    public function getLastImportAtMap_データ取得チェック(): void
    {
        // Setup
        $admMasterImportHistories = [
            [
                'id' => 'admMasterImportHistory1',
                'git_revision' => 'git_revision_2024090101',
                'import_adm_user_id' => 'admin_1',
                'import_source' => 'import_source_2024090101',
                'created_at' => '2024-09-01 10:00:00',
                'updated_at' => '2024-09-01 10:00:00',
            ],
            [
                'id' => 'admMasterImportHistory2',
                'git_revision' => 'git_revision_2024090102',
                'import_adm_user_id' => 'admin_1',
                'import_source' => 'import_source_2024090102',
                'created_at' => '2024-09-01 12:00:00',
                'updated_at' => '2024-09-01 12:00:00',
            ],
            [
                'id' => 'admMasterImportHistory3',
                'git_revision' => 'git_revision_2024090201',
                'import_adm_user_id' => 'admin_1',
                'import_source' => 'import_source_2024090201',
                'created_at' => '2024-09-01 12:00:01',
                'updated_at' => '2024-09-01 12:00:01',
            ],
        ];
        $admMasterImportHistoryVersions = [
            [
                'adm_master_import_history_id' => 'admMasterImportHistory1',
                'mng_master_release_version_id' => 'mngMasterReleaseVersion1',
            ],
            [
                'adm_master_import_history_id' => 'admMasterImportHistory2',
                'mng_master_release_version_id' => 'mngMasterReleaseVersion2',
            ],
            [
                'adm_master_import_history_id' => 'admMasterImportHistory3',
                'mng_master_release_version_id' => 'mngMasterReleaseVersion2',
            ],
        ];
        AdmMasterImportHistory::factory()
            ->createMany($admMasterImportHistories);
        AdmMasterImportHistoryVersion::factory()
            ->createMany($admMasterImportHistoryVersions);

        // Exercise
        $actuals = collect($this->mngMasterReleaseService->getLastImportAtMap());

        // Verify
        //  「mngMasterReleaseVersion1」と「最新のmngMasterReleaseVersion2」の2件を取得しているか
        $this->assertCount(2, $actuals);
        $actual1 = $actuals['mngMasterReleaseVersion1'];
        $this->assertEquals('2024-09-01 10:00:00', $actual1->format('Y-m-d H:i:s'));
        $actual2 = $actuals['mngMasterReleaseVersion2'];
        $this->assertEquals('2024-09-01 12:00:01', $actual2->format('Y-m-d H:i:s'));
    }

    /**
     * @test
     */
    public function getLatestImportAtMapByReleaseKeys_データ取得チェック(): void
    {
        // Setup
        MngMasterReleaseVersion::factory()
            ->create([
                'id' => '100',
                'release_key' => '202411010',
            ]);
        MngMasterReleaseVersion::factory()
            ->create([
                'id' => '101',
                'release_key' => '202411010',
            ]);
        MngMasterReleaseVersion::factory()
            ->create([
                'id' => '102',
                'release_key' => '202412010',
            ]);
        AdmMasterImportHistoryVersion::factory()
            ->create([
                'mng_master_release_version_id' => '100',
                'created_at' => '2024-10-20 00:00:00',
            ]);
        AdmMasterImportHistoryVersion::factory()
            ->create([
                'mng_master_release_version_id' => '101',
                'created_at' => '2024-10-20 01:00:00',
            ]);
        AdmMasterImportHistoryVersion::factory()
            ->create([
                'mng_master_release_version_id' => '102',
                'created_at' => '2024-11-20 01:00:00',
            ]);
        $releaseKeys = ['202411010', '202412010'];

        // Exercise
        $actuals = $this->mngMasterReleaseService->getLatestImportAtMapByReleaseKeys($releaseKeys);

        // Verify
        //  リリースキーごとにmng_master_release_versionの最新のcreated_atのデータが取得できているか
        $this->assertEquals(
            [
                '202411010' => [
                    'mng_master_release_version_id' => '101',
                    'import_at' => '2024-10-20 01:00:00',
                ],
                '202412010' => [
                    'mng_master_release_version_id' => '102',
                    'import_at' => '2024-11-20 01:00:00',
                ],
            ],
            $actuals
        );
    }

    /**
     * @test
     */
    public function updateMasterRelease_更新チェック(): void
    {
        // Setup
        $mngMasterReleaseKeys = [
            2024092501,
            2024092601
        ];
        $gitRevision = 'git-revision';
        $masterDataHashMap = [
            2024092501 => 'master-data-hash1',
            2024092601 => 'master-data-hash2',
        ];
        $masterDataI18nHashMap = [
            2024092501 => [
                'ja' => 'master-data-hash1-ja',
                'en' => 'master-data-hash1-en',
                'zh-Hant' => 'master-data-hash1-zh-Hant',
            ],
            2024092601 => [
                'ja' => 'master-data-hash2-ja',
                'en' => 'master-data-hash2-en',
                'zh-Hant' => 'master-data-hash2-zh-Hant',
            ],
        ];
        $operationDataHashMap = [
            2024092501 => 'operation-data-hash1',
            2024092601 => 'operation-data-hash2',
        ];
        $operationDataI18nHashMap = [
            2024092501 => [
                'ja' => 'operation-data-hash1-ja',
                'en' => 'operation-data-hash1-en',
                'zh-Hant' => 'operation-data-hash1-zh-Hant',
            ],
            2024092601 => [
                'ja' => 'operation-data-hash2-ja',
                'en' => 'operation-data-hash2-en',
                'zh-Hant' => 'operation-data-hash2-zh-Hant',
            ],
        ];
        $serverDbHashMap = [
            2024092501 => 'server-db-hash1',
            2024092601 => 'server-db-hash2',
        ];
        $dataHashMap = [
            2024092501 => 'data-hash1',
            2024092601 => 'data-hash2',
        ];
        // mngMasterRelease作成
        $mngMasterReleases = [
            [
                // 配信中
                'release_key' => 2024092501,
                'enabled' => 1,
                'target_release_version_id' => '101',
            ],
            [
                // 配信準備中
                'release_key' => 2024092601,
                'enabled' => 0,
                'target_release_version_id' => null,
            ],
        ];
        MngMasterRelease::factory()
            ->createMany($mngMasterReleases);
        $importAdmUserId = 'adm-user-id';
        $importSource = 'import-source';
        $masterSchemaVersions = [
            config('app.env') . '_mst_2024092501_server-db-hash1' => 'masterSchemaVersion_1',
            config('app.env') . '_mst_2024092601_server-db-hash2' => 'masterSchemaVersion_2',
        ];

        // Exercise
        $this->mngMasterReleaseService
            ->updateMasterRelease(
                $mngMasterReleaseKeys,
                $gitRevision,
                $masterDataHashMap,
                $masterDataI18nHashMap,
                $operationDataHashMap,
                $operationDataI18nHashMap,
                $masterSchemaVersions,
                $serverDbHashMap,
                $dataHashMap,
                $importAdmUserId,
                $importSource
            );

        // Verify
        // mng_master_release_versionsのチェック
        $mngMasterReleaseVersions = MngMasterReleaseVersion::all();
        $this->assertCount(2, $mngMasterReleaseVersions);
        $mngMasterReleaseVersion1 = $mngMasterReleaseVersions->first(fn ($row) => $row->release_key === 2024092501);
        $this->assertNotNull($mngMasterReleaseVersion1);
        $mngMasterReleaseVersion2 = $mngMasterReleaseVersions->first(fn ($row) => $row->release_key === 2024092601);
        $this->assertNotNull($mngMasterReleaseVersion2);
        foreach ($mngMasterReleaseVersions as $mngMasterReleaseVersion) {
            $masterDataHash = $masterDataHashMap[$mngMasterReleaseVersion->release_key];
            $operationDataHash = $operationDataHashMap[$mngMasterReleaseVersion->release_key];
            $serverDbHash = $serverDbHashMap[$mngMasterReleaseVersion->release_key];
            $masterDataI18nHashJa = $masterDataI18nHashMap[$mngMasterReleaseVersion->release_key]['ja'];
            $masterDataI18nHashEn = $masterDataI18nHashMap[$mngMasterReleaseVersion->release_key]['en'];
            $masterDataI18nHashZh = $masterDataI18nHashMap[$mngMasterReleaseVersion->release_key]['zh-Hant'];
            $operationDataI18nHashJa = $operationDataI18nHashMap[$mngMasterReleaseVersion->release_key]['ja'];
            $operationDataI18nHashEn = $operationDataI18nHashMap[$mngMasterReleaseVersion->release_key]['en'];
            $operationDataI18nHashZh = $operationDataI18nHashMap[$mngMasterReleaseVersion->release_key]['zh-Hant'];
            $dataHash = $dataHashMap[$mngMasterReleaseVersion->release_key];

            $this->assertEquals($gitRevision, $mngMasterReleaseVersion->git_revision);
            $this->assertEquals($dataHash, $mngMasterReleaseVersion->data_hash);
            $this->assertEquals($serverDbHash, $mngMasterReleaseVersion->server_db_hash);
            $this->assertEquals($masterDataHash, $mngMasterReleaseVersion->client_mst_data_hash);
            $this->assertEquals($masterDataI18nHashJa, $mngMasterReleaseVersion->client_mst_data_i18n_ja_hash);
            $this->assertEquals($masterDataI18nHashEn, $mngMasterReleaseVersion->client_mst_data_i18n_en_hash);
            $this->assertEquals($masterDataI18nHashZh, $mngMasterReleaseVersion->client_mst_data_i18n_zh_hash);
            $this->assertEquals($operationDataHash, $mngMasterReleaseVersion->client_opr_data_hash);
            $this->assertEquals($operationDataI18nHashJa, $mngMasterReleaseVersion->client_opr_data_i18n_ja_hash);
            $this->assertEquals($operationDataI18nHashEn, $mngMasterReleaseVersion->client_opr_data_i18n_en_hash);
            $this->assertEquals($operationDataI18nHashZh, $mngMasterReleaseVersion->client_opr_data_i18n_zh_hash);
        }
        $this->assertEquals('masterSchemaVersion_1', $mngMasterReleaseVersion1->master_schema_version);
        $this->assertEquals('masterSchemaVersion_2', $mngMasterReleaseVersion2->master_schema_version);

        // mng_master_releasesのチェック
        $mngMasterReleases = MngMasterRelease::all();
        $mngMasterRelease1 = $mngMasterReleases->first(fn ($row) => $row->release_key === 2024092501);
        $this->assertEquals($mngMasterReleaseVersion1->id, $mngMasterRelease1->target_release_version_id);
        $mngMasterRelease2 = $mngMasterReleases->first(fn ($row) => $row->release_key === 2024092601);
        $this->assertEquals($mngMasterReleaseVersion2->id, $mngMasterRelease2->target_release_version_id);

        // adm_master_import_historiesのチェック
        $admMasterImportHistories = AdmMasterImportHistory::all();
        $this->assertCount(1, $admMasterImportHistories);
        $admMasterImportHistory = $admMasterImportHistories->first();
        $this->assertEquals($gitRevision, $admMasterImportHistory->git_revision);
        $this->assertEquals($importAdmUserId, $admMasterImportHistory->import_adm_user_id);
        $this->assertEquals($importSource, $admMasterImportHistory->import_source);

        // adm_master_import_history_versionsのチェック
        $admMasterImportHistoryVersions = AdmMasterImportHistoryVersion::all();
        $this->assertCount(2, $admMasterImportHistoryVersions);
        foreach ($admMasterImportHistoryVersions as $admMasterImportHistoryVersion) {
            $this->assertEquals($admMasterImportHistory->id, $admMasterImportHistoryVersion->adm_master_import_history_id);
            if (!in_array($admMasterImportHistoryVersion->mng_master_release_version_id, [$mngMasterReleaseVersion1->id, $mngMasterReleaseVersion2->id], true)) {
                // 登録したmng_master_release_version_idがmng_master_release_versionsに存在しなかったらエラーとする
                $this->fail();
            }
        }
    }

    /**
     * @test
     */
    public function deleteMasterRelease_データ削除チェック(): void
    {
        // Setup
        $mngMasterRelease = MngMasterRelease::factory()
            ->create([
                // 配信準備中 DB作成済み
                'id' => 'release_1',
                'release_key' => 2024090101,
                'enabled' => 0,
                'target_release_version_id' => '101',
            ]);
        MngMasterReleaseVersion::factory()
            ->create([
                // 配信準備中
                'id' => '101',
                'release_key' => 2024090101,
            ]);

        // Exercise
        $this->mngMasterReleaseService
            ->deleteMasterRelease($mngMasterRelease);

        // Verify
        $mngMasterReleases = MngMasterRelease::all();
        $this->assertCount(0, $mngMasterReleases);
        $mngMasterReleaseVersions = MngMasterReleaseVersion::all();
        $this->assertCount(0, $mngMasterReleaseVersions);
    }

    /**
     * @test
     */
    public function deleteMasterRelease_MngMasterReleaseのみ削除チェック(): void
    {
        // Setup
        $mngMasterRelease = MngMasterRelease::factory()
            ->create([
                // 配信準備中 DB未作成
                'id' => 'release_1',
                'release_key' => 2024090101,
            ]);

        // Exercise
        $this->mngMasterReleaseService
            ->deleteMasterRelease($mngMasterRelease);

        // Verify
        $mngMasterReleases = MngMasterRelease::all();
        $this->assertCount(0, $mngMasterReleases);
        $mngMasterReleaseVersions = MngMasterReleaseVersion::all();
        $this->assertCount(0, $mngMasterReleaseVersions);
    }

    /**
     * @test
     */
    public function releasedMngMasterReleasesById_更新チェック(): void
    {
        // Setup
        MngMasterRelease::factory()
            ->create([
                // 現在配信中
                'release_key' => 2024080101,
                'target_release_version_id' => '101',
                'enabled' => 1,
            ]);
        $mngMasterRelease = MngMasterRelease::factory()
            ->create([
                // 配信準備中
                'release_key' => 2024090101,
                'target_release_version_id' => '102',
            ]);
        MngMasterRelease::factory()
            ->create([
                // 配信準備中(リリース対象外)
                'release_key' => 2024100101,
            ]);

        // Exercise
        $this->mngMasterReleaseService
            ->releasedMngMasterReleasesById($mngMasterRelease->id);

        // Verify
        $actuals = MngMasterRelease::all();
        // 2024090101以前のenabledがtrueになっている
        $actual1 = $actuals->first(fn ($row) => $row->release_key === 2024080101);
        $this->assertTrue((bool) $actual1->enabled);
        $actual2 = $actuals->first(fn ($row) => $row->release_key === 2024090101);
        $this->assertTrue((bool) $actual2->enabled);
        // 対象外のenabledがfalseのままになっている
        $actual3 = $actuals->first(fn ($row) => $row->release_key === 2024100101);
        $this->assertFalse((bool) $actual3->enabled);
    }

    /**
     * @test
     */
    public function releasedMngMasterReleasesById_対象idが存在しない(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('not found mng_master_releases id:9999');

        // Exercise
        $this->mngMasterReleaseService
            ->releasedMngMasterReleasesById('9999');
    }

    /**
     * @test
     */
    public function getMngMasterReleasesByReleaseKeys_データ取得チェック(): void
    {
        // Setup
        MngMasterRelease::factory()->createMany([
            [
                // 配信終了
                'id' => 'master-1',
                'release_key' => 2024090101,
                'enabled' => 1,
                'target_release_version_id' => '101',
                'description' => '2024年9月配信版(終了済み)',
            ],
            [
                // 配信中
                'id' => 'master-2',
                'release_key' => 2024090102,
                'enabled' => 1,
                'target_release_version_id' => '102',
                'description' => '2024年9月配信版',
            ],
            [
                // 配信準備中
                'id' => 'master-3',
                'release_key' => 2024090103,
                'enabled' => 0,
                'target_release_version_id' => '103',
                'description' => '2024年9月修正版',
            ],
            [
                // 配信準備中 設定なし
                'id' => 'master-4',
                'release_key' => 2024090104,
                'enabled' => 0,
            ],
        ]);

        // Exercise
        $actuals = $this->mngMasterReleaseService->getMngMasterReleasesByReleaseKeys([2024090101,2024090102,2024090103]);

        // Verify
        $this->assertCount(3, $actuals);
        $actual1 = $actuals->first(fn (MngMasterRelease $row) => $row->release_key === 2024090101);
        $this->assertEquals('master-1', $actual1->id);
        $actual2 = $actuals->first(fn (MngMasterRelease $row) => $row->release_key === 2024090102);
        $this->assertEquals('master-2', $actual2->id);
        $actual3 = $actuals->first(fn (MngMasterRelease $row) => $row->release_key === 2024090103);
        $this->assertEquals('master-3', $actual3->id);
    }
}

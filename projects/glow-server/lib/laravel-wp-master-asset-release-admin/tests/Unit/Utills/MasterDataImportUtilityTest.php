<?php

namespace MasterAssetReleaseAdmin\Unit\Utils;

use WonderPlanet\Domain\MasterAssetReleaseAdmin\Entities\ImportDataDiffEntity;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Utils\MasterDataImportUtility;
use WonderPlanet\Tests\TestCase;

class MasterDataImportUtilityTest extends TestCase
{
    /**
     * @test
     */
    public function generateDataHashMapByHashMap_マップ生成チェック(): void
    {
        // Setup
        $releaseKeys = ['202411010', '202412010'];
        $masterDataHashMap = [
            '202411010' => 'master_data_hash_202411010',
            '202412010' => 'master_data_hash_202412010',
        ];
        $masterDataI18nHashMap = [
            '202411010' => [
                'ja' => 'master_data_i18n_ja_hash_202411010',
                'en' => 'master_data_i18n_en_hash_202411010',
                'zh-Hant' => 'master_data_i18n_zh-Hant_hash_202411010',
            ],
            '202412010' => [
                'ja' => 'master_data_i18n_ja_hash_202412010',
                'en' => 'master_data_i18n_en_hash_202412010',
                'zh-Hant' => 'master_data_i18n_zh-Hant_hash_202412010',
            ],
        ];
        $operationDataHashMap = [
            '202411010' => 'operation_data_hash_202411010',
            '202412010' => 'operation_data_hash_202412010',
        ];
        $operationDataI18nHashMap = [
            '202411010' => [
                'ja' => 'operation_data_i18n_ja_hash_202411010',
                'en' => 'operation_data_i18n_en_hash_202411010',
                'zh-Hant' => 'operation_data_i18n_zh-Hant_hash_202411010',
            ],
            '202412010' => [
                'ja' => 'operation_data_i18n_ja_hash_202412010',
                'en' => 'operation_data_i18n_en_hash_202412010',
                'zh-Hant' => 'operation_data_i18n_zh-Hant_hash_202412010',
            ],
        ];
        $serverDbHashMap = [
            '202411010' => 'server_data_hash_202411010',
            '202412010' => 'server_data_hash_202412010',
        ];
        $expectedBy202411010 = md5(
            'master_data_hash_202411010'
            . 'master_data_i18n_ja_hash_202411010'
            . 'master_data_i18n_en_hash_202411010'
            . 'master_data_i18n_zh-Hant_hash_202411010'
            . 'operation_data_hash_202411010'
            . 'operation_data_i18n_ja_hash_202411010'
            . 'operation_data_i18n_en_hash_202411010'
            . 'operation_data_i18n_zh-Hant_hash_202411010'
            . 'server_data_hash_202411010'
        );
        $expectedBy202412010 = md5(
            'master_data_hash_202412010'
            . 'master_data_i18n_ja_hash_202412010'
            . 'master_data_i18n_en_hash_202412010'
            . 'master_data_i18n_zh-Hant_hash_202412010'
            . 'operation_data_hash_202412010'
            . 'operation_data_i18n_ja_hash_202412010'
            . 'operation_data_i18n_en_hash_202412010'
            . 'operation_data_i18n_zh-Hant_hash_202412010'
            . 'server_data_hash_202412010'
        );

        // Exercise
        $actual = MasterDataImportUtility::generateDataHashMapByHashMap(
            $releaseKeys,
            $masterDataHashMap,
            $masterDataI18nHashMap,
            $operationDataHashMap,
            $operationDataI18nHashMap,
            $serverDbHashMap
        );

        // Verify
        $this->assertEquals($expectedBy202411010, $actual['202411010']);
        $this->assertEquals($expectedBy202412010, $actual['202412010']);
    }

    /**
     * @test
     */
    public function convertToLineBreaksFromMasterDataRow_スプシから取り込んだ情報の改行コード変換チェック(): void
    {
        // Setup
        // スプシから取得した1レコード情報
        $row = [
            '1',
            20240001,
            'ja',
            "TEST,
NEXT", // インデントを揃えるとスペースも含めてしまうのでインデントしてません
            'aabbcc',
        ];

        // Exercise
        $actual = MasterDataImportUtility::convertToLineBreaksFromSpreadSheetRow($row);

        // Verify
        $this->assertEquals([
            '1',
            20240001,
            'ja',
            'TEST,\nNEXT', // 改行->改行コードに変換されている
            'aabbcc',
        ],$actual);
    }

    /**
     * @test
     */
    public function convertToSystemEOLFromDatabaseCsv_masterdataCsvからクライアントjsonを生成する時の改行変換チェック(): void
    {
        // Setup
        // スプシから取得した1レコード情報
        $row = [
            'id' => '1',
            'release_key' => 20240001,
            'language' => 'ja',
            'description' => 'TEST,\nNEXT',
            'memo' => 'aabbcc',
        ];

        // Exercise
        $actual = MasterDataImportUtility::convertToSystemEOLFromDatabaseCsv($row);

        // Verify
        $this->assertEquals([
            'id' => '1',
            'release_key' => 20240001,
            'language' => 'ja',
            'description' => "TEST,
NEXT", // 改行コード->改行されている。インデントを揃えるとスペースも含めてしまうのでインデントしてません
            'memo' => 'aabbcc',
        ],$actual);
    }

    /**
     * @test
     */
    public function sortDiffData_ソート実行チェック(): void
    {
        // Setup
        $diffs[] = new ImportDataDiffEntity(
            'MstTest',
            [
                'modify' => [
                    [
                        'beforeRow' => ['id' => '3_zh-Hant', 'release_key' => '202301010', 'name' => '虛擬_c'],
                        'modifyColumnMap' => ['name' => '虛擬_C'],
                    ],
                    [
                        'beforeRow' => ['id' => '2_ja', 'release_key' => '202301011', 'name' => 'ダミー_b'],
                        'modifyColumnMap' => ['name' => 'ダミー_B'],
                    ],
                    [
                        'beforeRow' => ['id' => '2_zh-Hant', 'release_key' => '202301011', 'name' => '虛擬_b'],
                        'modifyColumnMap' => ['name' => '虛擬_B'],
                    ],
                    [
                        'beforeRow' => ['id' => '1_ja', 'release_key' => '202301010', 'name' => 'ダミー_a'],
                        'modifyColumnMap' => ['name' => 'ダミー_A'],
                    ],
                    [
                        'beforeRow' => ['id' => '1_en', 'release_key' => '202301010', 'name' => 'dummy_a'],
                        'modifyColumnMap' => ['name' => 'dummy_A'],
                    ],
                    [
                        'beforeRow' => ['id' => '1_zh-Hant', 'release_key' => '202301010', 'name' => '虛擬_a'],
                        'modifyColumnMap' => ['name' => '虛擬_A'],
                    ],
                    [
                        'beforeRow' => ['id' => '2_en', 'release_key' => '202301011', 'name' => 'dummy_B'],
                        'modifyColumnMap' => ['name' => 'dummy_B'],
                    ],
                    [
                        'beforeRow' => ['id' => '3_ja', 'release_key' => '202301010', 'name' => 'ダミー_c'],
                        'modifyColumnMap' => ['name' => 'ダミー_C'],
                    ],
                    [
                        'beforeRow' => ['id' => '3_en', 'release_key' => '202301010', 'name' => 'dummy_C'],
                        'modifyColumnMap' => ['name' => 'dummy_C'],
                    ],
                ],
                'delete' => [
                    ['id' => '1_ja', 'release_key' => '202301010', 'name' => 'ダミー_a'],
                    ['id' => '2_ja', 'release_key' => '202301011', 'name' => 'ダミー_b'],
                    ['id' => '3_ja', 'release_key' => '202301010', 'name' => 'ダミー_c'],
                    ['id' => '1_zh-Hant', 'release_key' => '202301010', 'name' => '虛擬_a'],
                    ['id' => '2_zh-Hant', 'release_key' => '202301011', 'name' => '虛擬_b'],
                    ['id' => '3_zh-Hant', 'release_key' => '202301010', 'name' => '虛擬_c'],
                    ['id' => '1_en', 'release_key' => '202301010', 'name' => 'dummy_a'],
                    ['id' => '2_en', 'release_key' => '202301011', 'name' => 'dummy_B'],
                    ['id' => '3_en', 'release_key' => '202301010', 'name' => 'dummy_C'],
                ],
                'new' => [
                    ['id' => '1_en', 'release_key' => '202301010', 'name' => 'dummy_a'],
                    ['id' => '1_ja', 'release_key' => '202301010', 'name' => 'ダミー_a'],
                    ['id' => '1_zh-Hant', 'release_key' => '202301010', 'name' => '虛擬_a'],
                    ['id' => '3_en', 'release_key' => '202301010', 'name' => 'dummy_C'],
                    ['id' => '3_ja', 'release_key' => '202301010', 'name' => 'ダミー_c'],
                    ['id' => '3_zh-Hant', 'release_key' => '202301010', 'name' => '虛擬_c'],
                    ['id' => '2_en', 'release_key' => '202301011', 'name' => 'dummy_B'],
                    ['id' => '2_ja', 'release_key' => '202301011', 'name' => 'ダミー_b'],
                    ['id' => '2_zh-Hant', 'release_key' => '202301011', 'name' => '虛擬_b'],
                ],
            ],
            // 以降はソートに不要なので空を設定
            [],
            [],
            [],
            [],
            [],
            []
        );

        // Exercise
        $sortedDiffs = MasterDataImportUtility::sortDiffData($diffs);

        // Verify
        $diffColl = collect($sortedDiffs);
        // 変更データのソートがrelease_keyの昇順、idの昇順になっているか
        $modifyData = $diffColl->first()['modifyData'];
        $this->assertEquals([
            [
                'beforeRow' => ['id' => '1_en', 'release_key' => '202301010', 'name' => 'dummy_a'],
                'modifyColumnMap' => ['name' => 'dummy_A'],
            ],
            [
                'beforeRow' => ['id' => '1_ja', 'release_key' => '202301010', 'name' => 'ダミー_a'],
                'modifyColumnMap' => ['name' => 'ダミー_A'],
            ],
            [
                'beforeRow' => ['id' => '1_zh-Hant', 'release_key' => '202301010', 'name' => '虛擬_a'],
                'modifyColumnMap' => ['name' => '虛擬_A'],
            ],
            [
                'beforeRow' => ['id' => '3_en', 'release_key' => '202301010', 'name' => 'dummy_C'],
                'modifyColumnMap' => ['name' => 'dummy_C'],
            ],
            [
                'beforeRow' => ['id' => '3_ja', 'release_key' => '202301010', 'name' => 'ダミー_c'],
                'modifyColumnMap' => ['name' => 'ダミー_C'],
            ],
            [
                'beforeRow' => ['id' => '3_zh-Hant', 'release_key' => '202301010', 'name' => '虛擬_c'],
                'modifyColumnMap' => ['name' => '虛擬_C'],
            ],
            [
                'beforeRow' => ['id' => '2_en', 'release_key' => '202301011', 'name' => 'dummy_B'],
                'modifyColumnMap' => ['name' => 'dummy_B'],
            ],
            [
                'beforeRow' => ['id' => '2_ja', 'release_key' => '202301011', 'name' => 'ダミー_b'],
                'modifyColumnMap' => ['name' => 'ダミー_B'],
            ],
            [
                'beforeRow' => ['id' => '2_zh-Hant', 'release_key' => '202301011', 'name' => '虛擬_b'],
                'modifyColumnMap' => ['name' => '虛擬_B'],
            ],
        ], $modifyData);

        // 削除データのソートがrelease_keyの昇順、idの昇順になっているか
        $deleteData = $diffColl->first()['deleteData'];
        $this->assertEquals([
            ['id' => '1_en', 'release_key' => '202301010', 'name' => 'dummy_a'],
            ['id' => '1_ja', 'release_key' => '202301010', 'name' => 'ダミー_a'],
            ['id' => '1_zh-Hant', 'release_key' => '202301010', 'name' => '虛擬_a'],
            ['id' => '3_en', 'release_key' => '202301010', 'name' => 'dummy_C'],
            ['id' => '3_ja', 'release_key' => '202301010', 'name' => 'ダミー_c'],
            ['id' => '3_zh-Hant', 'release_key' => '202301010', 'name' => '虛擬_c'],
            ['id' => '2_en', 'release_key' => '202301011', 'name' => 'dummy_B'],
            ['id' => '2_ja', 'release_key' => '202301011', 'name' => 'ダミー_b'],
            ['id' => '2_zh-Hant', 'release_key' => '202301011', 'name' => '虛擬_b'],
        ], $deleteData);

        // 新規データのソートがidの昇順になっているか
        $newData = $diffColl->first()['newData'];
        $this->assertEquals([
            ['id' => '1_en', 'release_key' => '202301010', 'name' => 'dummy_a'],
            ['id' => '1_ja', 'release_key' => '202301010', 'name' => 'ダミー_a'],
            ['id' => '1_zh-Hant', 'release_key' => '202301010', 'name' => '虛擬_a'],
            ['id' => '3_en', 'release_key' => '202301010', 'name' => 'dummy_C'],
            ['id' => '3_ja', 'release_key' => '202301010', 'name' => 'ダミー_c'],
            ['id' => '3_zh-Hant', 'release_key' => '202301010', 'name' => '虛擬_c'],
            ['id' => '2_en', 'release_key' => '202301011', 'name' => 'dummy_B'],
            ['id' => '2_ja', 'release_key' => '202301011', 'name' => 'ダミー_b'],
            ['id' => '2_zh-Hant', 'release_key' => '202301011', 'name' => '虛擬_b'],
        ], $newData);
    }

    /**
     * @test
     */
    public function makeRowCountMapFromAllTables_生成チェック(): void
    {
        // Setup
        $entities = [
            [
                'sheetName' => 'MstTest1',
                'modifyRowCountMapByReleaseKey' => [
                    '20231101' => 2,
                    '20231201' => 3,
                    '20240101' => 1,
                ],
                'newRowCountMapByReleaseKey' => [
                    '20231101' => 1,
                    '20240101' => 2,
                ],
                'deleteRowCountMapByReleaseKey' => [
                    '20231101' => 5,
                ],
            ],
            [
                'sheetName' => 'MstTest2',
                'modifyRowCountMapByReleaseKey' => [
                    '20231101' => 1,
                    '20231201' => 2,
                    '20240101' => 3,
                ],
                'newRowCountMapByReleaseKey' => [],
                'deleteRowCountMapByReleaseKey' => [],
            ],
        ];

        // Exercise
        [
            $modifyRowCountMapFromAllTable,
            $newRowCountMapFromAllTable,
            $deleteRowCountMapFromAllTable,
        ] = MasterDataImportUtility::makeRowCountMapFromAllTables($entities);

        // Verify
        // リリースキーごとの集計結果が問題ないか
        $this->assertEquals([
            '20231101' => 3,
            '20231201' => 5,
            '20240101' => 4,
        ], $modifyRowCountMapFromAllTable);
        $this->assertEquals([
            '20231101' => 1,
            '20240101' => 2,
        ], $newRowCountMapFromAllTable);
        $this->assertEquals([
            '20231101' => 5,
        ], $deleteRowCountMapFromAllTable);
    }

    /**
     * @test
     */
    public function getMasterDataHashPathList_生成テスト(): void
    {
        // Setup
        $mngMasterReleaseVersion = [
            'client_mst_data_hash' => 'test_client_mst_data_hash',
            'client_mst_data_i18n_ja_hash' => 'test_client_mst_data_i18n_ja_hash',
            'client_mst_data_i18n_en_hash' => 'test_client_mst_data_i18n_en_hash',
            'client_mst_data_i18n_zh_hash' => 'test_client_mst_data_i18n_zh_hash',
            'client_opr_data_hash' => 'testclient_opr_data_hash',
            'client_opr_data_i18n_ja_hash' => 'test_client_opr_data_i18n_ja_hash',
            'client_opr_data_i18n_en_hash' => 'test_client_opr_data_i18n_en_hash',
            'client_opr_data_i18n_zh_hash' => 'test_opr_data_i18n_zh_hash',
        ];

        // Exercise
        $hashPathList = MasterDataImportUtility::getMasterDataHashPathList($mngMasterReleaseVersion);

        // Verify
        $this->assertEquals([
            'masterdata/masterdata_test_client_mst_data_hash.data',
            'masteri18ndata/mst_I18n_ja_test_client_mst_data_i18n_ja_hash.data',
            'masteri18ndata/mst_I18n_en_test_client_mst_data_i18n_en_hash.data',
            'masteri18ndata/mst_I18n_zh-Hant_test_client_mst_data_i18n_zh_hash.data',
            'operationdata/operationdata_testclient_opr_data_hash.data',
            'operationi18ndata/opr_I18n_ja_test_client_opr_data_i18n_ja_hash.data',
            'operationi18ndata/opr_I18n_en_test_client_opr_data_i18n_en_hash.data',
            'operationi18ndata/opr_I18n_zh-Hant_test_opr_data_i18n_zh_hash.data',
        ], $hashPathList);
    }

    /**
     * @test
     * @dataProvider getS3MysqlDumpFilePrefixData
     */
    public function getS3MysqlDumpFilePrefix_取得データチェック(string $fromEnvironment, string $expected): void
    {
        // Exercise
        $actual = MasterDataImportUtility::getS3MysqlDumpFilePrefix($fromEnvironment);

        // Verify
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array[]
     */
    private function getS3MysqlDumpFilePrefixData(): array
    {
        return [
            '指定環境が設定されている' => [
                'develop1',
                'develop',
            ],
            '指定環境が設定されていない' => [
                'test1',
                'test1',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider s3MasterDumpData
     */
    public function getFromEnvironmentMySqlDumpConfigName_生成テスト(string $fromEnvironment, string $expected): void
    {
        // Exercise
        $actual = MasterDataImportUtility::getFromEnvironmentMySqlDumpConfigName($fromEnvironment);

        // Verify
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array[]
     */
    private function s3MasterDumpData(): array
    {
        return [
            'develop' => ['develop', 's3_master_dump_develop'],
            'develop1' => ['develop1', 's3_master_dump_develop1'],
            'develop2' => ['develop2', 's3_master_dump_develop2'],
            'develop3' => ['develop3', 's3_master_dump_develop3'],
        ];
    }

    /**
     * @test
     * @dataProvider s3ClientMasterData
     */
    public function getFromEnvironmentClientMasterDataConfigName_生成テスト(string $fromEnvironment, string $expected): void
    {
        // Exercise
        $actual = MasterDataImportUtility::getFromEnvironmentClientMasterDataConfigName($fromEnvironment);

        // Verify
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array[]
     */
    private function s3ClientMasterData(): array
    {
        return [
            'develop' => ['develop', 's3_client_master_data_develop'],
            'develop1' => ['develop1', 's3_client_master_data_develop1'],
            'develop2' => ['develop2', 's3_client_master_data_develop2'],
            'develop3' => ['develop3', 's3_client_master_data_develop3'],
        ];
    }
}

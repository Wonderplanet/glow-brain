<?php

declare(strict_types=1);

namespace MasterAssetReleaseAdmin\Unit\Services;

use Filament\Notifications\Notification;
use Google\Service\Sheets\Sheet;
use Google\Service\Sheets\SheetProperties;
use Mockery;
use Tests\Traits\ReflectionTrait;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Entities\SpreadSheetCsvEntity;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Entities\SpreadSheetRequestEntity;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators\GoogleDriveOperator;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators\GoogleSpreadSheetOperator;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\SpreadSheetFetchService;
use WonderPlanet\Tests\TestCase;

/**
 * マスターデータインポートv2用クラスのテスト
 */
class SpreadSheetFetchServiceTest extends TestCase
{
    use ReflectionTrait;

    protected function tearDown(): void
    {
        // テスト終了後、TestMstTableName.csvファイルがあれば削除する
        $filePath = storage_path('app/masterdata_csv/TestMstTableName.csv');
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        parent::tearDown();
    }

    /**
     * @test
     */
    public function fetch_正常_空配列(): void
    {
        // Setup
        $googleDriveOperatorMock = Mockery::mock(GoogleDriveOperator::class, ['']);
        $googleDriveOperatorMock->shouldReceive('getFileList')->andReturn([]);
        $googleSpreadSheetOperatorMock = Mockery::mock(GoogleSpreadSheetOperator::class, ['']);
        $googleSpreadSheetOperatorMock->shouldReceive('getSheets')->andReturn([]);

        /** @var SpreadSheetFetchService $service */
        $service = $this->app->make(SpreadSheetFetchService::class);

        $this->setPrivateProperty($service, 'driveOperator', $googleDriveOperatorMock);
        $this->setPrivateProperty($service, 'sheetOperator', $googleSpreadSheetOperatorMock);
        $releaseKey = 0;

        // Exercise
        $result = $service->fetch($releaseKey);

        // Verify
        $this->assertEquals($result, []);
    }

    /**
     * @test
     */
    public function fetch_正常_データあり(): void
    {
        // Setup
        $fileList = [
            ['id' => '1000000001', 'fileName' => 'MstTest']
        ];
        $sheetPropertiesMock = Mockery::mock(SheetProperties::class);
        $sheetPropertiesMock->shouldReceive('getTitle')->andReturn('TestMstTableName');
        $sheetPropertiesMock->shouldReceive('getSheetId')->andReturn('1000000001');
        $sheetMock = Mockery::mock(Sheet::class);
        $sheetMock->shouldReceive('getProperties')->andReturn($sheetPropertiesMock);
        $googleDriveOperatorMock = Mockery::mock(GoogleDriveOperator::class, ['']);
        $googleDriveOperatorMock->shouldReceive('getFileList')->andReturn($fileList);
        $googleSpreadSheetOperatorMock = Mockery::mock(GoogleSpreadSheetOperator::class, ['']);
        $googleSpreadSheetOperatorMock->shouldReceive('getSheets')->andReturn([$sheetMock]);
        $table = [
            ['TABLE', 'MstTest','MstTest','MstTest','MstTest','MstTest'],
            ['ENABLE', 'release_key', 'id', 'release_key', 'key', 'value'],
        ];
        $googleSpreadSheetOperatorMock->shouldReceive('getSheetValues')->andReturn($table);

        $service = $this->app->make(SpreadSheetFetchService::class);

        $this->setPrivateProperty($service, 'driveOperator', $googleDriveOperatorMock);
        $this->setPrivateProperty($service, 'sheetOperator', $googleSpreadSheetOperatorMock);
        $releaseKey = 0;

        // Exercise
        $result = $service->fetch($releaseKey);

        // Verify
        $this->assertEquals(count(array_keys($result)), 1);
        $this->assertEquals(array_keys($result)[0], 'TestMstTableName');
        $this->assertEquals(array_values($result)[0]->getTitle(), 'TestMstTableName');
        $this->assertEquals(array_values($result)[0]->getSheetId(), '1000000001');
        $this->assertEquals(array_values($result)[0]->getFileName(), 'MstTest');
        $this->assertEquals(array_values($result)[0]->getData()[0], $table[1]);
    }

    /**
     * @test
     */
    public function fetch_正常_メモ欄空欄(): void
    {
        // Setup
        $fileList = [
            ['id' => '1000000001', 'fileName' => 'MstTest']
        ];
        $sheetPropertiesMock = Mockery::mock(SheetProperties::class);
        $sheetPropertiesMock->shouldReceive('getTitle')->andReturn('TestMstHasMemoTableName');
        $sheetPropertiesMock->shouldReceive('getSheetId')->andReturn('1000000002');
        $sheetMock = Mockery::mock(Sheet::class);
        $sheetMock->shouldReceive('getProperties')->andReturn($sheetPropertiesMock);
        $googleDriveOperatorMock = Mockery::mock(GoogleDriveOperator::class, ['']);
        $googleDriveOperatorMock->shouldReceive('getFileList')->andReturn($fileList);
        $googleSpreadSheetOperatorMock = Mockery::mock(GoogleSpreadSheetOperator::class, ['']);
        $googleSpreadSheetOperatorMock->shouldReceive('getSheets')->andReturn([$sheetMock]);
        $table = [
            [],
            ['TABLE', 'MstTest','MstTest','MstTest','MstTest','MstTest'],
            ['ENABLE', 'release_key', 'id', 'release_key', 'key', 'value']
        ];
        $googleSpreadSheetOperatorMock->shouldReceive('getSheetValues')->andReturn($table);

        $service = $this->app->make(SpreadSheetFetchService::class);

        $this->setPrivateProperty($service, 'driveOperator', $googleDriveOperatorMock);
        $this->setPrivateProperty($service, 'sheetOperator', $googleSpreadSheetOperatorMock);
        $releaseKey = 0;

        // Exercise
        $result = $service->fetch($releaseKey);

        // Verify
        $this->assertEquals(count(array_keys($result)), 1);
        $this->assertEquals(array_keys($result)[0], 'TestMstHasMemoTableName');
        $this->assertEquals(array_values($result)[0]->getTitle(), 'TestMstHasMemoTableName');
        $this->assertEquals(array_values($result)[0]->getSheetId(), '1000000002');
        $this->assertEquals(array_values($result)[0]->getData()[0], $table[2]);
    }

    /**
     * @test
     */
    public function fetch_正常_メモ欄あり(): void
    {
        // Setup
        $fileList = [
            ['id' => '1000000001', 'fileName' => 'MstTest']
        ];
        $sheetPropertiesMock = Mockery::mock(SheetProperties::class);
        $sheetPropertiesMock->shouldReceive('getTitle')->andReturn('TestMstHasMemoTableName');
        $sheetPropertiesMock->shouldReceive('getSheetId')->andReturn('1000000002');
        $sheetMock = Mockery::mock(Sheet::class);
        $sheetMock->shouldReceive('getProperties')->andReturn($sheetPropertiesMock);
        $googleDriveOperatorMock = Mockery::mock(GoogleDriveOperator::class, ['']);
        $googleDriveOperatorMock->shouldReceive('getFileList')->andReturn($fileList);
        $googleSpreadSheetOperatorMock = Mockery::mock(GoogleSpreadSheetOperator::class, ['']);
        $googleSpreadSheetOperatorMock->shouldReceive('getSheets')->andReturn([$sheetMock]);
        $table = [
            ['メモ欄', 'テスト', '', '', '', ''],
            ['', 'info', '', '', '', ''],
            ['TABLE', 'MstTest','MstTest','MstTest','MstTest','MstTest'],
            ['ENABLE', 'release_key', 'id', 'release_key', 'key', 'value']
        ];
        $googleSpreadSheetOperatorMock->shouldReceive('getSheetValues')->andReturn($table);

        $service = $this->app->make(SpreadSheetFetchService::class);

        $this->setPrivateProperty($service, 'driveOperator', $googleDriveOperatorMock);
        $this->setPrivateProperty($service, 'sheetOperator', $googleSpreadSheetOperatorMock);
        $releaseKey = 0;

        // Exercise
        $result = $service->fetch($releaseKey);

        // Verify
        $this->assertEquals(count(array_keys($result)), 1);
        $this->assertEquals(array_keys($result)[0], 'TestMstHasMemoTableName');
        $this->assertEquals(array_values($result)[0]->getTitle(), 'TestMstHasMemoTableName');
        $this->assertEquals(array_values($result)[0]->getSheetId(), '1000000002');
        $this->assertEquals(array_values($result)[0]->getData()[0], $table[3]);
    }

    /**
     * @test
     */
    public function fetch_正常_メモ欄なし_i18nテーブル設定あり(): void
    {
        // Setup
        $fileList = [
            ['id' => '1000000003', 'fileName' => 'MstTest']
        ];
        $sheetPropertiesMock = Mockery::mock(SheetProperties::class);
        $sheetPropertiesMock->shouldReceive('getTitle')->andReturn('MstType');
        $sheetPropertiesMock->shouldReceive('getSheetId')->andReturn('1000000003');
        $sheetMock = Mockery::mock(Sheet::class);
        $sheetMock->shouldReceive('getProperties')->andReturn($sheetPropertiesMock);
        $googleDriveOperatorMock = Mockery::mock(GoogleDriveOperator::class, ['']);
        $googleDriveOperatorMock->shouldReceive('getFileList')->andReturn($fileList);
        $googleSpreadSheetOperatorMock = Mockery::mock(GoogleSpreadSheetOperator::class, ['']);
        $googleSpreadSheetOperatorMock->shouldReceive('getSheets')->andReturn([$sheetMock]);
        $table = [
            ['TABLE', 'MstType', 'MstType', 'MstType', 'MstTypeI18n', 'MstTypeI18n', 'MstType'],
            ['ENABLE', 'release_key', 'id', 'name', 'name.ja', 'name.en', 'role'],
            ['e', '20240101', '1', 'system', 'システム', 'System', 'admin'],
            ['e', '20240101', 'rankup', 'rankup', 'ランクアップ', 'RankUp', 'common'],
            ['e', '20240101', 'category_1', 'category-1', 'カテゴリー１', 'Category 1', 'common']
        ];
        $googleSpreadSheetOperatorMock->shouldReceive('getSheetValues')->andReturn($table);

        $service = $this->app->make(SpreadSheetFetchService::class);

        $this->setPrivateProperty($service, 'driveOperator', $googleDriveOperatorMock);
        $this->setPrivateProperty($service, 'sheetOperator', $googleSpreadSheetOperatorMock);
        $releaseKey = 20240101;

        // Exercise
        $result = $service->fetch($releaseKey);

        // Verify
        $this->assertEquals(array_values($result)[0]->getTitle(), 'MstType');
        $this->assertEquals(array_values($result)[1]->getTitle(), 'MstTypeI18n');
        $this->assertEquals(array_values($result)[0]->getSheetId(), '1000000003');
        $this->assertEquals(array_values($result)[1]->getSheetId(), '1000000003');
        $mstTableData = array_values($result)[0]->getData();
        $this->assertEquals(count(array_keys($mstTableData)), 4);
        $this->assertEquals($mstTableData[0], ['ENABLE', 'release_key', 'id', 'name', 'role']);
        $this->assertEquals($mstTableData[1], ['e', '20240101', '1', 'system', 'admin']);
        $this->assertEquals($mstTableData[2], ['e', '20240101', 'rankup', 'rankup', 'common']);
        $this->assertEquals($mstTableData[3], ['e', '20240101', 'category_1', 'category-1', 'common']);
        $mstTableI18nData = array_values($result)[1]->getData();
        $this->assertEquals(count(array_keys($mstTableI18nData)), 7);
        $this->assertEquals($mstTableI18nData[0], ['ENABLE', 'release_key', 'id', 'mst_type_id', 'language', 'name']);
        $this->assertEquals($mstTableI18nData[1], ['e', '20240101', '1_ja', '1', 'ja', 'システム']);
        $this->assertEquals($mstTableI18nData[2], ['e', '20240101', 'rankup_ja', 'rankup', 'ja', 'ランクアップ']);
        $this->assertEquals($mstTableI18nData[3], ['e', '20240101', 'category_1_ja', 'category_1', 'ja', 'カテゴリー１']);
        $this->assertEquals($mstTableI18nData[4], ['e', '20240101', '1_en', '1', 'en', 'System']);
        $this->assertEquals($mstTableI18nData[5], ['e', '20240101', 'rankup_en', 'rankup', 'en', 'RankUp']);
        $this->assertEquals($mstTableI18nData[6], ['e', '20240101', 'category_1_en', 'category_1', 'en', 'Category 1']);
    }

    /**
     * @test
     */
    public function fetch_正常_メモ欄あり_i18nテーブル設定あり(): void
    {
        // Setup
        $fileList = [
            ['id' => '1000000004', 'fileName' => 'MstTest']
        ];
        $sheetPropertiesMock = Mockery::mock(SheetProperties::class);
        $sheetPropertiesMock->shouldReceive('getTitle')->andReturn('MstType');
        $sheetPropertiesMock->shouldReceive('getSheetId')->andReturn('1000000004');
        $sheetMock = Mockery::mock(Sheet::class);
        $sheetMock->shouldReceive('getProperties')->andReturn($sheetPropertiesMock);
        $googleDriveOperatorMock = Mockery::mock(GoogleDriveOperator::class, ['']);
        $googleDriveOperatorMock->shouldReceive('getFileList')->andReturn($fileList);
        $googleSpreadSheetOperatorMock = Mockery::mock(GoogleSpreadSheetOperator::class, ['']);
        $googleSpreadSheetOperatorMock->shouldReceive('getSheets')->andReturn([$sheetMock]);
        $table = array(
            0 => ['メモ欄', 'テスト', '', '', '', '', ''],
            1 => ['', 'info', '', '', '', '', ''],
            2 => ['TABLE', 'MstType', 'MstType', 'MstType', 'MstTypeI18n', 'MstTypeI18n', 'MstType'],
            3 => ['ENABLE', 'release_key', 'id', 'name', 'name.ja', 'name.en', 'role'],
            4 => ['e', '20240102', '1', 'system', 'システム', 'System', 'admin']);
        $googleSpreadSheetOperatorMock->shouldReceive('getSheetValues')->andReturn($table);

        $service = $this->app->make(SpreadSheetFetchService::class);

        $this->setPrivateProperty($service, 'driveOperator', $googleDriveOperatorMock);
        $this->setPrivateProperty($service, 'sheetOperator', $googleSpreadSheetOperatorMock);
        $releaseKey = 20240102;

        // Exercise
        $result = $service->fetch($releaseKey);

        // Verify
        $this->assertEquals(array_values($result)[0]->getTitle(), 'MstType');
        $this->assertEquals(array_values($result)[1]->getTitle(), 'MstTypeI18n');
        $this->assertEquals(array_values($result)[0]->getSheetId(), '1000000004');
        $this->assertEquals(array_values($result)[1]->getSheetId(), '1000000004');
        $mstTableData = array_values($result)[0]->getData();
        $this->assertEquals(count(array_keys($mstTableData)), 2);
        $this->assertEquals($mstTableData[0], ['ENABLE', 'release_key', 'id', 'name', 'role']);
        $this->assertEquals($mstTableData[1], ['e', '20240102', '1', 'system', 'admin']);
        $mstTableI18nData = array_values($result)[1]->getData();
        $this->assertEquals(count(array_keys($mstTableI18nData)), 3);
        $this->assertEquals($mstTableI18nData[0], ['ENABLE', 'release_key', 'id', 'mst_type_id', 'language', 'name']);
        $this->assertEquals($mstTableI18nData[1], ['e', '20240102', '1_ja', '1', 'ja', 'システム']);
        $this->assertEquals($mstTableI18nData[2], ['e', '20240102', '1_en', '1', 'en', 'System']);
    }

    /**
     * @test
     */
    public function fetch_正常_データあり_targetあり(): void
    {
        // Setup
        $sheetPropertiesMock = Mockery::mock(SheetProperties::class);
        $sheetPropertiesMock->shouldReceive('getTitle')->andReturn('TestMstTableName');
        $sheetPropertiesMock->shouldReceive('getSheetId')->andReturn('1000000001');
        $sheetMock = Mockery::mock(Sheet::class);
        $sheetMock->shouldReceive('getProperties')->andReturn($sheetPropertiesMock);

        $googleSpreadSheetOperatorMock = Mockery::mock(GoogleSpreadSheetOperator::class, ['']);
        $googleSpreadSheetOperatorMock->shouldReceive('getSheets')->andReturn([$sheetMock]);
        $table = [
            ['TABLE', 'MstType', 'MstType', 'MstType', 'MstType', 'MstType', 'MstType'],
            ['ENABLE', 'release_key', 'id', 'release_key', 'key', 'value'],
        ];
        $googleSpreadSheetOperatorMock->shouldReceive('getSheetValues')->andReturn($table);

        $service = $this->app->make(SpreadSheetFetchService::class);
        $this->setPrivateProperty($service, 'sheetOperator', $googleSpreadSheetOperatorMock);

        $target[] = new SpreadSheetRequestEntity(
            'fileId',
            '',
            '1000000001'
        );
        $releaseKey = 0;

        // Exercise
        $result = $service->fetch($releaseKey, $target);

        // Verify
        $this->assertEquals(count(array_keys($result)), 1);
        $this->assertEquals(array_keys($result)[0], 'TestMstTableName');
        $this->assertEquals(array_values($result)[0]->getTitle(), 'TestMstTableName');
        $this->assertEquals(array_values($result)[0]->getSheetId(), '1000000001');
        $this->assertEquals(array_values($result)[0]->getData()[0], $table[1]);
    }

    /**
     * @test
     */
    public function getAndWriteSpreadSheetCsv_正常(): void
    {
        // Setup
        $fileList = [
            ['id' => '1000000001', 'fileName' => 'MstTest']
        ];
        $sheetPropertiesMock = Mockery::mock(SheetProperties::class);
        $sheetPropertiesMock->shouldReceive('getTitle')->andReturn('TestMstTableName');
        $sheetPropertiesMock->shouldReceive('getSheetId')->andReturn('1000000001');
        $sheetMock = Mockery::mock(Sheet::class);
        $sheetMock->shouldReceive('getProperties')->andReturn($sheetPropertiesMock);
        $googleDriveOperatorMock = Mockery::mock(GoogleDriveOperator::class, ['']);
        $googleDriveOperatorMock->shouldReceive('getFileList')->andReturn($fileList);
        $googleSpreadSheetOperatorMock = Mockery::mock(GoogleSpreadSheetOperator::class, ['']);
        $googleSpreadSheetOperatorMock->shouldReceive('getSheets')->andReturn([$sheetMock]);
        $table = [
            ['TABLE', 'MstTest', 'MstTest', 'MstTest', 'MstTest', 'MstTest'],
            ['ENABLE', 'release_key', 'id', 'release_key', 'key', 'value'],
        ];
        $googleSpreadSheetOperatorMock->shouldReceive('getSheetValues')->andReturn($table);

        $service = $this->app->make(SpreadSheetFetchService::class);

        $this->setPrivateProperty($service, 'driveOperator', $googleDriveOperatorMock);
        $this->setPrivateProperty($service, 'sheetOperator', $googleSpreadSheetOperatorMock);
        $releaseKey = 0;

        // Exercise
        $service->getAndWriteSpreadSheetCsv($releaseKey);

        // Verify
        $this->assertEquals(file_exists('storage/app/masterdata_csv/TestMstTableName.csv'), true);
    }

    /**
     * @test
     */
    public function fetchValues_スプシ情報からマスタデータcsv記載用のデータに落とし込めるかチェック_i18nあり(): void
    {
        // Setup
        /** @var SheetProperties $properties */
        $properties = $this->app->make(SheetProperties::class);
        $properties->setSheetId(999);
        $properties->setTitle('MstAvatar');
        /** @var Sheet $sheet */
        $sheet = $this->app->make(Sheet::class);
        $sheet->setProperties($properties);
        $spreadSheetRequestEntity = new SpreadSheetRequestEntity('file_id_1', '', '999');
        $spreadSheetCsvEntity = new SpreadSheetCsvEntity('file_id_1', 'MstAvatarName', $sheet, []);
        $value = [
            ["テストメモ", "テストメモ2"],
            ["memo"],
            ["TABLE", "MstAvatar", "MstAvatar", "MstAvatar", "MstAvatar", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n"],
            ["ENABLE", "release_key", "id", "asset_key", "rarity", "name.ja", "description.ja", "name.en", "description.en", "name.zh-Hant", "description.zh-Hant"],
            ["e", 202301, 1, "dummy1", "Common", "ダミー1", "ダミー1のアイコン", "Dummy 1", "Dummy 1 icon", "虛擬1", "虛擬1的圖標"],
            ["", 202301, 2, "dummy2", "Common", "ダミー2", "ダミー2のアイコン", "Dummy 2", "Dummy 2 icon", "虛擬2", "虛擬2的圖標"], // ENABLEが有効ではない
            ["e", 202302, 3, "dummy3", "Common", "ダミー3", "ダミー3のアイコン", "Dummy 3", "Dummy 3 icon", "虛擬3", "虛擬3的圖標"], // releaseKeyがmng_master_releaseのリリースキーより大きい
        ];
        $googleSpreadSheetOperatorMock = Mockery::mock(GoogleSpreadSheetOperator::class, ['']);
        $googleSpreadSheetOperatorMock->shouldReceive('getSheetValues')
            ->with('file_id_1', 'MstAvatar')
            ->andReturn($value);

        $service = $this->app->make(SpreadSheetFetchService::class);
        $this->setPrivateProperty($service, 'sheetOperator', $googleSpreadSheetOperatorMock);
        $target = [$spreadSheetRequestEntity];
        $sheets = [$spreadSheetCsvEntity];
        $releaseKey = 202301;

        // Exercise
        $actuals = $service->fetchValues($releaseKey, $target, $sheets);

        // Verify
        // MstAvatarとMstAvatarI18nの2マスターデータが生成できてるか
        $this->assertCount(2, $actuals);
        // 有効なMstAvatar情報が生成できているか
        /** @var SpreadSheetCsvEntity $actualMstAvatarCsvEntity */
        $actualMstAvatarCsvEntity = $actuals['MstAvatar'];
        $this->assertEquals(
            [
                ["ENABLE", "release_key", "id", "asset_key", "rarity"],
                ["e", 202301, 1, "dummy1", "Common"],
            ],
            $actualMstAvatarCsvEntity->getData()
        );
        // 有効なMstAvatarI18n情報が生成できているか
        /** @var SpreadSheetCsvEntity $actualMstAvatarI18nCsvEntity */
        $actualMstAvatarI18nCsvEntity = $actuals['MstAvatarI18n'];
        $this->assertEquals(
            [
                ["ENABLE", "release_key", "id", "mst_avatar_id", "language", "name", "description"],
                ["e", 202301, "1_ja", 1, "ja", "ダミー1", "ダミー1のアイコン"],
                ["e", 202301, "1_en", 1, "en", "Dummy 1", "Dummy 1 icon"],
                ["e", 202301, "1_zh-Hant", 1, "zh-Hant", "虛擬1", "虛擬1的圖標"],
            ],
            $actualMstAvatarI18nCsvEntity->getData()
        );
    }

    /**
     * @test
     */
    public function fetchValues_スプシ情報からマスタデータcsv記載用のデータに落とし込めるかチェック_i18nなし(): void
    {
        // Setup
        /** @var SheetProperties $properties */
        $properties = $this->app->make(SheetProperties::class);
        $properties->setSheetId(999);
        $properties->setTitle('MstAvatar');
        /** @var Sheet $sheet */
        $sheet = $this->app->make(Sheet::class);
        $sheet->setProperties($properties);
        $spreadSheetRequestEntity = new SpreadSheetRequestEntity('file_id_1', '', '999');
        $spreadSheetCsvEntity = new SpreadSheetCsvEntity('file_id_1', 'MstAvatarName', $sheet, []);
        $value = [
            ["テストメモ", "テストメモ2"],
            ["memo"],
            ["TABLE", "MstAvatar", "MstAvatar", "MstAvatar", "MstAvatar"],
            ["ENABLE", "release_key", "id", "asset_key", "rarity"],
            ["e", 202301, 1, "dummy1", "Common"],
            ["", 202301, 2, "dummy2", "Common"], // ENABLEが有効ではない
            ["e", 202302, 3, "dummy3", "Common"], // releaseKeyがmng_master_releaseのリリースキーより大きい
        ];
        $googleSpreadSheetOperatorMock = Mockery::mock(GoogleSpreadSheetOperator::class, ['']);
        $googleSpreadSheetOperatorMock->shouldReceive('getSheetValues')
            ->with('file_id_1', 'MstAvatar')
            ->andReturn($value);

        $service = $this->app->make(SpreadSheetFetchService::class);
        $this->setPrivateProperty($service, 'sheetOperator', $googleSpreadSheetOperatorMock);
        $target = [$spreadSheetRequestEntity];
        $sheets = [$spreadSheetCsvEntity];
        $releaseKey = 202301;

        // Exercise
        $actuals = $service->fetchValues($releaseKey, $target, $sheets);

        // Verify
        // MstAvatarのデータが生成できてるか
        $this->assertCount(1, $actuals);
        // 有効なMstAvatar情報が生成できているか
        /** @var SpreadSheetCsvEntity $actualMstAvatarCsvEntity */
        $actualMstAvatarCsvEntity = $actuals['MstAvatar'];
        $this->assertEquals(
            [
                ["ENABLE", "release_key", "id", "asset_key", "rarity"],
                ["e", 202301, 1, "dummy1", "Common"],
            ],
            $actualMstAvatarCsvEntity->getData()
        );
    }

    /**
     * @test
     * @dataProvider validateSpreadSheetValueDataArray
     */
    public function validateSpreadSheetValue_エラー時に例外内容を通知しているかチェック(array $sheetValue, string $exceptionMsg, string $body): void
    {
        $this->expectExceptionMessage($exceptionMsg);

        // Setup
        $fileName = 'MstUsers';

        // Exercise
        $service = $this->app->make(SpreadSheetFetchService::class);
        $this->callMethod(
            $service,
            'validateSpreadSheetValue',
            $fileName,
            $sheetValue
        );

        // Verify
        // エラー内容に沿った通知が表示されるか
        $notification = Notification::make()
            ->title("MstUsers シートの値が不正です")
            ->body($body)
            ->danger()
            ->color('danger')
            ->persistent();
        Notification::assertNotified($notification);
    }

    /**
     * @return array
     */
    private function validateSpreadSheetValueDataArray(): array
    {
        // MstUsers.MstAvatarテーブルと仮定
        return [
            'テーブル行がない' => [
                [
                    ["ENABLE", "release_key", "id", "asset_key", "rarity", "name.ja", "description.ja", "name.en", "description.en", "name.zh-Hant", "description.zh-Hant"],
                    ["e", 202301, 1, "dummy1", "Common", "ダミー1", "ダミー1のアイコン", "Dummy 1", "Dummy 1 icon", "虛擬1", "虛擬1的圖標"],
                ],
                'SpreadSheetFetchService バリデーションエラー title:MstUsers,isFailedByTableRow:true,isFailedByEnableColumn:false,isFailedByEmptyColumn:false',
                "テーブル行が見つかりませんでした<br/>"
            ],
            'カラム行がない' => [
                [
                    ["TABLE", "MstAvatar", "MstAvatar", "MstAvatar", "MstAvatar", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n"],
                    ["e", 202301, 1, "dummy1", "Common", "ダミー1", "ダミー1のアイコン", "Dummy 1", "Dummy 1 icon", "虛擬1", "虛擬1的圖標"],
                ],
                'SpreadSheetFetchService バリデーションエラー title:MstUsers,isFailedByTableRow:false,isFailedByEnableColumn:true,isFailedByEmptyColumn:false',
                "ENABLEが見つかりませんでした<br/>"
            ],
            'カラム行はあるがENABLE列がない' => [
                [
                    ["TABLE", "MstAvatar", "MstAvatar", "MstAvatar", "MstAvatar", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n"],
                    ["release_key", "id", "asset_key", "rarity", "name.ja", "description.ja", "name.en", "description.en", "name.zh-Hant", "description.zh-Hant"],
                    [202301, 1, "dummy1", "Common", "ダミー1", "ダミー1のアイコン", "Dummy 1", "Dummy 1 icon", "虛擬1", "虛擬1的圖標"],
                ],
                'SpreadSheetFetchService バリデーションエラー title:MstUsers,isFailedByTableRow:false,isFailedByEnableColumn:true,isFailedByEmptyColumn:false',
                "ENABLEが見つかりませんでした<br/>"
            ],
            'テーブル行なし and カラム行なし' => [
                [
                    ['e', 202301, 1, "dummy1", "Common", "ダミー1", "ダミー1のアイコン", "Dummy 1", "Dummy 1 icon", "虛擬1", "虛擬1的圖標"],
                ],
                'SpreadSheetFetchService バリデーションエラー title:MstUsers,isFailedByTableRow:true,isFailedByEnableColumn:true,isFailedByEmptyColumn:false',
                "テーブル行が見つかりませんでした<br/>ENABLEが見つかりませんでした<br/>"
            ],
            'テーブル行なし and ENABLE列なし' => [
                [
                    ["release_key", "id", "asset_key", "rarity", "name.ja", "description.ja", "name.en", "description.en", "name.zh-Hant", "description.zh-Hant"],
                    [202301, 1, "dummy1", "Common", "ダミー1", "ダミー1のアイコン", "Dummy 1", "Dummy 1 icon", "虛擬1", "虛擬1的圖標"],
                ],
                'SpreadSheetFetchService バリデーションエラー title:MstUsers,isFailedByTableRow:true,isFailedByEnableColumn:true,isFailedByEmptyColumn:false',
                "テーブル行が見つかりませんでした<br/>ENABLEが見つかりませんでした<br/>"
            ],
        ];
    }

    /**
     * @test
     */
    public function validateSpreadSheetValue_メモ列の空文字チェック_正常(): void
    {
        // Setup
        $fileName = 'MstUsers';
        $sheetValue = [
            ["TABLE", "MstAvatar", "MstAvatar", "", "MstAvatar", "MstAvatar", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n"],
            ["ENABLE", "release_key", "id", "", "asset_key", "rarity", "name.ja", "description.ja", "name.en", "description.en", "name.zh-Hant", "description.zh-Hant"],
            ["e", 202301, 1, "メモ", "dummy1", "Common", "ダミー1", "ダミー1のアイコン", "Dummy 1", "Dummy 1 icon", "虛擬1", "虛擬1的圖標"],
            ["e", 202301, 1, "", "dummy1", "Common", "ダミー1", "ダミー1のアイコン", "Dummy 1", "Dummy 1 icon", "虛擬1", "虛擬1的圖標", "メモ"],
        ];

        // Exercise
        $service = $this->app->make(SpreadSheetFetchService::class);
        $this->callMethod(
            $service,
            'validateSpreadSheetValue',
            $fileName,
            $sheetValue
        );

        // Verify
        // エラーが発生せず終了するか
        $this->assertTrue(true);
    }

    /**
     * @test
     * @dataProvider validateSpreadSheetValueMemoColumnDataArray
     */
    public function validateSpreadSheetValue_メモ列の空文字チェック_異常(array $sheetValue): void
    {
        $this->expectExceptionMessage('SpreadSheetFetchService バリデーションエラー title:MstUsers,isFailedByTableRow:false,isFailedByEnableColumn:false,isFailedByEmptyColumn:true');

        // Setup
        $fileName = 'MstUsers';

        // Exercise
        $service = $this->app->make(SpreadSheetFetchService::class);
        $this->callMethod(
            $service,
            'validateSpreadSheetValue',
            $fileName,
            $sheetValue
        );

        // Verify
        // エラー内容に沿った通知が表示されるか
        $notification = Notification::make()
            ->title("MstAvatar シートの値が不正です")
            ->body("テーブル行またはENABLE行にあるメモ列は空欄である必要があります<br/>")
            ->danger()
            ->color('danger')
            ->persistent();
        Notification::assertNotified($notification);
    }

    /**
     * @return array
     */
    private function validateSpreadSheetValueMemoColumnDataArray(): array
    {
        // MstUsers.MstAvatarテーブルと仮定
        return [
            'ENABLE行が空欄ではない' => [
                [
                    ["TABLE", "MstAvatar", "MstAvatar", "", "MstAvatar", "MstAvatar", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n"],
                    ["ENABLE", "release_key", "id", "f", "asset_key", "rarity", "name.ja", "description.ja", "name.en", "description.en", "name.zh-Hant", "description.zh-Hant"],
                    ["e", 202301, 1, "メモ", "dummy1", "Common", "ダミー1", "ダミー1のアイコン", "Dummy 1", "Dummy 1 icon", "虛擬1", "虛擬1的圖標"],
                ],
            ],
            'テーブル行が空欄ではない' => [
                [
                    ["TABLE", "MstAvatar", "MstAvatar", "f", "MstAvatar", "MstAvatar", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n"],
                    ["ENABLE", "release_key", "id", "", "asset_key", "rarity", "name.ja", "description.ja", "name.en", "description.en", "name.zh-Hant", "description.zh-Hant"],
                    ["e", 202301, 1, "メモ", "dummy1", "Common", "ダミー1", "ダミー1のアイコン", "Dummy 1", "Dummy 1 icon", "虛擬1", "虛擬1的圖標"],
                ],
            ],
            '空欄の位置ばずれている' => [
                [
                    ["TABLE", "MstAvatar", "MstAvatar", "MstAvatar", "", "MstAvatar", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n"],
                    ["ENABLE", "release_key", "id", "", "asset_key", "rarity", "name.ja", "description.ja", "name.en", "description.en", "name.zh-Hant", "description.zh-Hant"],
                    ["e", 202301, 1, "メモ", "dummy1", "Common", "ダミー1", "ダミー1のアイコン", "Dummy 1", "Dummy 1 icon", "虛擬1", "虛擬1的圖標"],
                ],
            ],
        ];
    }
}
